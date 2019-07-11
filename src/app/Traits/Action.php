<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait Action
{
    public function __invoke(Request $request)
    {
        App::makeWith(
            $this->actionClass, [
                'class'=>$this->tableClass,
                'request' => $request->all()
            ])->handle();
    }
}
