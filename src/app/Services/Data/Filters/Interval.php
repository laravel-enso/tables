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
        $this->query->where($table.'.'.$column, '>=',
            $this->value($value, 'min'));

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        $this->query->where($table.'.'.$column, '<',
            $this->value($value, 'max'));

        return $this;
    }

    private function value($value, $bound)
    {
        $dbDateFormat = $value->get('dbDateFormat');

        if ($dbDateFormat) {
            $dateFormat = $value->get('dateFormat')
                ?? config('enso.config.dateFormat');

            return Carbon::createFromFormat($dateFormat, $value->get($bound))
                ->format($dbDateFormat);
        }

        return $value->get($bound);
    }
}
