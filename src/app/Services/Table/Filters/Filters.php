<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use App;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Table\Request;

class Filters
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

    public function __construct(Request $request, Builder $query, Table $table)
    {
        $this->table = $table;
        $this->request = $request;
        $this->query = $query;
    }

    public static function setFilters($filters)
    {
        self::$filters = $filters;
    }

    public function handle()
    {
        return collect(self::$filters)
            ->reduce(function ($isFiltered, $class) {
                return $this->filter($class) || $isFiltered;
            }, false);
    }

    private function filter($class)
    {
        return App::make($class, [
            'request' => $this->request,
            'query' => $this->query,
            'table' => $this->table,
        ])->handle();
    }
}
