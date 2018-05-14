<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template;

use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Routes;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Buttons;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Columns;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Structure;

class Validator
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function run()
    {
        (new Structure($this->template))
            ->validate();

        (new Routes($this->template))
            ->validate();

        $this->validateButtons();

        (new Columns($this->template))
            ->validate();
    }

    private function validateButtons()
    {
        if (!property_exists($this->template, 'buttons')) {
            $this->template->buttons = [];

            return;
        }

        (new Buttons($this->template))
            ->validate();
    }
}
