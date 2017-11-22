<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template;

use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Buttons;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Columns;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Routes;
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
        (new Structure($this->template))->validate();
        (new Routes($this->template))->validate();
        (new Buttons($this->template))->validate();
        (new Columns($this->template))->validate();
    }
}
