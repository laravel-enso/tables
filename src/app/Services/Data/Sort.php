<?php

namespace LaravelEnso\Tables\App\Services\Data;

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
        $this->config->columns()
            ->filter(fn ($column) => $column->get('meta')->get('sortable')
                && $column->get('meta')->get('sort')
            )->each(fn ($column) => $column->get('meta')->get('nullLast')
                ? $this->query->orderByRaw($this->rawSort($column))
                : $this->query->orderBy(
                    $column->get('data'), $column->get('meta')->get('sort')
                )
            );
    }

    private function rawSort($column): string
    {
        return "({$column->get('data')} IS NULL), {$column->get('data')} {$column->get('meta')->get('sort')}";
    }
}
