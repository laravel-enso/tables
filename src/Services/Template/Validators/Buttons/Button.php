<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Buttons;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Button as Attributes;
use LaravelEnso\Tables\Exceptions\Button as Exception;

class Button
{
    private Obj $button;
    private ?string $routePrefix;

    public function __construct(Obj $button, ?string $routePrefix)
    {
        $this->button = $button;
        $this->routePrefix = $routePrefix;
    }

    public function validate(): void
    {
        $this->mandatoryAttributes()
            ->optionalAttributes()
            ->complementaryAttributes()
            ->actions()
            ->route()
            ->method();
    }

    private function mandatoryAttributes(): self
    {
        $formattedWrong = (new Collection(Attributes::Mandatory))
            ->diff($this->button->keys())
            ->isNotEmpty();

        if ($formattedWrong) {
            throw Exception::missingAttributes();
        }

        return $this;
    }

    private function optionalAttributes(): self
    {
        $formattedWrong = $this->button->keys()
            ->diff(Attributes::Mandatory)
            ->diff(Attributes::Optional)
            ->isNotEmpty();

        if ($formattedWrong) {
            throw Exception::unknownAttributes();
        }

        return $this;
    }

    private function complementaryAttributes(): self
    {
        if (! $this->button->has('action')) {
            return $this;
        }

        if (! $this->button->has('fullRoute') && ! $this->button->has('routeSuffix')) {
            throw Exception::missingRoute();
        }

        if ($this->button->get('action') === 'ajax' && ! $this->button->has('method')) {
            throw Exception::missingMethod();
        }

        return $this;
    }

    private function actions(): self
    {
        $formattedWrong = $this->button->has('action')
            && ! (new Collection(Attributes::Actions))
                ->contains($this->button->get('action'));

        if ($formattedWrong) {
            throw Exception::wrongAction();
        }

        return $this;
    }

    private function route(): self
    {
        $route = $this->button->get('fullRoute');

        $route ??= $this->button->has('routeSuffix')
            ? "{$this->routePrefix}.{$this->button->get('routeSuffix')}"
            : null;

        if ($route !== null && ! Route::has($route)) {
            throw Exception::routeNotFound($route);
        }

        return $this;
    }

    private function method(): self
    {
        if ($this->button->has('method')
            && ! (new Collection(Attributes::Methods))
                ->contains($this->button->get('method'))) {
            throw Exception::invalidMethod($this->button->get('method'));
        }

        return $this;
    }
}
