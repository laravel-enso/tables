<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Support\Facades\Route as RouteFacade;
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
