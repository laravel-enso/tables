<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Carbon\Carbon;
use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;

class Interval
{
    private $request;
    private $query;
    private $filters;

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;
        $this->query = $query;
        $this->filters = false;
    }

    public function handle()
    {
        $this->applyIntervals();

        return $this->filters;
    }

    private function applyIntervals()
    {
        if (! $this->request->filled('intervals')) {
            return $this;
        }

        $this->query->where(function () {
            $this->parse('intervals')->each(function ($interval, $table) {
                collect($interval)
                    ->each(function ($value, $column) use ($table) {
                        $this->setMinLimit($table, $column, $value)
                            ->setMaxLimit($table, $column, $value);
                    });
            });
        });

        return $this;
    }

    private function setMinLimit($table, $column, $value)
    {
        if ($value->get('min') === null) {
            return $this;
        }

        $this->query->where($table.'.'.$column, '>=',
            $this->value($value, 'min'));

        $this->filters = true;

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        if ($value->get('max') === null) {
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
                ->format($dbDateFormat) ;
        }

        return $value->get($bound);
    }

    private function parse($type)
    {
        return is_string($this->request->get($type))
            ? new Obj(json_decode($this->request->get($type), true))
            : $this->request->get($type);
    }
}
