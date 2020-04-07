<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators\Filters;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Filter as Exception;

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
        $formattedWrong = $this->filters
            ->filter(fn ($filter) => ! is_string($filter) && ! $filter instanceof Obj);

        if ($formattedWrong->isNotEmpty()) {
            throw Exception::wrongFormat();
        }

        return $this;
    }

    private function structure(): self
    {
        $this->filters->each(fn ($filter) => (new Filter($filter))->validate());

        return $this;
    }
}
