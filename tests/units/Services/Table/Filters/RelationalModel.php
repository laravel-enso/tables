<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class RelationalModel extends Model
{
    protected $fillable = ['name', 'parent_id'];

    public static function createTable()
    {
        Schema::create('relational_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('test_models');
            $table->timestamps();
        });
    }

}
