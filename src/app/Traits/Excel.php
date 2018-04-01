<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Http\Request;
use LaravelEnso\VueDatatable\app\Classes\Table;
use LaravelEnso\VueDatatable\app\Jobs\ExcelExport;
use LaravelEnso\VueDatatable\app\Exceptions\ExportException;
use LaravelEnso\VueDatatable\app\Notifications\ExportStartNotification;

trait Excel
{
    public function excel(Request $request)
    {
        $this->checkExportLimit($request);

        $request->user()
            ->notify(new ExportStartNotification($request->get('name')));

        $table = (new Table($request, $this->query()))
            ->excel();

        $this->dispatch(new ExcelExport($request->user(), $table));
    }

    private function checkExportLimit(Request $request)
    {
        $length = json_decode($request->get('meta'))
            ->length;

        if ($length > config('enso.datatable.export.limit')) {
            throw new ExportException(__(
                'The table exceeds the maximum number of records allowed: :actual vs :limit',
                ['actual' => $length, 'limit' => config('enso.datatable.export.limit')]
            ));
        }
    }
}
