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
                $this->computeMeta($column)
                    ->computeDefaultSort($column)
                    ->updateTemplate($column);
                $columns->push($column);

                return $columns;
            }, collect());
    }

    private function computeMeta($column)
    {
        if (! isset($column->meta)) {
            $column->meta = [];
        }

        $column->meta = collect(Attributes::List)
            ->reduce(function ($meta, $attribute) use ($column) {
                $meta[$attribute] = collect($column->meta)
                    ->contains($attribute);

                return $meta;
            }, []);

        $column->meta['visible'] = true;
        $column->meta['hidden'] = false;

        return $this;
    }

    private function computeDefaultSort($column)
    {
        if ($column->meta['sort:ASC']) {
            $column->meta['sort'] = 'ASC';
        } elseif ($column->meta['sort:DESC']) {
            $column->meta['sort'] = 'DESC';
        }

        if (! isset($column->meta['sort'])) {
            $column->meta['sort'] = null;
        }

        unset($column->meta['sort:ASC'], $column->meta['sort:DESC']);

        return $this;
    }

    private function updateTemplate($column)
    {
        if ($column->meta['searchable']) {
            $this->template->searchable = true;
        }

        if ($column->meta['sort']) {
            $this->template->sort = true;
        }

        if ($column->meta['total'] || $column->meta['customTotal']) {
            $this->template->total = true;
        }

        if ($column->meta['date']) {
            $this->template->date = true;
        }

        if (property_exists($column, 'enum')) {
            $this->template->enum = true;
        }

        if (property_exists($column, 'money')) {
            $this->template->money = true;
        }
    }
}
