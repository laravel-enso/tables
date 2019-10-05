<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use App;
use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

class ExportTest extends TestCase
{
    private $testModel;
    private $faker;
    private $builder;
    private $params;
    private $select;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        $this->params = ['columns' => [], 'meta' => ['length' => 10]];
        $this->select = 'id, name, is_active, created_at, price';

        TestModel::createTable();

        $this->testModel = $this->createTestModel();
    }

    /** @test */
    public function can_get_export_data_with_translatable()
    {
        App::make('translator')->addJsonPath(__DIR__.'/lang');

        App::setLocale('lang');

        $this->testModel->update(['name' => 'should translate']);

        $this->params['columns']['name'] = [
            'name' => 'name',
            'meta' => ['translatable' => true],
        ];

        $this->params['meta']['translatable'] = true;

        $response = $this->requestResponse();

        $this->assertEquals('translation', $response[0]['name']);
    }

    private function requestResponse()
    {
        $this->builder = new Export(
            new TestTable($this->select),
            new Request($this->params, true)
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
