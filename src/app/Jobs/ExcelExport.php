<?php

namespace LaravelEnso\VueDatatable\app\Jobs;

use Illuminate\Bus\Queueable;
use LaravelEnso\Core\app\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use LaravelEnso\VueDatatable\app\Exports\Excel;
use LaravelEnso\VueDatatable\app\Notifications\ExportNotification;

class ExcelExport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $table;
    private $filePath;

    public function __construct(User $user, $table)
    {
        $this->user = $user;
        $this->table = $table;
        $this->timeout = config('enso.datatable.export.maxExecutionTime');

        $this->setFilePath();
    }

    public function handle()
    {
        $this->export()
            ->sendReport()
            ->cleanUp();
    }

    private function export()
    {
        $exporter = new Excel($this->filePath, $this->table['header'], $this->table['data']);
        $exporter->run();

        return $this;
    }

    private function sendReport()
    {
        $this->user->notify(new ExportNotification($this->filePath, $this->table['name']));

        return $this;
    }

    private function cleanUp()
    {
        \Storage::delete($this->filePath);
    }

    private function setFilePath()
    {
        $filename = preg_replace(
            '/[^A-Za-z0-9_.-]/',
            '_',
            __($this->table['name']).'_'.__('Report')
        ).'.xlsx';

        $this->filePath = storage_path(
            'app/'.config('enso.datatable.export.path').'/'.$filename
        );
    }
}
