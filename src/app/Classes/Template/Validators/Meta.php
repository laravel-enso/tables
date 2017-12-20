<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;
use LaravelEnso\VueDatatable\app\Classes\Attributes\Meta as Attributes;

class Meta
{
    public static function validate($meta)
    {
        $diff = collect($meta)
            ->diff(Attributes::List);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Unknown Meta Parameter(s): "%s"',
                $diff->implode('", "')
            )));
        }
    }
}
