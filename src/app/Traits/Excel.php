<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\VueDatatable\app\Jobs\ExcelExport;
use LaravelEnso\VueDatatable\app\Notifications\ExportStartNotification;

trait Excel
{
    public function excel(Request $request)
    {
        $this->checkExportLimit($request);

        $request->user()->notify(
            new ExportStartNotification($request->get('name'))
        );

        $this->dispatch(new ExcelExport(
            $request->user(),
            $request->all(),
            $this->tableClass
        ));
    }

    private function checkExportLimit(Request $request)
    {
        $length = is_string($request->get('meta'))
            ? json_decode($request->get('meta'))->length
            : $request->get('meta')['length'];

        if ($length > config('enso.datatable.export.limit')) {
            throw new ExportException(__(
                'The table exceeds the maximum number of records allowed: :actual vs :limit',
                ['actual' => $length, 'limit' => config('enso.datatable.export.limit')]
            ));
        }
    }
}
