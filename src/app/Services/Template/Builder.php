<?php

namespace LaravelEnso\Tables\app\Services\Template;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template\Builders\Buttons;
use LaravelEnso\Tables\app\Services\Template\Builders\Columns;
use LaravelEnso\Tables\app\Services\Template\Builders\Controls;
use LaravelEnso\Tables\app\Services\Template\Builders\Structure;
use LaravelEnso\Tables\app\Services\Template\Builders\Style;

class Builder
{
    private $template;
    private $meta;

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

        $this->template->forget(['dataRouteSuffix', 'routePrefix']);
    }
}
