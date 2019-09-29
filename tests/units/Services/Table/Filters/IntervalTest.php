<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Filters\Interval;

class IntervalTest extends TestCase
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
        $this->createRelationalModel();

        $this->query = TestModel::select('*');
    }

    /** @test */
    public function can_use_interval()
    {
        $this->params['intervals']['test_models']['id'] = [
            'min' => $this->testModel->id - 1,
            'max' => $this->testModel->id + 1,
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $this->testModel->name,
            $response->first()->name
        );

        $this->params['intervals']['test_models']['id'] = [
            'min' => $this->testModel->id - 1,
            'max' => $this->testModel->id - 2,
        ];

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_date_interval()
    {
        $this->params['intervals']['test_models']['created_at'] = [
            'dbDateFormat' => 'Y-m-d',
            'dateFormat' => 'Y-m-d',
            'min' => $this->testModel->created_at->subDays(1)->format('Y-m-d'),
            'max' => $this->testModel->created_at->addDays(1)->format('Y-m-d'),
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $this->testModel->name,
            $response->first()->name
        );

        $this->params['intervals']['test_models']['created_at'] = [
            'dbDateFormat' => 'Y-m-d',
            'dateFormat' => 'Y-m-d',
            'min' => $this->testModel->created_at->subDays(2)->format('Y-m-d'),
            'max' => $this->testModel->created_at->subDays(1)->format('Y-m-d'),
        ];

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    private function requestResponse()
    {
        (new Interval(
            new Request($this->params),
            $this->query
        ))->handle();

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
