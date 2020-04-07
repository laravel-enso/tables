<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Filters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Filter as Attributes;
use LaravelEnso\Tables\App\Exceptions\Filter as Exception;

class Filter
{
    private Obj $filter;

    public function __construct(Obj $filter)
    {
        $this->filter = $filter;
    }

    public function validate(): void
    {
        $this->mandatoryAttributes()
            ->optionalAttributes()
            ->complementaryAttributes()
            ->route();
    }

    private function mandatoryAttributes(): self
    {
        $formattedWrong = (new Collection(Attributes::Mandatory))
            ->diff($this->filter->keys())
            ->isNotEmpty();

        if ($formattedWrong) {
            throw Exception::missingAttributes();
        }

        return $this;
    }

    private function optionalAttributes(): self
    {
        $formattedWrong = $this->filter->keys()
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
        if ($this->filter->get('type') === 'select' && ! $this->filter->has('route')) {
            throw Exception::missingRoute();
        }

        return $this;
    }

    private function route(): void
    {
        $route = $this->filter->get('route');

        if ($route !== null && ! Route::has($route)) {
            throw Exception::routeNotFound($route);
        }
    }
}
