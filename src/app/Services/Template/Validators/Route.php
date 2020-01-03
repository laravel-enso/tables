<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators;

use Illuminate\Support\Facades\Route as RouteFacade;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Route as Exception;

class Route
{
    private $readRoute;

    public function __construct(Obj $template)
    {
        $this->readRoute = $template->get('routePrefix').'.'
            .($template->has('dataRouteSuffix')
                ? $template->get('dataRouteSuffix')
                : config('enso.tables.dataRouteSuffix'));
    }

    public function validate()
    {
        if (! RouteFacade::has($this->readRoute)) {
            throw Exception::notFound($this->readRoute);
        }
    }
}
