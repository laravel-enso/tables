<?php

namespace LaravelEnso\Tables\Exports;

use Illuminate\Foundation\Auth\User;
use LaravelEnso\DataExport\Enums\Status;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\Tables\Jobs\EnsoExcel;
use LaravelEnso\Tables\Jobs\Excel;
use LaravelEnso\Tables\Services\Data\Config;

class Prepare
{
    public function __construct(
        protected User $user,
        protected Config $config,
        protected string $table
    ) {
    }

    public function handle(): void
    {
        $args = [$this->user, $this->config, $this->table];

        if ($this->config->isEnso()) {
            $args[] = $this->export();
            EnsoExcel::dispatch(...$args);
        } else {
            Excel::dispatch(...$args);
        }
    }

    protected function export(): Export
    {
        return Export::factory()->create([
            'name' => $this->config->name(),
            'status' => Status::Waiting,
        ]);
    }
}
