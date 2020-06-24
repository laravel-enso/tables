<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Tables\Traits\TableCache;

class TestModel extends Model
{
    use TableCache;

    protected $fillable = ['name', 'price', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function getCustomAttribute()
    {
        return ['relation' => 'name'];
    }

    public static function createTable()
    {
        Schema::create('test_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_active')->nullable();
            $table->integer('price')->nullable();
            $table->integer('color')->nullable();
            $table->timestamps();
        });
    }
}
