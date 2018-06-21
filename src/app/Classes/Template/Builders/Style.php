<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

use LaravelEnso\VueDatatable\app\Classes\Attributes\Style as Attributes;

class Style
{
    private $template;
    private $style;

    public function __construct($template)
    {
        $this->template = $template;
        $this->style = config('enso.datatable.style');
    }

    public function build()
    {
        $this->template->align = $this->compute(Attributes::Align);
        $this->template->style = $this->compute(Attributes::Table);
        $this->template->aligns = $this->preset(Attributes::Align);
        $this->template->styles = $this->preset(Attributes::Table);
    }

    private function compute($style)
    {
        return collect($this->style['default'])->intersect($style)
            ->values()
            ->reduce(function ($style, $param) {
                $style->push($this->style['mapping'][$param]);

                return $style;
            }, collect())->unique()->implode(' ');
    }

    private function preset($style)
    {
        return collect($style)->reduce(function ($styles, $style) {
            $styles[$style] = $this->style['mapping'][$style];

            return $styles;
        }, []);
    }
}
