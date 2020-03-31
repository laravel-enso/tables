<?php

namespace LaravelEnso\Tables\App\Services\Template\Builders;

use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Controls as Attributes;

class Controls
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function build(): void
    {
        if (! $this->template->has('controls')) {
            $this->template->set(
                'controls',
                Config::get('enso.tables.controls') ?? Attributes::List
            );
        }
    }
}
