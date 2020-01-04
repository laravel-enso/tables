<?php

namespace LaravelEnso\Tables\App\Services;

use LaravelEnso\Tables\App\Contracts\Table;
use LaravelEnso\Tables\App\Services\Data\Config;
use LaravelEnso\Tables\App\Services\Data\Fetcher;

abstract class Action
{
    private Fetcher $fetcher;
    private $request;

    public function __construct(Table $table, Config $config)
    {
        $this->fetcher = new Fetcher($table, $config);
        $this->request = $config->request();
    }

    abstract public function process(array $row);

    public function handle()
    {
        $this->fetcher->next();

        while ($this->fetcher->valid()) {
            $this->fetcher->current()
                ->each(fn ($row) => $this->process($row));

            $this->fetcher->next();
        }

        return $this;
    }

    public function request()
    {
        return $this->request;
    }
}
