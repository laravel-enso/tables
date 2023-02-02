<?php

namespace LaravelEnso\Tables\Exports;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Helpers\Services\OptimalChunk;
use LaravelEnso\Tables\Contracts\AuthenticatesOnExport;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Notifications\ExportDone;
use LaravelEnso\Tables\Notifications\ExportError;
use LaravelEnso\Tables\Services\Data\ArrayComputors;
use LaravelEnso\Tables\Services\Data\Builders\Computor;
use LaravelEnso\Tables\Services\Data\Builders\Meta;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Filters;
use Throwable;

class Excel
{
    protected const Extension = 'xlsx';

    protected Builder $query;
    protected Writer $writer;
    protected Collection $columns;
    protected int $count;
    protected int $optimalChunk;
    protected int $sheetCount;
    protected int $entryCount;
    protected string $filename;
    protected string $savedName;
    protected string $path;
    protected bool $cancelled;

    public function __construct(
        protected User $user,
        protected Table $table,
        protected Config $config
    ) {
        $this->writer = null;
    }

    public function handle(): void
    {
        $this->init();

        try {
            $this->filter()
                ->count()
                ->optimalChunk()
                ->initWriter()
                ->process();
        } catch (Throwable $th) {
            $this->notifyError();
            throw $th;
        } finally {
            $this->closeWriter();
        }

        if ($this->cancelled) {
            Storage::delete($this->path);
        } else {
            $this->finalize();
        }
    }

    protected function process(): void
    {
        $this->sheetCount = 1;
        $this->writer->addRow($this->header());

        $defaultSort = $this->config->template()->get('defaultSort');

        $ends = [
            DB::raw("min({$defaultSort}) as start"),
            DB::raw("max({$defaultSort}) as end"),
        ];

        ['start' => $start, 'end' => $end] = $this->query->clone()
            ->select(...$ends)
            ->first();

        while ($start <= $end) {
            $chunk = $this->query->clone()
                ->where($defaultSort, '>=', $start)
                ->where($defaultSort, '<', $start += $this->optimalChunk)
                ->get();

            if ($chunk->isNotEmpty()) {
                $this->processChunk($chunk);
            }
        }
    }

    protected function finalize(): void
    {
        $notification = (new ExportDone($this->path, $this->filename, $this->entryCount))
            ->onQueue(ConfigFacade::get('enso.tables.queues.notifications'));

        $this->user->notify($notification);
    }

    protected function notifyError(): void
    {
        $this->cancelled = true;

        $this->user->notify((new ExportError($this->config->name()))
            ->onQueue(ConfigFacade::get('enso.tables.queues.notifications')));
    }

    protected function updateProgress(int $chunkSize): self
    {
        $this->entryCount += $chunkSize;

        return $this;
    }

    private function init(): void
    {
        if ($this->table instanceof AuthenticatesOnExport) {
            Auth::setUser($this->user);
        }

        $this->query = $this->table->query();
        $this->filename = $this->filename();
        $this->savedName = $this->savedName();
        $this->path = $this->relativePath();
        $this->entryCount = 0;
        $this->cancelled = false;

        ArrayComputors::serverSide();
    }

    private function filter(): self
    {
        (new Filters($this->table, $this->config, $this->query))->handle();

        return $this;
    }

    private function count(): self
    {
        $this->count = (new Meta($this->table, $this->config))
            ->filter()->count(true);

        return $this;
    }

    private function optimalChunk(): self
    {
        $this->optimalChunk = OptimalChunk::get($this->count);

        return $this;
    }

    private function initWriter(): self
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterEntityFactory::createXLSXWriter();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->path);

        return $this;
    }

    private function processChunk(Collection $chunk): bool
    {
        if ($this->needsNewSheet()) {
            $this->addNewSheet();
        }

        $chunk = (new Computor($this->config, $chunk))->handle();

        $chunk->each(fn ($row) => $this->writeRow($row));

        $this->updateProgress($chunk->count());

        return ! $this->cancelled;
    }

    private function needsNewSheet(): bool
    {
        $limit = ConfigFacade::get('enso.tables.export.sheetLimit');
        $needed = Decimals::div($this->entryCount, $limit);

        return $needed >= $this->sheetCount;
    }

    private function addNewSheet(): void
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->writer->addRow($this->header());
        $this->sheetCount++;
    }

    private function writeRow(array $row): bool
    {
        $value = $this->columns->map(fn ($column) => $this->value($column, $row));

        $this->writer->addRow($this->row($value));

        return ! $this->cancelled;
    }

    private function header(): Row
    {
        $labels = $this->columns()->pluck('label')
            ->map(fn ($label) => __($label));

        return $this->row($labels);
    }

    private function columns(): Collection
    {
        return $this->columns ??= $this->config->columns()
            ->filter(fn ($column) => $this->exportable($column))
            ->reduce(fn ($columns, $column) => $columns
                ->push($column), new Collection());
    }

    private function exportable(Obj $column): bool
    {
        $meta = $column->get('meta');

        return $meta->get('visible')
            && ! $meta->get('notExportable');
    }

    private function row(Collection $row): Row
    {
        return WriterEntityFactory::createRowFromArray($row->toArray());
    }

    private function value(Obj $column, array $row)
    {
        return Collection::wrap(explode('.', $column->get('name')))
            ->reduce(fn ($value, $segment) => $value[$segment] ?? '', $row);
    }

    private function closeWriter(): void
    {
        if (isset($this->writer)) {
            $this->writer->close();
            unset($this->writer);
        }
    }

    private function relativePath(): string
    {
        $folder = ConfigFacade::get('enso.tables.export.folder');

        if (! Storage::has($folder)) {
            Storage::makeDirectory($folder);
        }

        return Storage::path("{$folder}/{$this->savedName}");
    }

    private function savedName(): string
    {
        $hash = Str::random(40);
        $extension = self::Extension;

        return "{$hash}.{$extension}";
    }

    private function filename(): string
    {
        $suffix = __('table_export');
        $extension = self::Extension;

        return "{$this->config->name()}_{$suffix}.{$extension}";
    }
}
