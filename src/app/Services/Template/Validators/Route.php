<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;

class Route
{
    private $readRoute;

    public function __construct(Obj $template)
    {
        $this->readRoute = $template->get('routePrefix').'.'.(
            $template->has('dataRouteSuffix')
                ? $template->get('dataRouteSuffix')
                : config('enso.tables.dataRouteSuffix')
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
