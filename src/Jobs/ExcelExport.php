<?php

namespace LaravelEnso\Tables\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\Tables\Contracts\AuthenticatesOnExport;
use LaravelEnso\Tables\Exports\Excel;
use LaravelEnso\Tables\Services\Data\Config;

class ExcelExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout;
    public $queue;

    private User $user;
    private Config $config;
    private string $tableClass;
    private $dataExport;

    public function __construct(User $user, Config $config, string $tableClass, $dataExport = null)
    {
        $this->user = $user;
        $this->config = $config;
        $this->tableClass = $tableClass;
        $this->dataExport = $dataExport;

        $this->timeout = ConfigFacade::get('enso.tables.export.timeout');
        $this->queue = ConfigFacade::get('enso.tables.queues.exports');
    }

    public function handle()
    {
        $table = App::make($this->tableClass, ['request' => $this->config->request()]);

        if ($table instanceof AuthenticatesOnExport) {
            Auth::setUser($this->user);
        }

        (new Excel(
            $this->user,
            $table,
            $this->config,
            $this->dataExport
        ))->run();
    }
}
