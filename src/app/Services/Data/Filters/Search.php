<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Illuminate\Support\Str;
use LaravelEnso\Tables\app\Exceptions\Query as Exception;

class Search extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->meta()->filled('search')
            && $this->searchable()->isNotEmpty();
    }

    public function handle()
    {
        $this->searchArguments()->each(fn($argument) => (
            $this->query->where(fn($query) => $this->matchArgument($query, $argument))
        ));
    }

    private function searchArguments()
    {
        $search = $this->config->meta()->get('search');

        return $this->config->meta()->get('searchMode') === 'full'
            ? collect(explode(' ', $search))->filter()
            : collect($search);
    }

    private function matchArgument($query, $argument)
    {
        $this->searchable()->each(fn($column) => $query->orWhere(fn($query) => $this->matchAttribute($query, $column->get('data'), $argument, $column->get('name'))));
    }

    private function matchAttribute($query, $attribute, $argument, $name = null)
    {
        $isNested = $this->isNested($name ?? $attribute);

        $query->when($isNested, function ($query) use ($attribute, $argument) {
            $attributes = collect(explode('.', $attribute));

            $query->whereHas($attributes->shift(), fn($query) => $this->matchAttribute($query, $attributes->implode('.'), $argument));
        })->when(! $isNested, fn($query) => $query->where(
            $attribute, $this->config->get('comparisonOperator'), $this->wildcards($argument)
        ));
    }

    private function wildcards($argument)
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

    private function searchable()
    {
        return $this->config->columns()->filter(fn($column) => $column->get('meta')->get('searchable'));
    }

    private function isNested($attribute)
    {
        return Str::contains($attribute, '.');
    }
}
