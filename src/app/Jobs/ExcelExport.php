<?php

namespace LaravelEnso\Tables\app\Jobs;

use Illuminate\Bus\Queueable;
use LaravelEnso\Core\app\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use LaravelEnso\Tables\app\Exports\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        $this->timeout = config('enso.tables.export.timeout');
        $this->queue = config('enso.tables.queues.exports');
    }

    public function handle()
    {
        (new Excel(
            $this->tableClass, $this->request, $this->user, $this->dataExport
        ))->run();
    }
}
