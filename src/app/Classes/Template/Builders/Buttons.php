<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

class Buttons
{
    private const PathActions = ['href', 'ajax', 'export', 'action'];

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
                    ? $this->mapping($button)
                    : [$button, $button->type];

                if ($this->actionComputingFailes($button, $type)) {
                    return $buttons;
                }

                $buttons[$type][] = $button;

                if (!$this->template->actions && $type === 'row') {
                    $this->template->actions = true;
                }

                unset($button->fullRoute, $button->routeSuffix);

                return $buttons;
            }, ['global' => [], 'row' => []]);
    }

    private function mapping($button)
    {
        return collect($this->defaults['global'])->keys()->contains($button)
            ? [(object) $this->defaults['global'][$button], 'global']
            : [(object) $this->defaults['row'][$button], 'row'];
    }

    private function actionComputingFailes($button, $type)
    {
        if (!property_exists($button, 'action')) {
            return false;
        }

        $route = $this->route($button);

        if ($this->routeIsForbidden($route)) {
            return true;
        }

        if (collect(self::PathActions)->contains($button->action)) {
            $button->path = route($route, [$type === 'row' ? 'dtRowId' : null], false);

            return false;
        }

        $button->route = $route;

        return false;
    }

    private function route($button)
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
        if (empty(config('enso.config'))
            || (property_exists($this->template, 'auth') && $this->template->auth === false)) {
            return false;
        }

        return auth()->user()
            ->cannot('access-route', $route);
    }
}
