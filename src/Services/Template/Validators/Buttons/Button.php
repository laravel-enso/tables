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
        'mandatory', 'optional', 'complementary',
        'actions', 'method', 'name', 'route',
    ];

    private Obj $button;
    private Table $table;
    private ?string $routePrefix;

    public function __construct(Obj $button, Table $table, ?string $routePrefix)
    {
        $this->button = $button;
        $this->table = $table;
        $this->routePrefix = $routePrefix;
    }

    public function validate(): void
    {
        Collection::wrap(self::Validations)
            ->each(fn ($validation) => $this->{$validation}());
    }

    private function mandatory(): void
    {
        $missing = (new Collection(Attributes::Mandatory))
            ->diff($this->button->keys())
            ->isNotEmpty();

        if ($missing) {
            throw Exception::missingAttributes();
        }
    }

    private function optional(): void
    {
        $unknown = $this->button->keys()
            ->diff(Attributes::Mandatory)
            ->diff(Attributes::Optional)
            ->isNotEmpty();

        if ($unknown) {
            throw Exception::unknownAttributes();
        }
    }

    private function complementary(): void
    {
        if (! $this->button->has('action')) {
            return;
        }

        $missingRoute = ! $this->button->has('fullRoute')
            && ! $this->button->has('routeSuffix');

        if ($missingRoute) {
            throw Exception::missingRoute();
        }

        $missingMethod = $this->button->get('action') === 'ajax'
            && ! $this->button->has('method');

        if ($missingMethod) {
            throw Exception::missingMethod();
        }
    }

    private function actions(): void
    {
        $invalid = $this->button->has('action')
            && ! (new Collection(Attributes::Actions))
                ->contains($this->button->get('action'));

        if ($invalid) {
            throw Exception::invalidAction();
        }
    }

    private function route(): void
    {
        $route = $this->button->get('fullRoute');

        $route ??= $this->button->has('routeSuffix')
            ? "{$this->routePrefix}.{$this->button->get('routeSuffix')}"
            : null;

        if ($route !== null && ! Route::has($route)) {
            throw Exception::routeNotFound($route);
        }
    }

    private function method(): void
    {
        $invalid = $this->button->has('method')
            && ! in_array($this->button->get('method'), Attributes::Methods);

        if ($invalid) {
            throw Exception::invalidMethod($this->button->get('method'));
        }
    }

    private function name(): void
    {
        $missing = $this->table instanceof ConditionalActions
            && ! $this->button->has('name');

        if ($missing) {
            throw Exception::missingName();
        }
    }
}
