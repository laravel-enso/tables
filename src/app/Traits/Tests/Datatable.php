<?php

namespace LaravelEnso\VueDatatable\app\Traits\Tests;

trait Datatable
{
    /** @test */
    public function can_view_index()
    {
        if (! isset($this->permissionGroup)) {
            throw Exception('"permissionGroup" property is missing from your test');
        }

        $meta = '{"start":0,"length":10,"sort":false,"total":false,"enum":false,"date":false,"actions":true,"forceInfo":false}';
        $init = $this->get(route($this->permissionGroup.'.initTable', [], false));

        $init->assertStatus(200)
            ->assertJsonStructure(['template']);

        $params = (array) json_decode($init->getContent()) + [
            'columns' => '{}',
            'meta' => $meta,
        ];

        $this->get(route($this->permissionGroup.'.tableData', $params, false))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
