<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\VueDatatable\app\Classes\Table;
use LaravelEnso\VueDatatable\app\Classes\Template;

trait Datatable
{
    public function init()
    {
        $template = new Template(self::Template);

        return $template->get();
    }

    public function data(Request $request)
    {
        $table = new Table($request, $this->query());

        return $table->data();
    }
}
