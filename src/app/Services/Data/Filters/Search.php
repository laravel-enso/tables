<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Illuminate\Support\Str;
use LaravelEnso\Tables\app\Exceptions\QueryException;

class Search extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->meta()->filled('search')
            && $this->searchable()->isNotEmpty();
    }

    public function handle()
    {
        $this->searchArguments()->each(function ($argument) {
            $this->query->where(function ($query) use ($argument) {
                $this->matchArgument($query, $argument);
            });
        });
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
        $this->searchable()->each(function ($column) use ($query, $argument) {
            $query->orWhere(function ($query) use ($column, $argument) {
                $this->matchAttribute($query, $column->get('data'), $argument, $column->get('name'));
            });
        });
    }

    private function matchAttribute($query, $attribute, $argument, $name = null)
    {
        $isNested = $this->isNested($name ?? $attribute);

        $query->when($isNested, function ($query) use ($attribute, $argument) {
            $attributes = collect(explode('.', $attribute));

            $query->whereHas($attributes->shift(), function ($query) use ($attributes, $argument) {
                $this->matchAttribute($query, $attributes->implode('.'), $argument);
            });
        })->when(! $isNested, function ($query) use ($attribute, $argument) {
            $query->where(
                $attribute, $this->config->get('comparisonOperator'), $this->wildcards($argument)
            );
        });
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
                throw QueryException::unknownSearchMode();
        }
    }

    private function searchable()
    {
        return $this->config->columns()->filter(function ($column) {
            return $column->get('meta')->get('searchable');
        });
    }

    private function isNested($attribute)
    {
        return Str::contains($attribute, '.');
    }
}
