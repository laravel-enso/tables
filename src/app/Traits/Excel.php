<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;

trait Excel
{
    public function excel(Request $request)
    {
        return [
            'message' => __(config('labels.emailReportRequest')),
        ];
    }
}
