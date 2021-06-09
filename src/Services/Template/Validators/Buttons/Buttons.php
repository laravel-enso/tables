<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Buttons;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Button as Exception;

class Buttons
{
    private const Validations = ['format', 'defaults', 'structure'];

    private Obj $defaults;

    public function __construct(
        private Obj $template,
        private Table $table
    ) {
        $this->defaults = $this->configButtons();
    }

    public function validate(): void
    {
        Collection::wrap(self::Validations)
            ->each(fn ($validation) => $this->{$validation}());
    }

    private function format(): void
    {
        $invalid = $this->template->get('buttons')
            ->filter(fn ($button) => ! is_string($button) && ! $button instanceof Obj);

        if ($invalid->isNotEmpty()) {
            throw Exception::invalidFormat();
        }
    }

    private function defaults(): void
    {
        $diff = $this->template->get('buttons')
            ->filter(fn ($button) => is_string($button))
            ->diff($this->defaults->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::undefined($diff->implode('", "'));
        }
    }

    private function structure(): void
    {
        $this->template->get('buttons')
            ->map(fn ($button) => $this->map($button))
            ->each(fn ($button) => (new Button($button, $this->table, $this->template))
                ->validate());
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
            ->map(fn ($button, $key) => $button->set('type', 'global')
                ->set('name', $key));

        $row = (new Obj(Config::get('enso.tables.buttons.row')))
            ->map(fn ($button, $key) => $button->set('type', 'row')
                ->set('name', $key));

        return $global->merge($row);
    }
}
