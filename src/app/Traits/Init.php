<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\TemplateLoader;
use LaravelEnso\Tables\app\Services\Table\Request as TableRequest;

trait Init
{
    public function __invoke(Request $request)
    {
        $request = new TableRequest($request->all());
        $table = App::make($this->tableClass, ['request' => $request]);

        return TemplateLoader::load($table)->handle();
    }
}
