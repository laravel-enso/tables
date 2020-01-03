<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Button as Attributes;
use LaravelEnso\Tables\App\Exceptions\Button as Exception;

class Buttons
{
    private $buttons;
    private $routePrefix;
    private $defaults;

    public function __construct(Obj $template)
    {
        $this->buttons = $template->get('buttons');
        $this->routePrefix = $template->get('routePrefix');

        $this->defaults();
    }

    public function validate()
    {
        $this->checkFormat()
            ->checkDefault()
            ->checkStructure();
    }

    private function checkFormat()
    {
        $formattedWrong = $this->buttons
            ->filter(fn ($button) => ! is_string($button) && ! $button instanceof Obj);

        if ($formattedWrong->isNotEmpty()) {
            throw Exception::wrongFormat();
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = $this->buttons->filter(fn ($button) => is_string($button))
            ->diff($this->defaults->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::undefined($diff->implode('", "'));
        }

        return $this;
    }

    private function checkStructure()
    {
        $this->buttons
            ->map(fn ($button) => $button instanceof Obj
                ? $button
                : $this->defaults->get($button)
            )->each(fn ($button) => $this->checkAttributes($button));

        return $this;
    }

    private function checkAttributes($button)
    {
        $this->checkMandatoryAttributes($button)
            ->checkOptionalAttributes($button)
            ->checkComplementaryAttributes($button)
            ->checkActions($button)
            ->checkRoute($button)
            ->checkMethod($button);
    }

    private function checkMandatoryAttributes($button)
    {
        $formattedWrong = (new Collection(Attributes::Mandatory))
            ->diff($button->keys())
            ->isNotEmpty();

        if ($formattedWrong) {
            throw Exception::missingAttributes();
        }

        return $this;
    }

    private function checkOptionalAttributes($button)
    {
        $formattedWrong = $button->keys()
            ->diff(Attributes::Mandatory)
            ->diff(Attributes::Optional)
            ->isNotEmpty();

        if ($formattedWrong) {
            throw Exception::unknownAttributes();
        }

        return $this;
    }

    private function checkComplementaryAttributes($button)
    {
        if ($button->has('action')) {
            if (! $button->has('fullRoute') && ! $button->has('routeSuffix')) {
                throw Exception::missingRoute();
            }

            if ($button->get('action') === 'ajax' && ! $button->has('method')) {
                throw Exception::missingMethod();
            }
        }

        return $this;
    }

    private function checkActions($button)
    {
        $formattedWrong = $button->has('action')
            && ! (new Collection(Attributes::Actions))->contains($button->get('action'));

        if ($formattedWrong) {
            throw Exception::wrongAction();
        }

        return $this;
    }

    private function checkRoute($button)
    {
        $route = $button->get('fullRoute');

        $route = $route === null && $button->has('routeSuffix')
            && $button->get('routeSuffix') !== null
                ? $this->routePrefix.'.'.$button->get('routeSuffix')
                : $route;

        if ($route !== null && ! Route::has($route)) {
            throw Exception::routeNotFound($route);
        }

        return $this;
    }

    private function checkMethod($button)
    {
        if (! $button->has('method')) {
            return;
        }

        if (! (new Collection(Attributes::Methods))->contains($button->get('method'))) {
            throw Exception::invalidMethod($button->get('method'));
        }

        return $this;
    }

    private function defaults()
    {
        $row = (new Obj(config('enso.tables.buttons.row')))
            ->map(fn ($button) => $button->set('type', 'row'));

        $global = (new Obj(config('enso.tables.buttons.global')))
            ->map(fn ($button) => $button->set('type', 'global'));

        $this->defaults = $global->merge($row);
    }
}
