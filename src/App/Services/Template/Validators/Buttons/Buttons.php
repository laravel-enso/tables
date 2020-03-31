<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Buttons;

use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Button as Exception;

class Buttons
{
    private Obj $buttons;
    private string $routePrefix;
    private Obj $defaults;

    public function __construct(Obj $template)
    {
        $this->buttons = $template->get('buttons');
        $this->routePrefix = $template->get('routePrefix');

        $this->defaults = $this->configButtons();
    }

    public function validate(): void
    {
        $this->format()
            ->defaults()
            ->structure();
    }

    private function format(): self
    {
        $formattedWrong = $this->buttons
            ->filter(fn ($button) => ! is_string($button)
                && ! $button instanceof Obj);

        if ($formattedWrong->isNotEmpty()) {
            throw Exception::wrongFormat();
        }

        return $this;
    }

    private function defaults(): self
    {
        $diff = $this->buttons->filter(fn ($button) => is_string($button))
            ->diff($this->defaults->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::undefined($diff->implode('", "'));
        }

        return $this;
    }

    private function structure(): self
    {
        $this->buttons->map(
            fn ($button) => $button instanceof Obj
                ? $button
                : $this->defaults->get($button)
        )->each(fn ($button) => (new Button($button, $this->routePrefix))->validate());

        return $this;
    }

    private function configButtons(): Obj
    {
        $global = (new Obj(Config::get('enso.tables.buttons.global')))
            ->map(fn ($button) => $button->set('type', 'global'));

        $row = (new Obj(Config::get('enso.tables.buttons.row')))
            ->map(fn ($button) => $button->set('type', 'row'));

        return $global->merge($row);
    }
}
