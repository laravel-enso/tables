<?php

namespace LaravelEnso\VueDatatable\app\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\VueDatatable\app\Exports\Excel;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\VueDatatable\app\Notifications\ExportDoneNotification;

class ExcelExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $user;
    private $tableClass;
    private $table;
    private $export;

    public function __construct(User $user, array $request, string $tableClass)
    {
        $this->user = $user;
        $this->request = $request;
        $this->tableClass = $tableClass;
        $this->timeout = config('enso.datatable.export.maxExecutionTime');
    }

    public function handle()
    {
        auth()->onceUsingId($this->user->id);

        $this->table = (new $this->tableClass($this->request))
            ->excel();

        $this->export()
            ->store()
            ->notify()
            ->cleanUp();
    }

    private function export()
    {
        app()->setLocale($this->user->preferences()->global->lang);

        (new Excel(
            $this->filePath(),
            $this->table['header'],
            $this->table['data']
        ))->run();

        return $this;
    }

    private function store()
    {
        if ($this->isNotEnso()) {
            return;
        }

        $file = new UploadedFile(
            storage_path('app/'.$this->filePath()),
            $this->filename(),
            \Storage::mimeType($this->filePath()),
            \Storage::size($this->filePath()),
            0,
            true
        );

        \DB::transaction(function () use ($file) {
            $this->export = DataExport::create();
            $this->export->upload($file);
        });

        return $this;
    }

    private function notify()
    {
        $this->user->notify(
            new ExportDoneNotification(
                $this->filePath(),
                $this->filename(),
                optional($this->export)->temporaryLink()
            )
        );

        return $this;
    }

    private function cleanUp()
    {
        \Storage::delete($this->filePath());
    }

    private function filePath()
    {
        return config('enso.datatable.export.path')
            .DIRECTORY_SEPARATOR.$this->filename().'.xlsx';
    }

    private function filename()
    {
        return preg_replace(
            '/[^A-Za-z0-9_.-]/',
            '_',
            __(ucfirst($this->table['name'])).__('Table').__('Report')
        ).'.xlsx';
    }

    private function isNotEnso()
    {
        return empty(config('enso.config'));
    }
}
