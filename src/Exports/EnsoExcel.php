<?php

namespace LaravelEnso\Tables\Exports;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\DataExport\Enums\Status;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\DataExport\Notifications\ExportDone;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Config;

class EnsoExcel extends Excel
{
    public function __construct(
        protected User $user,
        protected Table $table,
        protected Config $config,
        private Export $export
    ) {
    }

    protected function process(): void
    {
        App::setLocale($this->user->preferences()->global->lang);

        $this->export->update([
            'status' => Status::Processing,
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
        $args = [
            $this->export, $this->savedName, $this->filename,
            $this->export->getAttribute('created_by'),
        ];

        $file = File::attach(...$args);

        $this->export->fill(['status' => Status::Finalized])
            ->file()->associate($file)
            ->save();

        $notification = new ExportDone($this->export, $this->emailSubject());
        $queue = ConfigFacade::get('enso.tables.queues.notifications');
        $this->user->notify($notification->onQueue($queue));
    }

    protected function notifyError(): void
    {
        $this->export->update(['status' => Status::Failed]);

        parent::notifyError();
    }

    private function emailSubject(): string
    {
        $name = $this->config->label();

        return __(':name export done', ['name' => $name]);
    }
}
