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
                $this->match($query, $argument);
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

    private function match($query, $argument)
    {
        $this->searchable()->each(function ($column) use ($query, $argument) {
            return $this->isNested($column->get('name'))
                ? $this->whereHasRelation($query, $column->get('data'), $argument)
                : $query->orWhere(
                    $column->get('data'),
                    $this->config->get('comparisonOperator'),
                    $this->wildcards($argument)
                );
        });
    }

    private function whereHasRelation($query, $attribute, $argument)
    {
        if (! $this->isNested($attribute)) {
            $query->where(
                $attribute,
                $this->config->get('comparisonOperator'),
                $this->wildcards($argument)
            );

            return;
        }

        $attributes = collect(explode('.', $attribute));

        $query->orWhere(function ($query) use ($attributes, $argument) {
            $query->whereHas($attributes->shift(), function ($query) use ($attributes, $argument) {
                $this->whereHasRelation($query, $attributes->implode('.'), $argument);
            });
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
