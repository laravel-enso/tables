<?php

namespace LaravelEnso\Tables\app\Services;

use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Data\Config;
use LaravelEnso\Tables\app\Services\Data\Fetcher;

abstract class Action
{
    private $fetcher;
    private $request;

    public function __construct(Table $table, Config $config)
    {
        $this->fetcher = new Fetcher($table, $config);
    }

    abstract public function process(array $row);

    public function handle()
    {
        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            $this->fetcher->current()
                ->each(fn($row) => $this->process($row));

            $this->fetcher->next();
        }

        return $this;
    }

    public function request()
    {
        return $this->request;
    }
}
