<?php

namespace LaravelEnso\Tables\Exports;

use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\Tables\Jobs\EnsoExcel as Job;

class EnsoPrepare extends Prepare
{
    private DataExport $export;

    protected function dispatch(): void
    {
        $this->export = DataExport::factory()->create([
            'name' => $this->config->name(),
            'status' => Statuses::Waiting,
        ]);

        Job::dispatch($this->user, $this->config, $this->table, $this->export);
    }
}
