<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Config;

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
        if ($this->config->meta()->get('sort')) {
            $this->sort();
        }
    }

    private function sort()
    {
        $this->config->columns()->each(function ($column) {
            if ($column->get('meta')->get('sortable') && $column->get('meta')->get('sort')) {
                $column->get('meta')->get('nullLast')
                    ? $this->query->orderByRaw($this->rawSort($column))
                    : $this->query->orderBy(
                        $column->get('data'), $column->get('meta')->get('sort')
                    );
            }
        });
    }

    private function rawSort($column)
    {
        return "({$column->get('data')} IS NULL),"
            ."{$column->get('data')} {$column->get('meta')->get('sort')}";
    }
}
