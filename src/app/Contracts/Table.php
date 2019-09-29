<?php

namespace LaravelEnso\Tables\app\Contracts;

interface Table
{
    public function query();

    public function templatePath();
}
