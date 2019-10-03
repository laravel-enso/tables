<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;

abstract class BaseFilter
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
