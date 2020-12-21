<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Filters;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Exceptions\Filter as Exception;

class Filters
{
    private ?Obj $filters;

    public function __construct(Obj $template)
    {
        $this->filters = $template->get('filters');
    }

    public function validate(): void
    {
        if ($this->filters) {
            $this->format()
                ->structure();
        }
    }

    private function format(): self
    {
        $invalid = $this->filters
            ->filter(fn ($filter) => ! is_string($filter) && ! $filter instanceof Obj);

        if ($invalid->isNotEmpty()) {
            throw Exception::invalidFormat();
        }

        return $this;
    }

    private function structure(): self
    {
        $this->filters->each(fn ($filter) => (new Filter($filter))->validate());

        return $this;
    }
}
