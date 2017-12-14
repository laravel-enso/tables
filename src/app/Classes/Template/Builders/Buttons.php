<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

class Buttons
{
    private const PathActions = ['href', 'ajax', 'export'];

    private $template;
    private $defaults;

    public function __construct($template)
    {
        $this->template = $template;
        $this->defaults = config('enso.datatable.buttons');
        $this->template->actions = false;
    }

    public function build()
    {
        $this->template->buttons = collect($this->template->buttons)
            ->reduce(function ($buttons, $button) {
                [$button, $type] = is_string($button)
                ? $this->getMapping($button)
                : [$button, $button->type];

                $valid = $this->computeRoute($button, $type);

                $buttons[$type][] = $button;

                if (!$this->template->actions && $type === 'row') {
                    $this->template->actions = true;
                }

                unset($button->fullRoute, $button->routeSuffix);

                return $buttons;
            }, ['global' => [], 'row' => []]);
    }

    private function getMapping($button)
    {
        return collect($this->defaults['global'])->keys()->contains($button)
            ? [(object) $this->defaults['global'][$button], 'global']
            : [(object) $this->defaults['row'][$button], 'row'];
    }

    private function computeRoute($button, $type)
    {
        if (!property_exists($button, 'action')) {
            return true;
        }

        $route = $this->getRoute($button);

        if ($this->routeIsForbidden($route)) {
            return false;
        }

        if (collect(self::PathActions)->contains($button->action)) {
            $button->path = route($route, [$type === 'row' ? 'dtRowId' : null], false);

            return true;
        }

        $button->route = $route;

        return true;
    }

    private function getRoute($button)
    {
        if (property_exists($button, 'fullRoute') && !is_null($button->fullRoute)) {
            return $button->fullRoute;
        }

        return property_exists($button, 'routeSuffix') && !is_null($button->routeSuffix)
            ? $this->template->routePrefix.'.'.$button->routeSuffix
            : null;
    }

    private function routeIsForbidden($route)
    {
        if (empty(config('enso.config'))) {
            return false;
        }

        return auth()->user()->cannot('access-route', $route);
    }
}
