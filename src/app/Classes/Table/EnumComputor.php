<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

class EnumComputor
{
    private $request;
    private $data;
    private $withEnum;

    public function __construct($data, $request)
    {
        $this->data = $data;

        $this->request = $request;
        $this->setWithEnum();
    }

    public function run()
    {
        $this->data->map(function ($record) {
            $this->withEnum->each(function ($column) use ($record) {
                $record->{$column->name} = $column->enum::get($record->{$column->name});
            });
        });
    }

    private function setWithEnum()
    {
        $this->withEnum = collect();

        collect($this->request->get('columns'))
            ->each(function ($column) {
                $column = json_decode($column);

                if (property_exists($column, 'enum')) {
                    $this->withEnum->push($column);
                }
            });
    }
}
