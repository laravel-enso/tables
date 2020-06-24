<?php

namespace LaravelEnso\Tables\Services\Data\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\Contracts\Filter;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Config;

abstract class BaseFilter implements Filter
{
    protected Table $table;
    protected Config $config;
    protected Builder $query;

    public function __construct(Table $table, Config $config, Builder $query)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $query;
    }

    abstract public function applies(): bool;

    abstract public function handle(): void;
}
