<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

use LaravelEnso\VueDatatable\app\Classes\Attributes\Meta as Attributes;

class Columns
{
    const Flags = ['total', 'enum'];

    private $template;

    public function __construct($template)
    {
        $this->template = $template;
        $this->setDefaults();
    }

    public function build()
    {
        $this->template->columns = collect($this->template->columns)
            ->reduce(function ($columns, $column) {
                $this->computeMeta($column);

                if (property_exists($column, 'enum')) {
                    $this->updateDefault('enum');
                }

                $columns->push($column);

                return $columns;
            }, collect());
    }

    private function computeMeta($column)
    {
        $column->meta = collect(Attributes::List)->reduce(function ($meta, $attribute) use ($column) {
            $this->updateDefault($attribute);
            $meta[$attribute] = property_exists($column, 'meta') && collect($column->meta)->contains($attribute);
            $meta['sort'] = null;

            return $meta;
        }, []);

        $column->meta['visible'] = true;
    }

    private function updateDefault($flag)
    {
        if (collect(self::Flags)->contains($flag) && !$this->template->{$flag}) {
            $this->template->{$flag} = true;
        }
    }

    private function setDefaults()
    {
        collect(self::Flags)->each(function ($flag) {
            $this->template->{$flag} = false;
        });
    }
}
