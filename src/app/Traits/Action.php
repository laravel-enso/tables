<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;

trait Action
{
    public function action(Request $request)
    {
        (new $this->actionClass())
            ->request($request->all())
            ->class($this->tableClass)
            ->chunk($this->chunk ?? 1000)
            ->run();
    }
}
