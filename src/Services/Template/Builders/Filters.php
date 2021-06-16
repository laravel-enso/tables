<?php

namespace LaravelEnso\Tables\Services\Template\Builders;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;

class Filters
{
    public function __construct(
        private Obj $template,
        private Obj $meta
    ) {
    }

    public function build(): void
    {
        if (! $this->template->has('filters')) {
            return;
        }

        $withRoute = fn ($filter) => $filter->has('route');
        $allowed = fn ($filter) => ! $withRoute($filter) || $this->routeAllowed($filter->get('route'));

        $filters = $this->template->get('filters')->filter($allowed)
            ->map(fn ($filter) => $this->compute($filter));

        $this->template->set('filters', $filters);
        $this->meta->set('filterable', true);
    }

    private function compute(Obj $filter): Obj
    {
        $absolute = Config::get('enso.tables.absoluteRoutes');

        return $filter->when($filter->has('route'), fn ($filter) => $filter
            ->set('path', route($filter->get('route'), [], $absolute))
            ->forget('route'));
    }

    private function routeAllowed($route): bool
    {
        return ! $this->needAuthorization()
            || Auth::user()->can('access-route', $route);
    }

    private function needAuthorization()
    {
        return ! empty(Config::get('enso.config'))
            && $this->template->get('auth') !== false;
    }
}
