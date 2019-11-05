<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use Illuminate\Support\Facades\Route as RouteFacade;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\RouteException;

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
        if (! RouteFacade::has($this->readRoute)) {
            throw RouteException::notFound($this->readRoute);
        }
    }
}
