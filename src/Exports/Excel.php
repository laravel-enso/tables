<?php

namespace LaravelEnso\Tables\Exports;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Notifications\ExportDoneNotification;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Fetcher;

class Excel
{
    private const Extension = 'xlsx';

    private User $user;
    private Config $config;
    private $dataExport;
    private Fetcher $fetcher;
    private Writer $writer;
    private Collection $columns;
    private int $sheetCount;
    private string $filename;
    private string $filePath;
    private int $entries;

    public function __construct(User $user, Table $table, Config $config, $dataExport = null)
    {
        $this->user = $user;
        $this->config = $config;
        $this->dataExport = $dataExport;
        $this->fetcher = new Fetcher($table, $this->config);
        $this->filename = $this->filename();
        $this->filePath = $this->filePath();
        $this->entries = optional($dataExport)->entries ?? 0;
    }

    public function run(): void
    {
        $this->initWriter()
            ->start()
            ->process()
            ->closeWriter()
            ->finalize()
            ->notify();
    }

    private function process(): self
    {
        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            if ($this->needsNewSheet()) {
                $this->addNewSheet();
            }

            $this->writeChunk();

            $this->updateProgress($this->fetcher->chunkSize());

            $this->fetcher->next();
        }

        return $this;
    }

    private function writeChunk()
    {
        $this->fetcher->current()
            ->each(fn ($row) => $this->writer->addRow($this->row(
                $this->columns->map(fn ($column) => $this->value($column, $row))
            )));
    }

    private function start(): self
    {
        if ($this->dataExport) {
            App::setLocale(
                $this->user->preferences()->global->lang
            );

            $this->dataExport->startProcessing();
        }

        $this->sheetCount = 1;

        $this->writer->addRow($this->header());

        return $this;
    }

    private function closeWriter(): self
    {
        $this->writer->close();
        unset($this->writer);

        return $this;
    }

    private function initWriter(): self
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterEntityFactory::createXLSXWriter();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->filePath);

        return $this;
    }

    private function finalize(): self
    {
        if (! $this->dataExport) {
            return $this;
        }

        $this->dataExport->attach(
            $this->filePath,
            $this->filename,
            $this->user
        );

        $this->dataExport->endOperation();

        return $this;
    }

    private function notify(): self
    {
        $this->user->notify((new ExportDoneNotification(
            $this->filePath,
            $this->filename,
            $this->dataExport,
            $this->entries
        ))->onQueue(ConfigFacade::get('enso.tables.queues.notifications')));

        return $this;
    }

    private function filePath(): string
    {
        $path = ConfigFacade::get('enso.tables.export.path');

        return Storage::path($path.DIRECTORY_SEPARATOR.$this->hashName());
    }

    private function hashName(): string
    {
        return Str::random(40).'.'.self::Extension;
    }

    private function filename(): string
    {
        $title = Str::title(Str::snake($this->config->get('name')));
        $baseName = __($title).'_'.__('Table_Report');
        $sanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $baseName);

        return $sanitized.'.'.self::Extension;
    }

    private function header(): Row
    {
        $labels = $this->columns()->pluck('label')->map(fn ($label) => __($label));

        return $this->row($labels);
    }

    private function columns(): Collection
    {
        return $this->columns ??= $this->config->columns()
            ->reduce(fn ($columns, $column) => $this->isExportable($column)
                ? $columns->push($column)
                : $columns, new Collection());
    }

    private function row(Collection $row): Row
    {
        return WriterEntityFactory::createRowFromArray($row->toArray());
    }

    private function updateProgress(int $entries): void
    {
        $this->entries += $entries;

        optional($this->dataExport)->update([
            'entries' => $this->entries,
        ]);
    }

    private function addNewSheet(): void
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->writer->addRow($this->header());
        $this->sheetCount++;
    }

    private function needsNewSheet(): bool
    {
        return $this->entries / ConfigFacade::get('enso.tables.export.sheetLimit')
            >= $this->sheetCount;
    }

    private function isExportable(Obj $column): bool
    {
        $meta = $column->get('meta');

        return $meta->get('visible')
            && ! $meta->get('notExportable');
    }

    private function value($column, $row)
    {
        return (new Collection(explode('.', $column->get('name'))))
            ->reduce(fn ($value, $segment) => $value[$segment] ?? null, $row);
    }
}
