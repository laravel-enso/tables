<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\IO\app\Enums\IOStatuses;
use LaravelEnso\Tables\app\Exceptions\ExportException;
use LaravelEnso\Tables\app\Jobs\ExcelExport;
use LaravelEnso\Tables\app\Notifications\ExportStartNotification;
use LaravelEnso\Tables\app\Services\Data\Config;

class Excel
{
    private $user;
    private $config;
    private $tableClass;
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
            throw ExportException::alreadyRunning();
        }

        return $this;
    }

    private function notifyStart()
    {
        $this->user->notify(
            (new ExportStartNotification(
                $this->type().'_'.__('Table_Report'))
            )->onQueue(config('enso.tables.queues.notifications'))
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
                config('enso.tables.export.timeout')
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
        return ! empty(config('enso.config'));
    }
}
