<?php

namespace LaravelEnso\VueDatatable\app\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\VueDatatable\app\Exports\Excel;

class ExcelExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $user;
    private $tableClass;
    private $dataExport;

    public function __construct(User $user, array $request, string $tableClass, $dataExport = null)
    {
        $this->user = $user;
        $this->request = $request;
        $this->tableClass = $tableClass;
        $this->dataExport = $dataExport;
        $this->timeout = config('enso.datatable.export.timeout');
        $this->queue = config('enso.datatable.queues.exports');
    }

    public function handle()
    {
        (new Excel(
            $this->tableClass, $this->request, $this->user, $this->dataExport
        ))->run();
    }
}
