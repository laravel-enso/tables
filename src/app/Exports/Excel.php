<?php

namespace LaravelEnso\Tables\app\Exports;

use Illuminate\Http\File;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Services\Fetcher;
use LaravelEnso\Tables\app\Services\Table\Request;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use LaravelEnso\Tables\app\Notifications\ExportDoneNotification;

class Excel
{
    private const Extension = 'xlsx';

    private $user;
    private $config;
    private $dataExport;
    private $fetcher;
    private $writer;
    private $columns;
    private $sheetCount;
    private $filename;
    private $filePath;

    public function __construct(User $user, Table $table, Config $config, $dataExport = null)
    {
        $this->user = $user;
        $this->config = $config;
        $this->dataExport = $dataExport;
        $this->fetcher = new Fetcher($table, $this->config);
    }

    public function run()
    {
        $this->initWriter()
            ->start()
            ->process()
            ->closeWriter()
            ->finalize()
            ->notify();

        return $this;
    }

    private function process()
    {
        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            if ($this->needsNewSheet()) {
                $this->addNewSheet();
            }

            $this->writer->addRows(
                $this->map($this->fetcher->data())
            );

            $this->updateProgress($this->fetcher->chunkSize());

            $this->fetcher->next();
        }

        return $this;
    }

    private function start()
    {
        if ($this->dataExport) {
            app()->setLocale(
                $this->user->preferences()->global->lang
            );

            $this->dataExport->startProcessing();
        }

        $this->sheetCount = 1;

        $this->writer->addRow($this->header());

        return $this;
    }

    private function closeWriter()
    {
        $this->writer->close();

        unset($this->writer);

        return $this;
    }

    private function initWriter()
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterEntityFactory::createXLSXWriter();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->filePath());

        return $this;
    }

    private function finalize()
    {
        if (! $this->dataExport) {
            return;
        }

        $this->dataExport->attach(
            new File($this->filePath()),
            $this->filename()
        );

        $this->dataExport->endOperation();

        return $this;
    }

    private function notify()
    {
        $this->user->notify((new ExportDoneNotification(
            $this->filePath(),
            $this->filename(),
            $this->dataExport
        ))->onQueue(config('enso.tables.queues.notifications')));

        return $this;
    }

    private function filePath()
    {
        return $this->filePath
            ?? $this->filePath = Storage::path(
                config('enso.tables.export.path')
                    .DIRECTORY_SEPARATOR
                    .$this->hashName()
            );
    }

    private function hashName()
    {
        return Str::random(40).'.'.self::Extension;
    }

    private function filename()
    {
        return $this->filename
            ?? $this->filename = preg_replace(
                '/[^A-Za-z0-9_.-]/',
                '_',
                Str::title(Str::snake($this->config->get('name')))
                .'_'.__('Table_Report')
            ).'.'.self::Extension;
    }

    private function header()
    {
        return $this->row($this->columns()->pluck('label')
                ->map(function ($label) {
                    return __($label);
                }));
    }

    private function columns()
    {
        if ($this->columns) {
            return $this->columns;
        }

        $this->columns = $this->config->columns()
            ->reduce(function ($columns, $column) {
                $meta = $column->get('meta');

                return $meta->get('visible') && ! $meta->get('rogue') && ! $meta->get('notExportable')
                    ? $columns->push($column)
                    : $columns;
            }, collect());

        return $this->columns;
    }

    private function map($data)
    {
        return $data->map(function ($row) {
            return $this->row($this->columns->map(function ($column) use ($row) {
                return $row[$column->get('name')];
            }));
        })->toArray();
    }

    private function row($row)
    {
        return WriterEntityFactory::createRowFromArray($row->toArray());
    }

    private function updateProgress($entries)
    {
        optional($this->dataExport)->update([
            'entries' => $this->dataExport->entries + $entries,
        ]);
    }

    private function addNewSheet()
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->writer->addRow($this->header());
        $this->sheetCount++;
    }

    private function needsNewSheet()
    {
        return $this->dataExport->entries / config('enso.tables.export.sheetLimit')
            >= $this->sheetCount;
    }
}
