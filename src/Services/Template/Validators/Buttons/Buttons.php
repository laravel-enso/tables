<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Buttons;

use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Button as Exception;

class Buttons
{
    private Obj $buttons;
    private string $routePrefix;
    private Obj $defaults;
    private Table $table;

    public function __construct(Obj $template, Table $table)
    {
        $this->buttons = $template->get('buttons');
        $this->routePrefix = $template->get('routePrefix');
        $this->table = $table;
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
            ->filter(fn ($button) => ! is_string($button) && ! $button instanceof Obj);

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
        $this->buttons->map(fn ($button) => $this->map($button))
            ->each(fn ($button) => (new Button($button, $this->table, $this->routePrefix))->validate());

        return $this;
    }

    private function map($button)
    {
        return $button instanceof Obj
            ? $button
            : $this->defaults->get($button);
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
