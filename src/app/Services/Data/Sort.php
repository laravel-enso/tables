<?php

namespace LaravelEnso\Tables\app\Services\Data;

use Illuminate\Database\Eloquent\Builder;

class Sort
{
    private $config;
    private $query;

    public function __construct(Config $config, Builder $query)
    {
        $this->config = $config;
        $this->query = $query;
    }

    public function handle()
    {
        $this->config->columns()
            ->filter(function ($column) {
                return $column->get('meta')->get('sortable')
                    && $column->get('meta')->get('sort');
            })->each(function ($column, $index) {
                $this->query->orderByRaw($this->rawSort($column));
            });
    }

    private function rawSort($column)
    {
        $sort = "{$column->get('data')} {$column->get('meta')->get('sort')}";

        return $column->get('meta')->get('nullLast')
            ? "({$column->get('data')} IS NULL), ".$sort
            : $sort;
    }
}
