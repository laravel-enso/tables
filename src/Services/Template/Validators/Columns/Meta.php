<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Columns;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column as Attributes;
use LaravelEnso\Tables\Exceptions\Meta as Exception;

class Meta
{
    public static function validate(Obj $column)
    {
        $meta = $column->get('meta');

        $diff = $meta->diff(Attributes::Meta);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes($diff->implode('", "'));
        }

        if (
            Str::contains($column->get('name'), '.')
            && ($meta->contains('sortable'))
        ) {
            throw Exception::unsupported($column->get('name'));
        }

        if ($meta->has('filterable') && $meta->has('icon')) {
            throw Exception::cannotFilterIcon($column->get('name'));
        }
    }
}
