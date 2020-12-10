<?php

namespace LaravelEnso\Tables\Exports;

use Illuminate\Foundation\Auth\User;
use LaravelEnso\Tables\Jobs\Excel as Job;
use LaravelEnso\Tables\Notifications\ExportStarted;
use LaravelEnso\Tables\Services\Data\Config;

class Prepare
{
    protected User $user;
    protected Config $config;
    protected string $table;

    public function __construct(User $user, Config $config, string $table)
    {
        $this->user = $user;
        $this->config = $config;
        $this->table = $table;
    }

    public function handle(): void
    {
        $this->notifyStart()
            ->dispatch();
    }

    private function notifyStart(): self
    {
        $this->user->notifyNow(
            (new ExportStarted($this->config->label()))
        );

        return $this;
    }

    protected function dispatch(): void
    {
        Job::dispatch($this->user, $this->config, $this->table);
    }
}
