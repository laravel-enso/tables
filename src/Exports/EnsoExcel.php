<?php

namespace LaravelEnso\Tables\Exports;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\DataExport\Notifications\ExportDone;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Config;

class EnsoExcel extends Excel
{
    private DataExport $export;

    public function __construct(User $user, Table $table, Config $config, DataExport $export)
    {
        parent::__construct($user, $table, $config);

        $this->export = $export;
    }

    protected function notifyError(): void
    {
        $this->export->update(['status' => Statuses::Failed]);

        parent::notifyError();
    }

    protected function start(): self
    {
        parent::start();

        App::setLocale($this->user->preferences()->global->lang);

        $this->export->update([
            'status' => Statuses::Processing,
            'total' => $this->fetcher->count(),
        ]);

        return $this;
    }

    protected function finalize(): void
    {
        $this->export->file->created_by = $this->export->created_by;
        $this->export->file->attach($this->relativePath, $this->filename);

        $this->export->update(['status' => Statuses::Finalized]);

        $this->user->notify((new ExportDone($this->export, $this->emailSubject()))
            ->onQueue(ConfigFacade::get('enso.tables.queues.notifications')));
    }

    private function emailSubject(): string
    {
        $name = $this->config->label();

        return __(':name export done', ['name' => $name]);
    }

    protected function updateProgress(): self
    {
        $this->cancelled = $this->export->fresh()->cancelled();

        parent::updateProgress();

        $this->export->update(['entries' => $this->entries]);

        return $this;
    }
}
