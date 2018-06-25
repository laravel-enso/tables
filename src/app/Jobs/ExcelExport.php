<?php

namespace LaravelEnso\VueDatatable\app\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use LaravelEnso\VueDatatable\app\Exports\Excel;
use LaravelEnso\VueDatatable\app\Notifications\ExportDoneNotification;

class ExcelExport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $user;
    private $tableClass;
    private $table;
    private $filePath;

    public function __construct(User $user, array $request, string $tableClass)
    {
        $this->user = $user;
        $this->request = $request;
        $this->tableClass = $tableClass;
        $this->timeout = config('enso.datatable.export.maxExecutionTime');
    }

    public function handle()
    {
        auth()->onceUsingId($this->user->id);

        $this->table = (new $this->tableClass($this->request))
            ->excel();

        $this->filePath()
            ->export()
            ->sendReport()
            ->cleanUp();
    }

    private function export()
    {
        (new Excel(
            $this->filePath,
            $this->table['header'],
            $this->table['data']
        ))->run();

        return $this;
    }

    private function sendReport()
    {
        $this->user->notify(
            new ExportDoneNotification(
                $this->filePath,
                $this->table['name']
            )
        );

        return $this;
    }

    private function cleanUp()
    {
        \Storage::delete($this->filePath);
    }

    private function filePath()
    {
        $filename = preg_replace(
            '/[^A-Za-z0-9_.-]/',
            '_',
            __($this->table['name']).'_'.__('Report')
        ).'.xlsx';

        $this->filePath = config('enso.datatable.export.path')
            .DIRECTORY_SEPARATOR.$filename;

        return $this;
    }
}
