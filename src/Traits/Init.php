<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\Services\TemplateLoader;

trait Init
{
    use ProvidesRequest;

    public function __invoke(Request $request)
    {
        $table = App::make($this->tableClass, [
            'request' => $this->request($request),
        ]);

        $template = (new TemplateLoader($table))->handle();

        return $template->toArray();
    }
}
