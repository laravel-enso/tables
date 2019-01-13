<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use LaravelEnso\DataExport\app\Enums\Statuses;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\VueDatatable\app\Jobs\ExcelExport;
use LaravelEnso\VueDatatable\app\Notifications\ExportStartNotification;

trait Excel
{
    public function excel(Request $request)
    {
        $type = Str::title(Str::snake($request->get('name')));

        $request->user()->notify(
            (new ExportStartNotification($type.'_'.__('Table_Report')))
                ->onQueue(config('enso.datatable.queues.notifications'))
        );

        $dataExport = $this->dataExport($type);

        ExcelExport::dispatch(
            $request->user(),
            $request->all(),
            $this->tableClass,
            $dataExport
        );

        return ['dataExport' => $dataExport];
    }

    private function dataExport($type)
    {
        return $this->ensoEnvironment()
            ? DataExport::create([
                'name' => str_replace('_', ' ', $type),
                'entries' => 0,
                'status' => Statuses::Waiting,
            ]) : null;
    }

    private function ensoEnvironment()
    {
        return ! empty(config('enso.config'));
    }
}
