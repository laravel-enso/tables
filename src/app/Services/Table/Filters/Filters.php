<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use App;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Contracts\Filter AS TableFilter;

class Filters implements TableFilter
{
    private static $filters = [
        Filter::class,
        Interval::class,
        Search::class,
        CustomFilter::class,
    ];

    private $request;
    private $query;
    private $table;

    public function filter(Request $request, Builder $query, Table $table): bool
    {
        $this->table = $table;
        $this->request = $request;
        $this->query = $query;

        return $this->handle();
    }

    public static function setFilters($filters)
    {
        self::$filters = $filters;
    }

    public function handle()
    {
        return collect(self::$filters)
            ->reduce(function ($isFiltered, $class) {
                return $this->makeFilter($class) || $isFiltered;
            }, false);
    }

    private function makeFilter($class)
    {
        return App::make($class)->filter(
            $this->request,
            $this->query,
            $this->table
        );
    }
}
