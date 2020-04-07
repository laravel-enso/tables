<?php

namespace LaravelEnso\Tables\App\Services\Template;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Services\Template\Builders\Buttons;
use LaravelEnso\Tables\App\Services\Template\Builders\Columns;
use LaravelEnso\Tables\App\Services\Template\Builders\Controls;
use LaravelEnso\Tables\App\Services\Template\Builders\Filters;
use LaravelEnso\Tables\App\Services\Template\Builders\Structure;
use LaravelEnso\Tables\App\Services\Template\Builders\Style;

class Builder
{
    private Obj $template;
    private Obj $meta;

    public function __construct(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;
    }

    public function handleCacheable()
    {
        (new Structure($this->template, $this->meta))->build();

        (new Columns($this->template, $this->meta))->build();

        (new Style($this->template))->build();

        (new Controls($this->template))->build();
    }

    public function handleNonCacheable()
    {
        (new Buttons($this->template))->build();

        (new Filters($this->template, $this->meta))->build();

        $this->template->forget(['dataRouteSuffix', 'routePrefix']);
    }
}
