<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Data\Config;
use LaravelEnso\Tables\app\Contracts\Filter;


abstract class BaseFilter implements Filter
{
    protected $table;
    protected $config;
    protected $query;

    public function __construct(Table $table, Config $config, Builder $query)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $query;
    }

    abstract public function applies(): bool;

    abstract public function handle();
}
