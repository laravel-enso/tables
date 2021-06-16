<?php

namespace LaravelEnso\Tables\Exports;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\AuthenticatesOnExport;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Notifications\ExportDone;
use LaravelEnso\Tables\Notifications\ExportError;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Fetcher;
use Throwable;

class Excel
{
    protected const Extension = 'xlsx';

    protected User $user;
    protected Config $config;
    protected Fetcher $fetcher;
    protected bool $authenticates;
    protected Writer $writer;
    protected Collection $columns;
    protected int $sheets;
    protected string $filename;
    protected string $relativePath;
    protected string $path;
    protected int $entries;
    protected bool $cancelled;

    public function __construct(User $user, Table $table, Config $config)
    {
        $this->user = $user;
        $this->config = $config;
        $this->fetcher = new Fetcher($table, $this->config);
        $this->authenticates = $table instanceof AuthenticatesOnExport;
        $this->filename = $this->filename();
        $this->relativePath = $this->relativePath();
        $this->path = Storage::path($this->relativePath);
        $this->entries = 0;
        $this->cancelled = false;
    }

    public function handle(): void
    {
        try {
            $this->initWriter()
                ->start()
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

    protected function notifyError(): void
    {
        $this->cancelled = true;

        $this->user->notify((new ExportError($this->config->name()))
            ->onQueue(ConfigFacade::get('enso.tables.queues.notifications')));
    }

    protected function process(): void
    {
        $this->fetcher->next();

        while ($this->fetcher->valid() && ! $this->cancelled) {
            if ($this->needsNewSheet()) {
                $this->addNewSheet();
            }

            $this->writeChunk()
                ->updateProgress();

            $this->fetcher->next();
        }
    }

    protected function writeChunk(): self
    {
        $this->fetcher->current()
            ->each(fn ($row) => $this->writer->addRow($this->row(
                $this->columns->map(fn ($column) => $this->value($column, $row))
            )));

        return $this;
    }

    protected function start(): self
    {
        if ($this->authenticates) {
            Auth::setUser($this->user);
        }

        $this->sheets = 1;

        $this->writer->addRow($this->header());

        return $this;
    }

    protected function closeWriter(): void
    {
        $this->writer->close();
        unset($this->writer);
    }

    protected function initWriter(): self
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterEntityFactory::createXLSXWriter();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->path);

        return $this;
    }

    protected function finalize(): void
    {
        $this->user->notify(
            (new ExportDone($this->path, $this->filename, $this->entries))
                ->onQueue(ConfigFacade::get('enso.tables.queues.notifications'))
        );
    }

    protected function relativePath(): string
    {
        $folder = ConfigFacade::get('enso.tables.export.folder');

        if (! Storage::has($folder)) {
            Storage::makeDirectory($folder);
        }

        $hash = Str::random(40);
        $extension = self::Extension;

        return "{$folder}/{$hash}.{$extension}";
    }

    protected function filename(): string
    {
        $suffix = __('table_export');
        $extension = self::Extension;

        return "{$this->config->name()}_{$suffix}.{$extension}";
    }

    protected function header(): Row
    {
        $labels = $this->columns()->pluck('label')
            ->map(fn ($label) => __($label));

        return $this->row($labels);
    }

    protected function columns(): Collection
    {
        return $this->columns ??= $this->config->columns()
            ->filter(fn ($column) => $this->exportable($column))
            ->reduce(fn ($columns, $column) => $columns
                ->push($column), new Collection());
    }

    protected function row(Collection $row): Row
    {
        return WriterEntityFactory::createRowFromArray($row->toArray());
    }

    protected function updateProgress(): self
    {
        $this->entries += $this->fetcher->chunkSize();

        return $this;
    }

    protected function addNewSheet(): void
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->writer->addRow($this->header());
        $this->sheets++;
    }

    protected function needsNewSheet(): bool
    {
        $limit = ConfigFacade::get('enso.tables.export.sheetLimit');
        $needed = Decimals::div($this->entries, $limit);

        return $needed >= $this->sheets;
    }

    protected function exportable(Obj $column): bool
    {
        $meta = $column->get('meta');

        return $meta->get('visible')
            && ! $meta->get('notExportable');
    }

    protected function value($column, $row)
    {
        return Collection::wrap(explode('.', $column->get('name')))
            ->reduce(fn ($value, $segment) => $value[$segment] ?? null, $row);
    }
}
