<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Support\Str;
use LaravelEnso\Tables\app\Exceptions\QueryException;

class Search extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->meta()->filled('search');
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
        return $this->config->meta()->get('searchMode') === 'full'
            ? collect(
                    explode(' ', $this->config->meta()->get('search'))
                )->filter()
            : collect($this->config->meta()->get('search'));
    }

    private function match($query, $argument)
    {
        $this->config->columns()->each(function ($column) use ($query, $argument) {
            if ($column->get('meta')->get('searchable')) {
                return $this->isNested($column->get('name'))
                    ? $this->whereHasRelation($query, $column->get('data'), $argument)
                    : $query->orWhere(
                        $column->get('data'),
                        $this->config->get('comparisonOperator'),
                        $this->wildcards($argument)
                    );
            }
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

    private function isNested($attribute)
    {
        return Str::contains($attribute, '.');
    }
}
