<?php

namespace App\Tables\Actions;

use LaravelEnso\Tables\app\Services\Action;

class CustomAction extends Action
{
    public function process(array $row)
    {
        // do something with $row, where $row represents line of the table as represented in the frontend
        // you have acces the $this->request() : Obj
    }
}
