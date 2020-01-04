<?php

namespace LaravelEnso\Tables\App\Traits\Tests;

use Exception;

trait Datatable
{
    /** @test */
    public function can_view_index()
    {
        if (! isset($this->permissionGroup)) {
            throw new Exception('"permissionGroup" property is missing from your test');
        }

        $init = $this->get(route($this->permissionGroup.'.initTable', [], false));

        $init->assertStatus(200)
            ->assertJsonStructure(['template', 'meta', 'apiVersion']);

        $params = [
            'columns' => [],
            'meta' => '{"start":0,"length":10,"sort":false,"search": "","forceInfo":false,"searchMode":"full"}',
        ];

        $this->get(route($this->permissionGroup.'.tableData', $params, false))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
