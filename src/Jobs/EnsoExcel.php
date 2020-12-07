<?php

namespace LaravelEnso\Tables\Jobs;

use Illuminate\Foundation\Auth\User;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\Tables\Exports\EnsoExcel as Service;
use LaravelEnso\Tables\Services\Data\Config;

class EnsoExcel extends Excel
{
    private DataExport $export;

    public function __construct(User $user, Config $config, string $table, DataExport $export)
    {
        parent::__construct($user, $config, $table);
        $this->export = $export;
    }

    public function handle()
    {
        (new Service($this->user, $this->table(), $this->config, $this->export))->handle();
    }
}
