<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Builders\Filters\Filter;


class FilterTest extends TestCase
{
    private $testModel;
    private $faker;
    private $query;
    private $params;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        TestModel::createTable();
        RelationalModel::createTable();

        $this->testModel = $this->createTestModel();

        $this->query = TestModel::select('*');

        $this->createRelationalModel();
    }

    /** @test */
    public function can_use_filters()
    {
        $this->params['filters']['test_models'] = ['name' => $this->testModel->name];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );

        $this->params['filters']['test_models'] = ['name' => $this->testModel->name.'-'];

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    private function requestResponse()
    {
        (new Filter(
            new Request($this->params), $this->query
        ))->handle(new DummyTable());

        return $this->query->get();
    }

    private function createTestModel()
    {
        return TestModel::create([
            'appellative' => $this->faker->firstName,
            'name' => $this->faker->name,
        ]);
    }

    private function createRelationalModel()
    {
        return RelationalModel::create([
            'name' => $this->faker->word,
            'parent_id' => $this->testModel->id,
        ]);
    }
}
