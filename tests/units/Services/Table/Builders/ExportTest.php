<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use App;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\Tests\units\Services\Setup;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

class ExportTest extends TestCase
{
    use Setup;

    /** @test */
    public function can_get_export_data_with_translatable()
    {
        App::make('translator')->addJsonPath(__DIR__.'/lang');

        App::setLocale('lang');

        $this->testModel->update(['name' => 'should translate']);

        $this->config->meta()->set('translatable', true);

        $this->config->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['translatable' => true],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals('translation', $response[0]['name']);
    }

    private function requestResponse()
    {
        $builder = new Export($this->table, $this->config);

        return $builder->fetch();
    }
}
