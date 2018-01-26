<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

use Carbon\Carbon;

class DateComputor
{
    private $columns;
    private $data;
    private $withDate;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;

        $this->setWithDate();
    }

    public function get()
    {
        return collect($this->data)->map(function ($record) {
            $this->withDate->each(function ($column) use (&$record) {
                $record[$column->name] = Carbon::parse($record[$column->name])
                    ->format(config('enso.datatable.dateFormat'));
            });

            return $record;
        })->toArray();
    }

    private function setWithDate()
    {
        $this->withDate = collect();

        $this->columns->each(function ($column) {
            if ($column->meta->date) {
                $this->withDate->push($column);
            }
        });
    }
}
