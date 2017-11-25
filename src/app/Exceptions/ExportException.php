<?php

namespace LaravelEnso\VueDatatable\app\Exceptions;

use Exception;

class ExportException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct(__($message), 555);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
