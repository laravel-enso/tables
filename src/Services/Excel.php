<?php

namespace LaravelEnso\Tables\Services;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\IO\Enums\IOStatuses;
use LaravelEnso\Tables\Exceptions\Export as Exception;
use LaravelEnso\Tables\Jobs\ExcelExport;
use LaravelEnso\Tables\Notifications\ExportStartNotification;
use LaravelEnso\Tables\Services\Data\Config;

class Excel
{
    private User $user;
    private Config $config;
    private string $tableClass;
    private $dataExport;

    public function __construct(User $user, Config $config, string $tableClass)
    {
        $this->user = $user;
        $this->config = $config;
        $this->tableClass = $tableClass;
        $this->dataExport = null;
    }

    public function handle()
    {
        $this->checkAlreadyRunning()
            ->notifyStart()
            ->createDataExport()
            ->dispatch();
    }

    private function checkAlreadyRunning()
    {
        if ($this->isEnso() && $this->alreadyRunning()) {
            throw Exception::alreadyRunning();
        }

        return $this;
    }

    private function notifyStart()
    {
        $this->user->notify(
            (new ExportStartNotification($this->type().'_'.__('Table_Report')))
                ->onQueue(ConfigFacade::get('enso.tables.queues.notifications'))
        );

        return $this;
    }

    private function createDataExport()
    {
        if ($this->isEnso()) {
            $this->dataExport = DataExport::create([
                'name' => str_replace('_', ' ', $this->type()),
                'entries' => 0,
            ]);
        }

        return $this;
    }

    private function dispatch()
    {
        ExcelExport::dispatch(
            $this->user,
            $this->config,
            $this->tableClass,
            $this->dataExport
        );
    }

    private function alreadyRunning()
    {
        return DataExport::whereName(str_replace('_', ' ', $this->type()))
            ->whereCreatedBy($this->user->id)
            ->where('status', '<', IOStatuses::Finalized)
            ->where('created_at', '>', now()->subSeconds(
                ConfigFacade::get('enso.tables.export.timeout')
            ))->exists();
    }

    private function type()
    {
        return __(Str::title(
            Str::snake($this->config->get('name'))
        ));
    }

    private function isEnso()
    {
        return ! empty(ConfigFacade::get('enso.config'));
    }
}
