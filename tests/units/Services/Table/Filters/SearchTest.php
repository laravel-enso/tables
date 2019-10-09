<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Filters;

use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Filters\Search;

class SearchTest extends TestCase
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

        $this->params = [
            'meta' => [],
            'columns' => [
                'name' => [
                    'data' => 'name',
                    'meta' => ['searchable' => true]
                ]
            ]
        ];

        $this->createRelationModel();
    }

    /** @test */
    public function can_get_data_without_condition()
    {
        $this->params['columns']['name'] = ['data' => 'name'];

        $response = $this->requestResponse();

        $this->assertCount(TestModel::count(), $response);

        $this->assertTrue(
            $response->pluck('name')
                ->contains($this->testModel->name)
        );
    }

    /** @test */
    public function can_use_search()
    {
        $this->params['meta'] = [
            'search' => $this->testModel->name,
            'comparisonOperator' => 'LIKE',
            'searchMode' => 'full',
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );

        $this->params['meta']['search'] = $this->testModel->name.'-';

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_full_search()
    {
        $this->params['meta'] = [
            'search' => substr($this->testModel->name, 1, strlen($this->testModel->name) - 2),
            'comparisonOperator' => 'LIKE',
            'searchMode' => 'full',
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );

        $this->params['meta']['search'] = $this->testModel->name.'-';

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_starts_with_search()
    {
        $this->params['meta'] = [
            'search' => collect(explode(' ', $this->testModel->name))->first(),
            'comparisonOperator' => 'LIKE',
            'searchMode' => 'startsWith',
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );
    }

    /** @test */
    public function can_use_ends_with_search()
    {
        $this->params['meta'] = [
            'search' => collect(explode(' ', $this->testModel->name))->last(),
            'comparisonOperator' => 'LIKE',
            'searchMode' => 'endsWith',
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );

        $this->params['meta']['search'] = $this->testModel->name.'-';

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_multi_argument_search()
    {
        $this->params['columns']['appellative'] = [
            'data' => 'appellative',
            'searchable' => true,
            'meta' => ['searchable' => true]
        ];

        $this->params['meta'] = [
            'search' => $this->testModel->name.' '.$this->testModel->appellative,
            'comparisonOperator' => 'LIKE',
            'searchMode' => 'full',
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );
    }

    /** @test */
    public function can_use_relation_search()
    {
        $this->params['columns']['name'] = [
            'name' => 'relation.name',
            'data' => 'relation.name',
            'meta' => ['searchable' => true],
        ];

        $this->params['meta'] = [
            'search' => $this->testModel->relation->name,
            'comparisonOperator' => 'LIKE',
            'searchMode' => 'full',
        ];

        $response = $this->requestResponse();

        $this->assertCount(1, $response);

        $this->assertEquals(
            $response->first()->name,
            $this->testModel->name
        );
    }

    private function requestResponse()
    {
        (new Search(
            (new Config($this->params))->setTemplate($this->template()),
            $this->query)
        )->handle();

        return $this->query->get();
    }

    private function createTestModel()
    {
        return TestModel::create([
            'appellative' => $this->faker->firstName,
            'name' => $this->faker->name,
        ]);
    }

    private function createRelationModel()
    {
        return RelationalModel::create([
            'name' => $this->faker->word,
            'parent_id' => $this->testModel->id,
        ]);
    }

    private function template()
    {
        return (new Template(new DummyTable()))->load([
            'meta' => new Obj($this->params['meta']),
            'template' => new Obj($this->params),
        ]);
    }
}
