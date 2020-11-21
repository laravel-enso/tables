<?php

namespace LaravelEnso\Tables\Services\Data\Filters;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Filters\Services\Search as Service;

class Search extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->meta()->filled('search')
            && $this->searchable()->isNotEmpty();
    }

    public function handle(): void
    {
        $search = $this->config->meta()->get('search');

        (new Service($this->query, $this->attributes(), $search))
            ->relations($this->relations())
            ->comparisonOperator($this->config->get('comparisonOperator'))
            ->searchMode($this->config->meta()->get('searchMode'))
            ->handle();
    }

    private function searchable(): Collection
    {
        return $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('searchable'));
    }

    private function attributes(): array
    {
        return $this->searchable()
            ->reject(fn ($column) => $this->isNested($column->get('name')))
            ->map(fn ($column) => $column->get('data'))
            ->toArray();
    }

    private function relations(): array
    {
        return $this->searchable()
            ->filter(fn ($column) => $this->isNested($column->get('name')))
            ->map(fn ($column) => $column->get('data'))
            ->toArray();
    }

    private function isNested($attribute): bool
    {
        return Str::of($attribute)->contains('.');
    }
}
