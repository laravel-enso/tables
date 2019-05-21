<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
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
            throw new TemplateException(__('The buttons array may contain only strings and objects.'));
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = $this->buttons->filter(function ($button) {
            return is_string($button);
        })->diff($this->defaults->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Button(s) Found: ":button"',
                ['button' => $diff->implode('", "')]
            ));
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
            throw new TemplateException(__(
                'The following attributes are mandatory for custom buttons: ":attr"',
                ['attr' => collect(Attributes::Mandatory)->implode('", "')]
            ));
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
            throw new TemplateException(__(
                'The following optional attributes are allowed for custom buttons: ":button"',
                ['button' => collect(Attributes::Optional)->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkComplementaryAttributes($button)
    {
        if ($button->has('action')) {
            if (! $button->has('fullRoute') && ! $button->has('routeSuffix')) {
                throw new TemplateException(__(
                    'Whenever you set an action for a button you need to provide the fullRoute or routeSuffix'
                ));
            }

            if ($button->get('action') === 'ajax' && ! $button->has('method')) {
                throw new TemplateException(__(
                    'Whenever you set an ajax action for a button you need to provide the method aswell'
                ));
            }
        }

        return $this;
    }

    private function checkActions($button)
    {
        $formattedWrong = $button->has('action')
            && ! collect(Attributes::Actions)->contains($button->get('action'));

        if ($formattedWrong) {
            throw new TemplateException(__(
                'The following actions are allowed for custom buttons: ":actions"',
                ['actions' => collect(Attributes::Actions)->implode('", "')]
            ));
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
            throw new TemplateException(__(
                'Button route does not exist: ":route"',
                ['route' => $route]
            ));
        }

        return $this;
    }

    private function checkMethod($button)
    {
        if (! $button->has('method')) {
            return;
        }

        if (! collect(Attributes::Methods)->contains($button->get('method'))) {
            throw new TemplateException(__(
                'Method is incorrect: ":method"',
                ['method' => $button->get('method')]
            ));
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
