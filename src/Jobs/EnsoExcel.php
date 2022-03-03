<?php

namespace LaravelEnso\Tables\Jobs;

use Illuminate\Foundation\Auth\User;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\Tables\Exports\EnsoExcel as Service;
use LaravelEnso\Tables\Services\Data\Config;

class EnsoExcel extends Excel
{
    private Export $export;

    public function __construct(User $user, Config $config, string $table, Export $export)
    {
        parent::__construct($user, $config, $table);

        $this->export = $export;
    }

    public function handle()
    {
        $args = [$this->user, $this->table(), $this->config, $this->export];

        (new Service(...$args))->handle();
    }
}
