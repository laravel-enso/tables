<?php

namespace LaravelEnso\Tables\App\Services\Data\Filters;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Tables\App\Exceptions\Query as Exception;

class Search extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->meta()->filled('search')
            && $this->searchable()->isNotEmpty();
    }

    public function handle(): void
    {
        $this->searchArguments()->each(fn ($argument) => $this
            ->query->where(fn ($query) => $this->matchArgument($query, $argument))
        );
    }

    private function searchArguments(): Collection
    {
        $search = $this->config->meta()->get('search');

        return $this->config->meta()->get('searchMode') === 'full'
            ? (new Collection(explode(' ', $search)))->filter()
            : (new Collection($search));
    }

    private function matchArgument($query, $argument): void
    {
        $this->searchable()->each(fn ($column) => $query
            ->orWhere(fn ($query) => $this
                ->matchAttribute($query, $column->get('data'), $argument, $column->get('name'))
            )
        );
    }

    private function matchAttribute($query, $attribute, $argument, $name = null): void
    {
        $isNested = $this->isNested($name ?? $attribute);

        $query->when($isNested, function ($query) use ($attribute, $argument) {
            $attributes = new Collection(explode('.', $attribute));

            $query->whereHas($attributes->shift(), fn ($query) => $this
                ->matchAttribute($query, $attributes->implode('.'), $argument));
        })->when(! $isNested, fn ($query) => $query->where(
            $attribute, $this->config->get('comparisonOperator'), $this->wildcards($argument)
        ));
    }

    private function wildcards($argument): string
    {
        switch ($this->config->meta()->get('searchMode')) {
            case 'full':
                return '%'.$argument.'%';
            case 'startsWith':
                return $argument.'%';
            case 'endsWith':
                return '%'.$argument;
            default:
                throw Exception::unknownSearchMode();
        }
    }

    private function searchable(): Collection
    {
        return $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('searchable'));
    }

    private function isNested($attribute): bool
    {
        return Str::contains($attribute, '.');
    }
}
