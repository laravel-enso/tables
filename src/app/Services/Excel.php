<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Str;
use LaravelEnso\IO\app\Enums\IOStatuses;
use LaravelEnso\Tables\app\Jobs\ExcelExport;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\Tables\app\Exceptions\ExportException;
use LaravelEnso\Tables\app\Notifications\ExportStartNotification;

class Excel
{
    private $user;
    private $request;
    private $tableClass;
    private $dataExport;

    public function __construct($user, array $request, $tableClass)
    {
        $this->user = $user;
        $this->request = $request;
        $this->tableClass = $tableClass;
        $this->dataExport = null;
    }

    public function handle()
    {
        $this->checkIfAlreadyRunning()
            ->notifyStart()
            ->createDataExport()
            ->dispatch();
    }

    private function checkIfAlreadyRunning()
    {
        if ($this->dataExportRunning()) {
            throw new ExportException(
                __('An export job is already running for the same table')
            );
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
            $this->request,
            $this->tableClass,
            $this->dataExport
        );
    }

    private function type()
    {
        return Str::title(
            Str::snake($this->request['name'])
        );
    }

    private function dataExportRunning()
    {
        return $this->isEnso()
            ? DataExport::whereName(str_replace('_', ' ', $this->type()))
                ->whereCreatedBy($this->user->id)
                ->where('status', '<', IOStatuses::Finalized)
                ->where('created_at', '>', now()->subSeconds(
                    config('enso.tables.export.timeout')
                    ))->first() !== null
            : false;
    }

    private function isEnso()
    {
        return ! empty(config('enso.config'));
    }
}
