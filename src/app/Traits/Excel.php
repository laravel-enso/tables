<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\VueDatatable\app\Classes\Table;
use LaravelEnso\VueDatatable\app\Jobs\ExcelExport;

trait Excel
{
    public function excel(Request $request)
    {
        $table = new Table($request, $this->query());

        $this->dispatch(new ExcelExport(request()->user(), $table->excel()));

        return [
            'message' => __(config('enso.labels.emailReportRequest')),
        ];
    }
}
