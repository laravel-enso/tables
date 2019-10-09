<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Contracts\Filter as TableFilter;

abstract class BaseFilter implements TableFilter
{
    protected $config;
    protected $query;
    protected $filters;

    public function __construct(Config $config, Builder $query)
    {
        $this->config = $config;
        $this->query = $query;
        $this->filters = false;
    }

    abstract public function handle(): bool;

    protected function parse($type)
    {
        return is_string($this->config->get($type))
            ? new Obj(json_decode($this->config->get($type), true))
            : $this->config->get($type);
    }
}
