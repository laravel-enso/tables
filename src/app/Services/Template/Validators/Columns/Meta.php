<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Columns;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Column as Attributes;
use LaravelEnso\Tables\App\Exceptions\Meta as Exception;

class Meta
{
    public static function validate(Obj $column)
    {
        $attributes = $column->get('meta');

        $diff = $attributes->diff(Attributes::Meta);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes($diff->implode('", "'));
        }

        if (Str::contains($column->get('name'), '.')
            && ($attributes->contains('sortable'))) {
            throw Exception::unsupported($column->get('name'));
        }
    }
}
