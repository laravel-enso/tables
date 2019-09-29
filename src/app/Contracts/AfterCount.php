<?php

namespace LaravelEnso\Tables\app\Contracts;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;

interface AfterCount
{
    public function afterCount(QueryBuilder $query);
}
