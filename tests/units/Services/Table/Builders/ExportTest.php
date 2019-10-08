<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use App;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

class ExportTest extends TestCase
{
    private $testModel;
    private $faker;
    private $builder;
    private $request;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        Route::any('route')->name('testTables.tableData');
        Route::getRoutes()->refreshNameLookups();

        TestModel::createTable();

        $this->testModel = $this->createTestModel();

        $this->request = new Request(['columns' => [], 'meta' => ['length' => 10]]);

        $this->table = (new TestTable())->select(
            'id, name, is_active, created_at, price'
        );

        $this->template = (new Template($this->table))->load([
            'template' => new Obj([
                'routePrefix' => 'testTables',
                'buttons' => [],
                'columns' => []
            ]),
            'meta' => new Obj(),
        ]);
    }

    /** @test */
    public function can_get_export_data_with_translatable()
    {
        App::make('translator')->addJsonPath(__DIR__.'/lang');

        App::setLocale('lang');

        $this->testModel->update(['name' => 'should translate']);

        $this->template->meta()->set('translatable', true);

        $this->template->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['translatable'],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals('translation', $response[0]['name']);
    }

    private function requestResponse()
    {
        $this->builder = new Export(
            $this->table, $this->request, $this->template
        );

        return $this->builder->fetch();
    }

    private function createTestModel()
    {
        return TestModel::create([
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->numberBetween(1000, 10000),
        ]);
    }
}
