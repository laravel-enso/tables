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
        $tableClass = method_exists($this, 'tableClass')
            ? $this->tableClass($request)
            : $this->tableClass;

        $table = App::make($tableClass, [
            'request' => $this->request($request),
        ]);

        $template = (new TemplateLoader($table))->handle();

        return $template->toArray();
    }
}
