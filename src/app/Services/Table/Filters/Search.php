<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Exceptions\QueryException;

class Search
{
    private $request;
    private $query;
    private $columns;

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;
        $this->query = $query;
        $this->columns = $request->get('columns');
    }

    public function handle()
    {
        if (! $this->request->get('meta')->filled('search')) {
            return false;
        }

        $this->searchArguments()->each(function ($argument) {
            $this->query->where(function ($query) use ($argument) {
                $this->match($query, $argument);
            });
        });

        return true;
    }

    private function searchArguments()
    {
        return $this->request->get('meta')->get('searchMode') === 'full'
            ? collect(
                    explode(' ', $this->request->get('meta')->get('search'))
                )->filter()
            : collect($this->request->get('meta')->get('search'));
    }

    private function match($query, $argument)
    {
        $this->columns->each(function ($column) use ($query, $argument) {
            if ($column->get('meta')->get('searchable')) {
                return $this->isNested($column->get('name'))
                    ? $this->whereHasRelation($query, $column->get('data'), $argument)
                    : $query->orWhere(
                        $column->get('data'),
                        $this->request->get('meta')->get('comparisonOperator'),
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
                $this->request->get('meta')->get('comparisonOperator'),
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
        switch ($this->request->get('meta')->get('searchMode')) {
            case 'full':
                return '%'.$argument.'%';
            case 'startsWith':
                return $argument.'%';
            case 'endsWith':
                return '%'.$argument;
            default:
                throw new QueryException(__('Unknown search mode'));
        }
    }


    private function isNested($attribute)
    {
        return Str::contains($attribute, '.');
    }
}
