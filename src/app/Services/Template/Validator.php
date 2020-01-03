<?php

namespace LaravelEnso\Tables\App\Services\Template;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Services\Template\Validators\Buttons;
use LaravelEnso\Tables\App\Services\Template\Validators\Columns;
use LaravelEnso\Tables\App\Services\Template\Validators\Controls;
use LaravelEnso\Tables\App\Services\Template\Validators\Route;
use LaravelEnso\Tables\App\Services\Template\Validators\Structure;

class Validator
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function run()
    {
        (new Structure($this->template))->validate();

        (new Route($this->template))->validate();

        (new Buttons($this->template))->validate();

        (new Controls($this->template))->validate();

        (new Columns($this->template))->validate();
    }
}
