<?php

namespace LaravelEnso\Tables\app\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CustomFilter
{
    public function filter(Builder $query): Builder;
}
