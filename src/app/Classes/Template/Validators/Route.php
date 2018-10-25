<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Route
{
    private $readRoute;

    public function __construct($template)
    {
        $this->readRoute = $template->routePrefix.'.'.(
            $template->dataRouteSuffix
                ?? config('enso.datatable.dataRouteSuffix')
        );
    }

    public function validate()
    {
        if (! \Route::has($this->readRoute)) {
            throw new TemplateException(__(
                'Read route does not exist: ":route"',
                ['route' => $this->readRoute]
            ));
        }
    }
}
