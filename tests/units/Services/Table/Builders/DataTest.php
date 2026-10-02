<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\Builders\Data;
use LaravelEnso\Tables\Services\Data\Sorts\Sort;
use LaravelEnso\Tables\Tests\units\Services\BuilderTestResource;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use LaravelEnso\Tables\Tests\units\Services\TestModel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataTest extends TestCase
{
    use SetUp;

    #[Test]
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertCount(TestModel::count(), $response);

        $isSame = $response->first()->diff($this->testModel->toArray())->isEmpty();

        $this->assertTrue($isSame);
    }

    #[Test]
    public function can_get_data_with_appends()
    {
        $this->config->get('appends')->push('custom');

        $response = $this->requestResponse();

        $this->assertEquals(
            'name',
            $response->first()
                ->get('custom')
                ->get('relation')
        );
    }

    #[Test]
    public function can_get_data_with_method()
    {
        $this->config->meta()->set('method', true);

        $this->config->columns()->push(new Obj([
            'name' => 'customMethod',
            'data' => 'customMethod',
            'meta' => ['method' => true],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals(
            'custom',
            $response->first()->get('customMethod')
        );
    }

    #[Test]
    public function can_get_data_with_flatten()
    {
        $this->config->get('appends')->push('custom');

        $this->config->put('flatten', true);

        $response = $this->requestResponse();

        $this->assertEquals(
            'name',
            $response->first()->get('custom.relation')
        );
    }

    #[Test]
    public function can_get_data_with_resource()
    {
        $this->config->meta()->set('resource', true);

        $this->config->columns()->push(new Obj([
            'name'     => 'price',
            'data'     => 'price',
            'resource' => BuilderTestResource::class,
            'meta'     => [],
        ]));

        $response = $this->requestResponse();

        $resource = (new BuilderTestResource($this->testModel->price))->resolve();

        $this->assertEquals(
            json_encode($resource),
            $response->first()->get('price')
        );
    }

    #[Test]
    public function can_get_data_with_date()
    {
        $this->config->meta()->set('date', true);

        $this->config->columns()->push(new Obj([
            'name'       => 'created_at',
            'dateFormat' => 'Y-m-d',
            'meta'       => ['date' => true],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->testModel->created_at->format('Y-m-d'),
            $response->first()->get('created_at')
        );
    }

    #[Test]
    public function can_get_data_with_datetime()
    {
        $format = Config::get('enso.tables.dateTimeFormat');
        $this->config->meta()->set('datetime', true);

        $this->config->columns()->push(new Obj([
            'name'       => 'created_at',
            'dateFormat' => $format,
            'meta'       => ['datetime' => true],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->testModel->created_at->format($format),
            $response->first()->get('created_at')
        );
    }

    #[Test]
    public function can_get_data_with_cents()
    {
        $this->config->meta()->set('cents', true);

        $this->config->columns()->push(new Obj([
            'name' => 'price',
            'data' => 'price',
            'meta' => ['cents' => true],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->testModel->price / 100,
            $response->first()->get('price')
        );
    }

    #[Test]
    public function can_get_data_with_sort()
    {
        $this->createTestModel();

        $column = new Obj([
            'name' => 'id',
            'data' => 'id',
            'meta' => ['sortable' => true, 'sort' => 'DESC'],
        ]);

        $this->config->columns()->push($column);

        $this->config->meta()->put('sort', true);

        $response = $this->requestResponse();

        $this->assertEquals(
            TestModel::orderByDesc('id')->first()->id,
            $response->first()->get('id')
        );
    }

    #[Test]
    public function can_get_data_with_default_sort()
    {
        $this->createTestModel('Z');
        $this->createTestModel('A');

        $this->config->template()->set('defaultSort', 'name');

        $response = $this->requestResponse();

        $this->assertEquals(
            TestModel::orderBy('name')->first()->name,
            $response->first()->get('name')
        );

        $this->config->template()->set('defaultSortDirection', 'desc');

        $response = $this->requestResponse();

        $this->assertEquals(
            TestModel::orderBy('name')->latest()->first()->name,
            $response->last()->get('name')
        );
    }

    #[Test]
    public function can_get_data_with_sort_null_last()
    {
        $secondModel = $this->createTestModel();

        $this->testModel->update(['name' => null]);

        $column = new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['sortable' => true, 'sort' => 'ASC', 'nullLast' => true],
        ]);

        $this->config->columns()->push($column);

        $this->config->meta()->put('sort', true);

        $response = $this->requestResponse();

        $this->assertEquals(
            $secondModel->name,
            $response->first()->get('name')
        );
    }

    #[Test]
    public function can_get_data_with_limit()
    {
        $this->config->meta()->set('length', 0);

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    #[Test]
    public function can_use_full_info_record_limit()
    {
        $limit = 1;

        $this->createTestModel();

        $this->config->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['searchable' => true],
        ]));

        $this->config->set('comparisonOperator', 'LIKE');

        $this->config->meta()->set('search', $this->testModel->name)
            ->set('fullInfoRecordLimit', $limit);

        $response = $this->requestResponse();

        $this->assertCount($limit, $response);
    }

    #[Test]
    public function can_disable_implicit_sorting(): void
    {
        $this->config->template()->set('disableImplicitSorting', true);

        $queries = $this->query->getConnection()->pretend(
            fn () => $this->requestResponse()
        );

        $this->assertStringNotContainsString('order by', $queries[0]['query']);
    }

    #[Test]
    public function fetch_mode_preserves_implicit_sorting(): void
    {
        $this->config->template()->set('disableImplicitSorting', true);

        $queries = $this->query->getConnection()->pretend(
            fn () => (new Data($this->table, $this->config, true))->handle()
        );

        $this->assertStringContainsString('order by', $queries[0]['query']);
    }

    #[Test]
    public function disabling_implicit_sorting_preserves_query_ordering(): void
    {
        $this->query->orderBy('name', 'desc');
        $orders = $this->query->getQuery()->orders;

        (new Sort($this->config, $this->query))->handle(false);

        $this->assertSame($orders, $this->query->getQuery()->orders);
    }

    #[Test]
    public function disabling_implicit_sorting_preserves_explicit_sort_and_tie_breaker(): void
    {
        $this->config->meta()->set('sort', true);
        $this->config->columns()->push(new Obj([
            'name' => 'price',
            'data' => 'price',
            'meta' => ['sortable' => true, 'sort' => 'desc'],
        ]));

        (new Sort($this->config, $this->query))->handle(false);

        $template = $this->config->template();

        $this->assertSame([
            ['column' => 'price', 'direction' => 'desc'],
            [
                'column' => "{$template->get('table')}.{$template->get('dtRowId')}",
                'direction' => $template->get('defaultSortDirection'),
            ],
        ], $this->query->getQuery()->orders);
    }

    private function requestResponse()
    {
        $data = new Data($this->table, $this->config);

        return new Obj($data->handle());
    }
}
