<?php

namespace LaravelEnso\Tables\Traits\Tests;

use Exception;
use Illuminate\Support\Facades\Config;

trait Datatable
{
    /** @test */
    public function can_view_index()
    {
        if (! isset($this->permissionGroup)) {
            throw new Exception('"permissionGroup" property is missing from your test');
        }

        $absolute = Config::get('enso.tables.absoluteRoutes');

        $init = $this->get(route($this->permissionGroup.'.initTable', [], $absolute));

        $init->assertStatus(200)
            ->assertJsonStructure(['template', 'meta', 'apiVersion']);

        $params = [
            'columns' => [],
            'meta' => '{"start":0,"length":10,"sort":false,"search": "","forceInfo":false,"searchMode":"full"}',
        ];

        $this->get(route($this->permissionGroup.'.tableData', $params, $absolute))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
