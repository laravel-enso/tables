<?php

namespace LaravelEnso\Tables\Exports;

use Carbon\Carbon;
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
use LaravelEnso\Tables\Contracts\CustomExportChunk;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Notifications\ExportDone;
use LaravelEnso\Tables\Notifications\ExportError;
use LaravelEnso\Tables\Services\Data\ArrayComputors;
use LaravelEnso\Tables\Services\Data\Builders\Computor;
use LaravelEnso\Tables\Services\Data\Builders\Meta;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Filters;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

class Excel
{
    protected const Extension = 'xlsx';

    protected Builder $query;
    protected Writer $writer;
    protected Collection $columns;
    protected int $count;
    protected int $min;
    protected int $max;
    protected int $optimalChunk;
    protected int $chunkLimit;
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

        $template = $this->config->template();

        $sort = ConfigFacade::get('enso.tables.dtRowId') === $template->get('dtRowId')
            ? "{$template->get('table')}.{$template->get('dtRowId')}"
            : $template->get('dtRowId');

        $ends = [
            DB::raw("min({$sort}) as min"),
            DB::raw("max({$sort}) as max"),
        ];

        ['min' => $this->min, 'max' => $this->max] = $this->query->clone()
            ->select(...$ends)
            ->first();

        while ($this->min <= $this->max) {
            $chunkSize = $this->optimalChunk;
            $max = min($this->min + $chunkSize, $this->max + 1);
            $query = $this->range($sort, $this->min, $max);

            $shouldAdjustChunk = $this->chunkLimit !== $this->optimalChunk
                && $query->count() > $this->chunkLimit;

            if ($shouldAdjustChunk) {
                $max = min($this->min + $this->chunkLimit, $this->max + 1);
                $query = $this->range($sort, $this->min, $max);
            }

            $this->min = $max;
            $chunk = $query->get();

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
        $this->chunkLimit = $this->table instanceof CustomExportChunk
            ? $this->table->exportChunk()
            : $this->optimalChunk;

        return $this;
    }

    private function range(string $sort, int $start, int $end): Builder
    {
        return $this->query->clone()
            ->where($sort, '>=', $start)
            ->where($sort, '<', $end);
    }

    private function initWriter(): self
    {
        $this->writer = new Writer();

        $this->writer->openToFile($this->path);

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

        return !$this->cancelled;
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

        return !$this->cancelled;
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
            && !$meta->get('notExportable');
    }

    private function row(Collection $row): Row
    {
        return Row::fromValues($row->toArray());
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

        if (!Storage::has($folder)) {
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
        $timestamp = Carbon::now()->format('Y_m_d_H_i_s');
        $extension = self::Extension;

        return "{$this->config->name()}_{$suffix}_{$timestamp}.{$extension}";
    }
}
