<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Carbon\Carbon;

class Interval extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->intervals()->first(function ($interval) {
            return $interval->first(function ($value) {
                return $this->isValid($value->get('min'))
                    || $this->isValid($value->get('max'));
            }) !== null;
        }) !== null;
    }

    public function handle()
    {
        $this->query->where(function () {
            $this->config->intervals()->each(function ($interval, $table) {
                collect($interval)->each(function ($value, $column) use ($table) {
                    $this->limit($table, $column, $value, 'min', '>=')
                        ->limit($table, $column, $value, 'max', '<=');
                });
            });
        });
    }

    private function limit($table, $column, $value, $bound, $operator)
    {
        if ($this->isValid($value->get($bound))) {
            $this->query->where(
                $table.'.'.$column, $operator, $value->get($bound)
            );
        }

        return $this;
    }

    private function isValid($value)
    {
        return ! collect([null, ''])->containsStrict($value);
    }
}
