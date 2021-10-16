<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Buttons;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Button as Attributes;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Button as Exception;

class Button
{
    private const Validations = [
        'mandatory', 'optional', 'type', 'action', 'name', 'selection',
    ];

    public function __construct(
        private Obj $button,
        private Table $table,
        private Obj $template
    ) {
    }

    public function validate(): void
    {
        Collection::wrap(self::Validations)
            ->each(fn ($validation) => $this->{$validation}());
    }

    private function mandatory(): void
    {
        Collection::wrap(Attributes::Mandatory)
            ->diff($this->button->keys())
            ->whenNotEmpty(fn () => throw Exception::missingAttributes());
    }

    private function optional(): void
    {
        $this->button->keys()
            ->diff(Attributes::Mandatory)
            ->diff(Attributes::Optional)
            ->whenNotEmpty(fn () => throw Exception::unknownAttributes());
    }

    private function type(): void
    {
        $invalid = ! in_array($this->button->get('type'), Attributes::Types);

        if ($invalid) {
            throw Exception::invalidType();
        }
    }

    private function action(): void
    {
        if (! $this->button->has('action')) {
            return;
        }

        $invalid = ! in_array($this->button->get('action'), Attributes::Actions);

        if ($invalid) {
            throw Exception::invalidAction();
        }

        $this->route()
            ->method();
    }

    private function route(): self
    {
        $route = $this->button->get('fullRoute');

        $route ??= $this->button->has('routeSuffix')
            ? "{$this->template->get('routePrefix')}.{$this->button->get('routeSuffix')}"
            : null;

        if ($route === null) {
            throw Exception::missingRoute();
        }

        if (! Route::has($route)) {
            throw Exception::routeNotFound($route);
        }

        return $this;
    }

    private function method(): void
    {
        if ($this->button->has('method')) {
            $invalid = ! in_array($this->button->get('method'), Attributes::Methods);

            if ($invalid) {
                throw Exception::invalidMethod($this->button->get('method'));
            }
        } else {
            if ($this->button->get('action') === 'ajax') {
                throw Exception::missingMethod();
            }
        }
    }

    private function name(): void
    {
        $missing = $this->table instanceof ConditionalActions
            && $this->button->get('type') === 'row'
            && ! $this->button->has('name');

        if ($missing) {
            throw Exception::missingName();
        }
    }

    private function selection(): void
    {
        if (! $this->button->get('selection')) {
            return;
        }

        if ($this->button->get('type') === 'row') {
            throw Exception::rowSelection();
        }

        if (! $this->template->get('selectable')) {
            throw Exception::noSelectable();
        }
    }
}
