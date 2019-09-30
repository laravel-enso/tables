<?php

namespace LaravelEnso\Tables\app\Contracts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;

interface Filter
{
    public function filter(Request $request, Builder $query, Table $table): bool;
}
