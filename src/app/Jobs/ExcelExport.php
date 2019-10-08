<?php

namespace LaravelEnso\Tables\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
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

    public $timeout;
    public $queue;

    public function __construct(User $user, string $tableClass, array $request, $dataExport = null)
    {
        $this->user = $user;
        $this->tableClass = $tableClass;
        $this->request = $request;
        $this->dataExport = $dataExport;

        $this->timeout = config('enso.tables.export.timeout');
        $this->queue = config('enso.tables.queues.exports');
    }

    public function handle()
    {
        Auth::onceUsingId($this->user->id); // implement AuthenticatesOnExport

        (new Excel(
            $this->tableClass, $this->request, $this->user, $this->dataExport
        ))->run();
    }
}
