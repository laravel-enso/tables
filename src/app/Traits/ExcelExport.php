<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;

trait ExcelExport
{
    public function excel(Request $request)
    {
        return (new Excel(
            $request->user(), $request->all()
        ))->handle();
    }
}
