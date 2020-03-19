<?php

namespace LaravelEnso\Tables\App\Services\Data\Filters;

use Illuminate\Database\Eloquent\Builder;
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
        $this->searchArguments()
            ->each(fn ($argument) => $this->query->where(
                fn ($query) => $this->matchArgument($query, $argument)
            ));
    }

    private function searchArguments(): Collection
    {
        $search = $this->config->meta()->get('search');

        return $this->config->meta()->get('searchMode') === 'full'
            ? (new Collection(explode(' ', $search)))->filter()
            : (new Collection($search));
    }

    private function matchArgument(Builder $query, string $argument): void
    {
        $this->searchable()->each(
            fn ($column) => $query->orWhere(
                fn ($query) => $this->matchAttribute(
                    $query,
                    $column->get('data'),
                    $argument,
                    $column->get('name')
                )
            )
        );
    }

    private function matchAttribute(Builder $query, string $attribute, string $argument, ?string $name = null): void
    {
        $nested = $this->isNested($name ?? $attribute);

        $query->when($nested, fn ($query) => $this
            ->matchSegments($query, $attribute, $argument))
            ->when(! $nested, fn ($query) => $query->where(
                $attribute,
                $this->config->get('comparisonOperator'),
                $this->wildcards($argument)
            ));
    }

    private function matchSegments(Builder $query, string $attribute, string $argument): void
    {
        $attributes = new Collection(explode('.', $attribute));

        $query->whereHas($attributes->shift(), fn ($query) => $this
            ->matchAttribute($query, $attributes->implode('.'), $argument));
    }

    private function wildcards(string $argument): string
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
