<?php

namespace LaravelEnso\Tables\app\Contracts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;

interface CustomFilter
{
    public function filter(Builder $query, Request $request): Builder;
}
