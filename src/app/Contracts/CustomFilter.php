<?php

namespace LaravelEnso\Tables\app\Contracts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Helpers\app\Classes\Obj;

interface CustomFilter
{
    public function filterApplies(Obj $params): bool;

    public function filter(Builder $query, Obj $params);
}
