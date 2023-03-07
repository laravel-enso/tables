<?php

namespace LaravelEnso\Tables\Services\Data\Filters;

use LaravelEnso\Helpers\Services\Obj;

class Interval extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->intervals()
            ->some(fn ($column) => $column
                ->some(fn ($interval) => $interval->filled('min')
                    || $interval->filled('max')));
    }

    public function handle(): void
    {
        $this->query->where(fn () => $this->config->intervals()
            ->each(fn ($interval, $table) => $interval
                ->each(fn ($interval, $column) => $this
                    ->limit($table, $column, $interval))));
    }

    private function limit($table, $column, Obj $interval): self
    {
        $attribute = "{$table}.{$column}";

        if ($interval->filled('min')) {
            $this->query->where($attribute, '>=', $interval->get('min'));
        }

        if ($interval->filled('max')) {
            $this->query->where($attribute, '<=', $interval->get('max'));
        }

        return $this;
    }
}
