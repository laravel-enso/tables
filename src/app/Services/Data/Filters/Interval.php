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
                    $this->setMinLimit($table, $column, $value)
                        ->setMaxLimit($table, $column, $value);
                });
            });
        });
    }

    private function setMinLimit($table, $column, $value)
    {
        if ($this->isValid($value->get('min'))) {
            $this->query->where(
                $table.'.'.$column, '>=', $this->value($value, 'min')
            );
        }

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        if ($this->isValid($value->get('max'))) {
            $this->query->where(
                $table.'.'.$column, '<=', $this->value($value, 'max')
            );
        }

        return $this;
    }

    private function value($value, $bound)
    {
        if (! $value->has('dateFormat')) {
            return $value->get($bound);
        }

        return $value->get($bound)
            ? Carbon::createFromFormat(
                $this->format($value), $value->get($bound)
            ) : null;
    }

    private function format($value)
    {
        return '!'.(
            $value->get('dateFormat') ?? config('enso.config.dateTimeFormat')
        );
    }

    private function isValid($value)
    {
        return ! collect([null, ''])->containsStrict($value);
    }
}
