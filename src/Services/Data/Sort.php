<?php

namespace LaravelEnso\Tables\Services\Data;

use Illuminate\Database\Eloquent\Builder;

class Sort
{
    private Config $config;
    private Builder $query;

    public function __construct(Config $config, Builder $query)
    {
        $this->config = $config;
        $this->query = $query;
    }

    public function handle(): void
    {
        $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('sortable') && $column->get('meta')->get('sort'))
            ->each(fn ($column) => $this->query->when(
                $column->get('meta')->get('nullLast'),
                fn ($query) => $query->orderByRaw($this->rawSort($column)),
                fn ($query) => $query
                    ->orderBy($column->get('data'), $column->get('meta')->get('sort'))
            ));
    }

    private function rawSort($column): string
    {
        $data = $column->get('data');
        $sort = $column->get('meta')->get('sort');

        return "({$data} IS NULL), {$data} {$sort}";
    }
}
