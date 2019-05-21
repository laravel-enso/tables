<?php

namespace LaravelEnso\Tables\app\Services\Template\Builders;

use LaravelEnso\Helpers\app\Classes\Obj;

class Buttons
{
    private const PathActions = ['href', 'ajax', 'export', 'action'];

    private $template;
    private $meta;
    private $defaults;

    public function __construct(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;
        $this->defaults = new Obj(config('enso.tables.buttons'));
        $this->template->set('actions', false);
    }

    public function build()
    {
        $buttons = $this->template->get('buttons')
            ->reduce(function ($buttons, $button) {
                [$button, $type] = is_string($button)
                    ? $this->mapping($button)
                    : [$button, $button->get('type')];

                if ($this->actionComputingFailes($button, $type)) {
                    return $buttons;
                }

                $buttons[$type]->push($button);

                if ($type === 'row') {
                    $this->template->set('actions', true);
                }

                $button->forget(['fullRoute', 'routeSuffix']);

                return $buttons;
            }, collect(['global' => collect(), 'row' => collect()]));
        $this->template->set('buttons', $buttons);
    }

    private function mapping($button)
    {
        return $this->defaults->get('global')->keys()->contains($button)
            ? [$this->defaults->get('global')->get($button), 'global']
            : [$this->defaults->get('row')->get($button), 'row'];
    }

    private function actionComputingFailes($button, $type)
    {
        if (! $button->has('action')) {
            return false;
        }

        $route = $this->route($button);

        if ($this->routeIsForbidden($route)) {
            return true;
        }

        if (collect(self::PathActions)->contains($button->get('action'))) {
            $button->set(
                'path',
                 route($route, [$type === 'row' ? 'dtRowId' : null], false)
            );

            return false;
        }

        $button->set('route', $route);

        return false;
    }

    private function route($button)
    {
        if ($button->has('fullRoute')
            && $button->get('fullRoute') !== null) {
            return $button->get('fullRoute');
        }

        return $button->has('routeSuffix')
            && $button->get('routeSuffix') !== null
            ? $this->template->get('routePrefix')
                .'.'.$button->get('routeSuffix')
            : null;
    }

    private function routeIsForbidden($route)
    {
        if (empty(config('enso.config'))
            || ($this->template->has('auth') && $this->template->get('auth') === false)) {
            return false;
        }

        return auth()->user()
            ->cannot('access-route', $route);
    }
}
