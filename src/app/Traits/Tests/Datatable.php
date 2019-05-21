<?php

namespace LaravelEnso\Tables\app\Traits\Tests;

trait Datatable
{
    /** @test */
    public function can_view_index()
    {
        if (! isset($this->permissionGroup)) {
            throw \Exception('"permissionGroup" property is missing from your test');
        }

        $init = $this->get(route($this->permissionGroup.'.initTable', [], false));

        $init->assertStatus(200)
            ->assertJsonStructure(['template']);

        $meta = '{"start":0,"length":10,"sort":false,"total":false,"enum":false,"date":false,"translatable": false,"actions":true,"forceInfo":false}';

        $params = json_decode($init->getContent(), true) + [
            'columns' => [[
                'name' => 'id',
                'meta' => ['sortable'],
            ]],
            'meta' => $meta,
        ];

        $this->get(route($this->permissionGroup.'.tableData', $params, false))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
