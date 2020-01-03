<?php

namespace LaravelEnso\Tables\App\Services\Data\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\App\Contracts\Filter;
use LaravelEnso\Tables\App\Contracts\Table;
use LaravelEnso\Tables\App\Services\Data\Config;

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
