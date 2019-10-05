<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Contracts\Filter as TableFilter;

abstract class BaseFilter implements TableFilter
{
    protected $request;
    protected $query;
    protected $filters;

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;
        $this->query = $query;
        $this->filters = false;
    }

    abstract public function handle(): bool;
}
