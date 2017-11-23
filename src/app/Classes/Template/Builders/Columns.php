<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

use LaravelEnso\VueDatatable\app\Classes\Attributes\Meta as Attributes;

class Columns
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function build()
    {
        $this->template->columns = collect($this->template->columns)
            ->reduce(function ($columns, $column) {
                $this->computeMeta($column);

                if (property_exists($column, 'enum')) {
                    $this->template->enum = true;
                }

                $columns->push($column);

                return $columns;
            }, collect());
    }

    private function computeMeta($column)
    {
        $column->meta = collect(Attributes::List)->reduce(function ($meta, $attribute) use ($column) {
            if ($attribute === 'total' && collect($column->meta)->contains('total')) {
                $this->template->total = true;
            }

            $meta[$attribute] = property_exists($column, 'meta') && collect($column->meta)->contains($attribute);
            $meta['sort'] = null;

            return $meta;
        }, []);

        $column->meta['visible'] = true;
    }
}
