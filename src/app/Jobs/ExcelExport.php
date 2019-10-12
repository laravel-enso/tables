<?php

namespace LaravelEnso\Tables\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\App;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use LaravelEnso\Tables\app\Exports\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\Tables\app\Services\Data\Config;
use LaravelEnso\Tables\app\Services\TemplateLoader;
use LaravelEnso\Tables\app\Contracts\AuthenticatesOnExport;

class ExcelExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $config;
    private $tableClass;
    private $dataExport;

    public $timeout;
    public $queue;

    public function __construct(User $user, Config $config, string $tableClass, $dataExport = null)
    {
        $this->user = $user;
        $this->config = $config;
        $this->tableClass = $tableClass;
        $this->dataExport = $dataExport;

        $this->timeout = config('enso.tables.export.timeout');
        $this->queue = config('enso.tables.queues.exports');
    }

    public function handle()
    {
        $table = App::make($this->tableClass, ['request' => $this->config->request()]);

        if ($table instanceof AuthenticatesOnExport) {
            Auth::onceUsingId($this->user->id);
        }

        (new Excel(
            $this->user, $table, $this->config, $this->dataExport
        ))->run();
    }
}
