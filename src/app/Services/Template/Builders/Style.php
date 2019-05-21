<?php

namespace LaravelEnso\Tables\app\Services\Template\Builders;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Attributes\Style as Attributes;

class Style
{
    private $template;
    private $defaultStyle;

    public function __construct($template)
    {
        $this->template = $template;
        $this->defaultStyle = new Obj(config('enso.tables.style'));
    }

    public function build()
    {
        $this->template->set('align', $this->compute(Attributes::Align));
        $this->template->set('style', $this->compute(Attributes::Table));
        $this->template->set('aligns', $this->preset(Attributes::Align));
        $this->template->set('styles', $this->preset(Attributes::Table));
        $this->template->set('highlight', $this->defaultStyle->get('highlight'));
    }

    private function compute($style)
    {
        return $this->defaultStyle->get('default')
            ->intersect($style)
            ->values()
            ->reduce(function ($style, $param) {
                return $style->push(
                    $this->defaultStyle->get('mapping')->get($param)
                );
            }, collect())->unique()->implode(' ');
    }

    private function preset($style)
    {
        return collect($style)->reduce(function ($styles, $style) {
            return $styles->set(
                $style, $this->defaultStyle->get('mapping')->get($style)
            );
        }, new Obj);
    }
}
