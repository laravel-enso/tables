<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Tables\app\Contracts\Table;

class TestModel extends Model
{
    protected $fillable = ['name', 'appellative', 'created_at'];

    public function relation()
    {
        return $this->hasOne(RelationalModel::class, 'parent_id');
    }

    public static function createTable()
    {
        Schema::create('test_models', function ($table) {
            $table->increments('id');
            $table->string('appellative')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
}

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

class DummyTable implements Table{
    public function query()
    {
    }

    public function templatePath()
    {
    }
}

