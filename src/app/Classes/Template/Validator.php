<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Route;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Buttons;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Columns;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Controls;
use LaravelEnso\VueDatatable\app\Classes\Template\Validators\Structure;

class Validator
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function run()
    {
        (new Structure($this->template))
            ->validate();

        (new Route($this->template))
            ->validate();

        $this->validateButtons();

        (new Controls($this->template))
            ->validate();

        (new Columns($this->template))
            ->validate();
    }

    private function validateButtons()
    {
        if (! $this->template->has('buttons')) {
            $this->template->buttons = [];

            return;
        }

        (new Buttons($this->template))
            ->validate();
    }
}
