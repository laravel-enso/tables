<?php

namespace App\Tabels\Builders;

use App\Model;
use LaravelEnso\Tables\app\Services\Table;

class ModelsTable extends Table
{
    protected $templatePath = __DIR__.'/../Templates/template.json';

    public function query()
    {
        return Model::selectRaw('
            id as "dtRowId", ......
        ');
    }
}
