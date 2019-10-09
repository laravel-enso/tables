<?php

namespace LaravelEnso\Tables\app\Contracts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Config;

interface CustomFilter
{
    public function filter(Builder $query, Config $config): Builder;
}
