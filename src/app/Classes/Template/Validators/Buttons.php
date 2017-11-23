<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use LaravelEnso\VueDatatable\app\Classes\Attributes\Button as Attributes;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Buttons
{
    private $buttons;
    private $routePrefix;
    private $defaults;

    public function __construct($template)
    {
        $this->buttons = $template->buttons;
        $this->routePrefix = $template->routePrefix;

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
        $formattedWrong = collect($this->buttons)
            ->filter(function ($button) {
                return !is_string($button) && !is_object($button);
            });

        if ($formattedWrong->isNotEmpty()) {
            throw new TemplateException(__('The buttons array may contain only strings and objects.'));
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = collect($this->buttons)
            ->filter(function ($button) {
                return is_string($button);
            })->diff(collect($this->defaults)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Unknown Button(s) Found: "%s"',
                $diff->implode('", "')
            )));
        }

        return $this;
    }

    private function checkStructure()
    {
        collect($this->buttons)
            ->each(function ($button) {
                $button = is_object($button)
                    ? $button
                    : (object) $this->defaults[$button];

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
            ->diff(collect($button)->keys())
            ->isNotEmpty();

        if ($formattedWrong) {
            throw new TemplateException(__(sprintf(
                'The following attributes are mandatory for custom buttons: "%s"',
                collect(Attributes::Mandatory)->implode('", "')
            )));
        }

        return $this;
    }

    private function checkOptionalAttributes($button)
    {
        $formattedWrong = collect($button)->keys()
            ->diff(Attributes::Mandatory)
            ->diff(Attributes::Optional)
            ->isNotEmpty();

        if ($formattedWrong) {
            throw new TemplateException(__(sprintf(
                'The following optional attributes are allowed for custom buttons: "%s"',
                collect(Attributes::Optional)->implode('", "')
            )));
        }

        return $this;
    }

    private function checkComplementaryAttributes($button)
    {
        if (property_exists($button, 'action')) {
            if (!property_exists($button, 'fullRoute') && !property_exists($button, 'routeSuffix')) {
                throw new TemplateException(__(sprintf(
                    'Whenever you have an action for a button you need to provide the fullRoute or routeSuffix aswell',
                    collect(Attributes::Optional)->implode('", "')
                )));
            }

            if ($button->action === 'ajax' && !property_exists($button, 'method')) {
                throw new TemplateException(__(sprintf(
                    'Whenever you have an ajax action for a button you need to provide the method aswell',
                    collect(Attributes::Optional)->implode('", "')
                )));
            }
        }

        return $this;
    }

    private function checkActions($button)
    {
        $formattedWrong = property_exists($button, 'action')
            && !collect(Attributes::Actions)->contains($button->action);

        if ($formattedWrong) {
            throw new TemplateException(__(sprintf(
                'The following actions are allowed for custom buttons: "%s"',
                collect(Attributes::Actions)->implode('", "')
            )));
        }

        return $this;
    }

    private function checkRoute($button)
    {
        $route = property_exists($button, 'fullRoute') && !is_null($button->fullRoute)
            ? $button->fullRoute
            : null;

        $route = is_null($route) && property_exists($button, 'routeSuffix') && !is_null($button->routeSuffix)
            ? $this->routePrefix.'.'.$button->routeSuffix
            : $route;

        if (!is_null($route) && !\Route::has($route)) {
            throw new TemplateException(__(sprintf(
                'Button route does not exist: %s',
                $route
            )));
        }

        return $this;
    }

    private function checkMethod($button)
    {
        if (!property_exists($button, 'method')) {
            return;
        }

        if (!collect(Attributes::Methods)->contains($button->method)) {
            throw new TemplateException(__(sprintf(
                'Method is incorrect: %s',
                $button->method
            )));
        }

        return $this;
    }

    private function setDefaults()
    {
        $this->defaults = collect(config('enso.datatables.buttons.global'))
            ->map(function ($button) {
                $button['type'] = 'global';

                return $button;
            })->merge(collect(config('enso.datatables.buttons.row'))
            ->map(function ($button) {
                $button['type'] = 'row';

                return $button;
            }))->toArray();
    }
}
