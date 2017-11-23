<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;

trait Pdf
{
    public function pdf(Request $request)
    {
        return [
            'message' => __(config('enso.labels.emailReportRequest')),
        ];
    }
}
