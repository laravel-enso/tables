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
    public function __construct(
        protected User $user,
        protected Table $table,
        protected Config $config,
        private DataExport $export
    ) {
    }

    protected function process(): void
    {
        App::setLocale($this->user->preferences()->global->lang);

        $this->export->update([
            'status' => Statuses::Processing,
            'total' => $this->count,
        ]);

        parent::process();
    }

    protected function updateProgress(int $chunkSize): self
    {
        parent::updateProgress($chunkSize);

        $this->export->update(['entries' => $this->entryCount]);
        $this->cancelled = $this->export->fresh()->cancelled();

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

    protected function notifyError(): void
    {
        $this->export->update(['status' => Statuses::Failed]);

        parent::notifyError();
    }

    private function emailSubject(): string
    {
        $name = $this->config->label();

        return __(':name export done', ['name' => $name]);
    }
}
