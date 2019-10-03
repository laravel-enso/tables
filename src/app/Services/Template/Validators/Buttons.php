<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\ButtonException;
use LaravelEnso\Tables\app\Attributes\Button as Attributes;

class Buttons
{
    private $buttons;
    private $routePrefix;
    private $defaults;

    public function __construct(Obj $template)
    {
        $this->buttons = $template->get('buttons');
        $this->routePrefix = $template->get('routePrefix');

        $this->setDefaults();
    }

    public function validate()
    {
        $this->checkFormat()
            ->checkDefault()
            ->checkStructure();
    }

    private function checkFormat()
    {
        $formattedWrong = $this->buttons->filter(function ($button) {
            return ! is_string($button) && ! $button instanceof Obj;
        });

        if ($formattedWrong->isNotEmpty()) {
            throw ButtonException::wrongFormat();
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = $this->buttons->filter(function ($button) {
            return is_string($button);
        })->diff($this->defaults->keys());

        if ($diff->isNotEmpty()) {
            throw ButtonException::undefined($diff->implode('", "'));
        }

        return $this;
    }

    private function checkStructure()
    {
        $this->buttons->each(function ($button) {
            $button = $button instanceof Obj
                ? $button
                : $this->defaults->get($button);

            $this->checkAttributes($button);
        });

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
        $formattedWrong = collect(Attributes::Mandatory)
            ->diff($button->keys())
            ->isNotEmpty();

        if ($formattedWrong) {
            throw ButtonException::missingAttributes();
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
            throw ButtonException::unknownAttributes();
        }

        return $this;
    }

    private function checkComplementaryAttributes($button)
    {
        if ($button->has('action')) {
            if (! $button->has('fullRoute') && ! $button->has('routeSuffix')) {
                throw ButtonException::missingRoute();
            }

            if ($button->get('action') === 'ajax' && ! $button->has('method')) {
                throw ButtonException::missingMethod();
            }
        }

        return $this;
    }

    private function checkActions($button)
    {
        $formattedWrong = $button->has('action')
            && ! collect(Attributes::Actions)->contains($button->get('action'));

        if ($formattedWrong) {
            throw ButtonException::wrongAction();
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
            throw ButtonException::routeNotFound($route);
        }

        return $this;
    }

    private function checkMethod($button)
    {
        if (! $button->has('method')) {
            return;
        }

        if (! collect(Attributes::Methods)->contains($button->get('method'))) {
            throw ButtonException::invalidMethod($button->get('method'));
        }

        return $this;
    }

    private function setDefaults()
    {
        $this->defaults = (new Obj(config('enso.tables.buttons.global')))
            ->map(function ($button) {
                return $button->set('type', 'global');
            })->merge(
                (new Obj(config('enso.tables.buttons.row')))
                    ->map(function ($button) {
                        return $button->set('type', 'row');
                    })
            );
    }
}
