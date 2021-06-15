<?php

namespace LaravelEnso\Tables\Services;

use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Fetcher;
use LaravelEnso\Tables\Services\Data\Request;

abstract class Action
{
    private Fetcher $fetcher;
    private Request $request;

    public function __construct(Table $table, Config $config)
    {
        $this->fetcher = new Fetcher($table, $config);
        $this->request = $config->request();
    }

    public function before(): void
    {
    }

    abstract public function process(array $row);

    public function after(): void
    {
    }

    public function handle(): void
    {
        $this->before();

        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            $this->fetcher->current()
                ->each(fn ($row) => $this->process($row));

            $this->fetcher->next();
        }

        $this->after();
    }

    public function request(): Request
    {
        return $this->request;
    }
}
