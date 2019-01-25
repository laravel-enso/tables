<?php

namespace LaravelEnso\VueDatatable\app\Exports;

use App\User;
use Box\Spout\Common\Type;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\Style\StyleBuilder;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataExport\app\Enums\Statuses;
use LaravelEnso\VueDatatable\app\Classes\Fetcher;
use LaravelEnso\VueDatatable\app\Notifications\ExportDoneNotification;

class Excel
{
    private const Extension = '.xlsx';

    private $user;
    private $dataExport;
    private $request;
    private $fetcher;
    private $writer;
    private $columns;
    private $hashName;

    public function __construct(string $class, array $request, User $user, $dataExport = null)
    {
        $this->user = $user;
        $this->dataExport = $dataExport;
        $this->request = new Obj($request);
        $this->fetcher = new Fetcher($class, $request);
        $this->hashName = Str::random(40).self::Extension;
    }

    public function run()
    {
        $this->initWriter()
            ->start()
            ->process()
            ->closeWriter()
            ->finalize()
            ->notify()
            ->cleanUp();

        return $this;
    }

    public function process()
    {
        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            $this->writer->addRows(
                $this->map($this->fetcher->data())
            );

            $this->updateProgress($this->fetcher->chunkSize());

            $this->fetcher->next();
        }

        return $this;
    }

    public function start()
    {
        app()->setLocale($this->user->preferences()->global->lang);
        optional($this->dataExport)->update(['status' => Statuses::Processing]);
        $this->writer->addRow($this->header());

        return $this;
    }

    public function closeWriter()
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

        $this->writer = WriterFactory::create(Type::XLSX);

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile(\Storage::path($this->filePath()));

        return $this;
    }

    public function finalize()
    {
        if (! $this->dataExport) {
            return;
        }

        $file = new UploadedFile(
            storage_path('app/'.$this->filePath()),
            $this->filename(),
            \Storage::mimeType($this->filePath()),
            \Storage::size($this->filePath()),
            0,
            true
        );

        $this->dataExport->upload($file);
        $this->dataExport->file->created_by = $this->user->id;
        $this->dataExport->file->save();
        $this->dataExport->update(['status' => Statuses::Finalized]);

        return $this;
    }

    public function notify()
    {
        $this->user->notify(
            (new ExportDoneNotification(
                $this->filePath(),
                $this->filename(),
                $this->dataExport
            ))->onQueue(config('enso.datatable.queues.notifications'))
        );

        return $this;
    }

    public function cleanUp()
    {
        \Storage::delete($this->filePath());
    }

    private function filePath()
    {
        return config('enso.datatable.export.path')
            .DIRECTORY_SEPARATOR
            .$this->hashName;
    }

    private function filename()
    {
        return preg_replace(
            '/[^A-Za-z0-9_.-]/',
            '_',
            Str::title(Str::snake($this->request->get('name')))
            .'_'.__('Table_Report')
        ).self::Extension;
    }

    private function header()
    {
        return $this->columns()->pluck('label')
            ->map(function ($label) {
                return __($label);
            })->toArray();
    }

    private function columns()
    {
        if ($this->columns) {
            return $this->columns;
        }

        $this->columns = collect($this->request->get('columns'))
            ->reduce(function ($columns, $column) {
                $column = is_string($column)
                    ? json_decode($column)
                    : (object) $column;

                if (! $column->meta->rogue && ! $column->meta->notExportable) {
                    $columns->push($column);
                }

                return $columns;
            }, collect());

        return $this->columns;
    }

    private function map($data)
    {
        return $data->map(function ($row) {
            return $this->columns->reduce(function ($mappedRow, $column) use ($row) {
                $mappedRow->push($row[$column->name]);

                return $mappedRow;
            }, collect());
        })->toArray();
    }

    private function updateProgress($entries)
    {
        optional($this->dataExport)->update([
            'entries' => $this->dataExport->entries + $entries,
        ]);
    }
}
