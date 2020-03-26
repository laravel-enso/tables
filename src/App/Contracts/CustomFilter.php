<?php

namespace LaravelEnso\Tables\App\Contracts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Helpers\App\Classes\Obj;

interface CustomFilter
{
    public function filterApplies(Obj $params): bool;

    public function filter(Builder $query, Obj $params);
}
