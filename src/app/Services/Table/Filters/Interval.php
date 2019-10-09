<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Carbon\Carbon;

class Interval extends BaseFilter
{
    public function handle(): bool
    {
        if ($this->config->filled('intervals')) {
            $this->filter();
        }

        return $this->filters;
    }

    private function filter()
    {
        $this->query->where(function () {
            $this->parse('intervals')->each(function ($interval, $table) {
                collect($interval)
                    ->each(function ($value, $column) use ($table) {
                        $this->setMinLimit($table, $column, $value)
                            ->setMaxLimit($table, $column, $value);
                    });
            });
        });
    }

    private function setMinLimit($table, $column, $value)
    {
        if ($value->get('min') === null || $value->get('min') === '') {
            return $this;
        }

        $this->query->where($table.'.'.$column, '>=',
            $this->value($value, 'min'));

        $this->filters = true;

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        if ($value->get('max') === null || $value->get('max') === '') {
            return $this;
        }

        $this->query->where($table.'.'.$column, '<',
            $this->value($value, 'max'));

        $this->filters = true;

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
