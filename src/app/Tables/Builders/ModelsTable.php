<?php

namespace App\Tabels\Builders;

use App\Model;
use LaravelEnso\VueDatatable\app\Classes\Table;

class ModelsTable extends Table
{
    protected $templatePath = __DIR__.'/../Templates/template.json';

    public function query()
    {
        return Model::select(\DB::raw('
            id as "dtRowId", ......
        '));
    }
}
