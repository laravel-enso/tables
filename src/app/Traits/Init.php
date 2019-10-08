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
        $table = App::make($this->tableClass, [
            'request' => new TableRequest($request->all())
        ]);

        $template = (new TemplateLoader($table))->handle();

        return $template->data();
    }
}
