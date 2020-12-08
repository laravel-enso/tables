<?php

namespace LaravelEnso\Tables\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\Tables\Exceptions\Export as Exception;
use LaravelEnso\Tables\Jobs\EnsoExcel as Job;

class EnsoPrepare extends Prepare
{
    private DataExport $export;

    public function handle(): void
    {
        $this->checkAlreadyRunning();

        parent::handle();
    }

    protected function dispatch(): void
    {
        $this->export = DataExport::factory()->create([
            'name' => $this->config->name(),
            'status' => Statuses::Waiting,
        ]);

        Job::dispatch($this->user, $this->config, $this->table, $this->export);
    }

    private function checkAlreadyRunning(): void
    {
        if ($this->alreadyRunning()) {
            throw Exception::alreadyRunning();
        }
    }

    private function alreadyRunning(): bool
    {
        $timeout = ConfigFacade::get('enso.tables.export.timeout');

        return DataExport::whereCreatedBy($this->user->id)
            ->whereName($this->config->name())
            ->whereIn('status', Statuses::cancellable())
            ->where('created_at', '>', Carbon::now()->subSeconds($timeout))
            ->exists();
    }
}
