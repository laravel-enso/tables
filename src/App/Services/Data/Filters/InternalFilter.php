<?php

namespace LaravelEnso\Tables\App\Services\Data\Filters;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelEnso\Filters\App\Enums\SearchModes;
use LaravelEnso\Filters\App\Services\Search;
use LaravelEnso\Helpers\App\Classes\Obj;

class InternalFilter extends BaseFilter
{
    private const Boolean = 'boolean';
    private const Date = 'date';
    private const Enum = 'enum';
    private const Money = 'money';
    private const String = 'string';

    public function applies(): bool
    {
        return $this->filterable()->isNotEmpty()
            && $this->filters()->isNotEmpty();
    }

    public function handle(): void
    {
        $this->query->where(fn () => $this->filters()
            ->each(fn ($filter) => $this->filter($filter)));
    }

    private function filter(Obj $filter): void
    {
        $search = (new Search($this->query, [$filter->get('data')], $filter->get('value')))
            ->comparisonOperator($this->config->get('comparisonOperator'));

        switch ($filter->get('type')) {
            case self::Boolean:
                $search->searchMode(SearchModes::ExactMatch)->handle();
                break;
            case self::Enum:
                $search->searchMode(SearchModes::StartsWith)->handle();
                break;
            case self::String:
                $search->searchMode($filter->get('mode'))->handle();
                break;
            default:
                throw new InvalidArgumentException();
                break;
        }
    }

    private function filterable(): Collection
    {
        return $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('filterable'));
    }

    private function filters(): Obj
    {
        return $this->config->internalFilters()->map(fn ($filters) => $filters
            ->filter(fn ($value) => $this->isValid($value)))
            ->filter->isNotEmpty();
    }

    private function isValid($value): bool
    {
        return ! (new Collection([null, '']))->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }
}
