<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Carbon\Carbon;

class Interval extends BaseFilter
{
    public function applies(): bool
    {
        $empty = collect([null, '']);

        return $this->config->intervals()->first(function ($interval) use ($empty) {
            return $interval->first(function ($value) use ($empty) {
                return ! $empty->contains($value->get('min'))
                    || ! $empty->contains($value->get('max'));
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
        if ($value->get('min') !== null) {
            $this->query->where(
                $table.'.'.$column, '>=', $this->value($value, 'min')
            );
        }

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        if ($value->get('max') !== null) {
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
                $value->get('dateFormat') ?? config('enso.config.dateFormat'),
                $value->get($bound)
            ) : null;
    }
}
