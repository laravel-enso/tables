<?php

namespace LaravelEnso\Tables\app\Contracts;

use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Database\Eloquent\Builder;

interface CustomFilter
{
    public function filterApplies(Obj $params): bool;

    public function filter(Builder $query, Obj $params);
}
