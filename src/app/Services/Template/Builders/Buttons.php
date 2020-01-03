<?php

namespace LaravelEnso\Tables\App\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use LaravelEnso\Helpers\App\Classes\Obj;

class Buttons
{
    private const PathActions = ['href', 'ajax', 'export', 'action'];

    private Obj $template;
    private Obj $defaults;

    public function __construct(Obj $template)
    {
        $this->template = $template;
        $this->defaults = new Obj(config('enso.tables.buttons'));
        $this->template->set('actions', false);
    }

    public function build(): void
    {
        $buttons = new Collection(['global' => new Collection(), 'row' => new Collection()]);

        $buttons = $this->template->get('buttons')
            ->reduce(fn ($buttons, $button) => $this
                ->addButton($buttons, $button), $buttons);

        $this->template->set('buttons', $buttons);
        $this->template->set('actions', $buttons->get('row')->isNotEmpty());
    }

    private function mapping($button): array
    {
        return $this->defaults->get('global')->keys()->contains($button)
            ? [$this->defaults->get('global')->get($button), 'global']
            : [$this->defaults->get('row')->get($button), 'row'];
    }

    private function actionComputingFailes($button, $type): bool
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
                'path', route($route, [$type === 'row' ? 'dtRowId' : null], false)
            );

            return false;
        }

        $button->set('route', $route);

        return false;
    }

    private function route($button): ?string
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

    private function routeIsForbidden($route): bool
    {
        if (empty(config('enso.config'))
            || ($this->template->has('auth') && $this->template->get('auth') === false)) {
            return false;
        }

        return Auth::user()
            ->cannot('access-route', $route);
    }

    private function addButton($buttons, $button): Collection
    {
        [$button, $type] = is_string($button)
            ? $this->mapping($button)
            : [$button, $button->get('type')];

        if ($this->actionComputingFailes($button, $type)) {
            return $buttons;
        }

        $buttons[$type]->push($button);

        $button->forget(['fullRoute', 'routeSuffix']);

        return $buttons;
    }
}
