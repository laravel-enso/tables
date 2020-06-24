<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Structure;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Structure as Attributes;
use LaravelEnso\Tables\Exceptions\Template as Exception;

class Structure
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->mandatoryAttributes()
            ->optionalAttributes();
    }

    private function mandatoryAttributes()
    {
        $diff = (new Collection(Attributes::Mandatory))
            ->diff($this->template->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function optionalAttributes()
    {
        $attributes = (new Collection(Attributes::Mandatory))
            ->merge(Attributes::Optional);

        $diff = $this->template->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes($diff->implode('", "'));
        }

        return $this;
    }
}
