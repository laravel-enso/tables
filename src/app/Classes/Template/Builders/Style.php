<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\VueDatatable\app\Classes\Attributes\Style as Attributes;

class Style
{
    private $template;
    private $styling;
    private $defaultStyle;

    public function __construct($template)
    {
        $this->template = $template;
        $this->styling = new Obj();
        $this->defaultStyle = config('enso.datatable.style');
    }

    public function build()
    {
        $this->styling->set('align', $this->compute(Attributes::Align));
        $this->styling->set('style', $this->compute(Attributes::Table));
        $this->styling->set('aligns', $this->preset(Attributes::Align));
        $this->styling->set('styles', $this->preset(Attributes::Table));
        $this->styling->set('highlight', $this->defaultStyle['highlight'] ?? null); //fixme

        $this->template->set('styling', $this->styling);
    }

    private function compute($style)
    {
        return collect($this->defaultStyle['default'])->intersect($style)
            ->values()
            ->reduce(function ($style, $param) {
                $style->push($this->defaultStyle['mapping'][$param]);

                return $style;
            }, collect())->unique()->implode(' ');
    }

    private function preset($style)
    {
        return collect($style)->reduce(function ($styles, $style) {
            $styles[$style] = $this->defaultStyle['mapping'][$style];

            return $styles;
        }, []);
    }
}
