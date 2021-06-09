<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Template\Builders\Buttons;
use Mockery;
use Route;
use Tests\TestCase;

class ButtonsTest extends TestCase
{
    private $meta;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->meta = new Obj([]);

        $this->template = new Obj([
            'auth' => false,
            'buttons' => [],
        ]);
    }

    /** @test */
    public function can_build_with_type()
    {
        $this->template->get('buttons')->push(new Obj(['type' => 'row']));

        $this->build();

        $this->assertEquals(1, $this->template->get('buttons')->get('row')->count());

        $this->assertEquals(0, $this->template->get('buttons')->get('global')->count());

        $this->assertTrue($this->template->get('actions'));
    }

    /** @test */
    public function cannot_build_when_user_cannot_access_to_route()
    {
        $user = Mockery::mock(Config::get('auth.providers.users.model'))->makePartial();

        $this->actingAs($user);

        $this->template->get('buttons')->push(new Obj([
            'action' => '',
            'type' => 'row',
            'fullRoute' => 'test',
        ]));

        $this->template->get('buttons')->push('create');

        $this->template->set('auth', true);

        $user->shouldReceive('cannot')->andReturn(true);

        $this->build();

        $this->assertEmpty($this->template->get('buttons')->get('global'));

        $this->assertEmpty($this->template->get('buttons')->get('row'));
    }

    /** @test */
    public function can_build_with_route()
    {
        Route::any('test')->name('test');

        Route::getRoutes()->refreshNameLookups();

        $this->template->get('buttons')->push(new Obj([
            'action' => 'ajax',
            'type' => 'row',
            'fullRoute' => 'test',
        ]));

        $this->build();

        $this->assertEquals(
            '/test?dtRowId',
            $this->template->get('buttons')
                ->get('row')
                ->first()
                ->get('path')
        );
    }

    /** @test */
    public function can_build_with_predefined_buttons()
    {
        $this->template->set('buttons', new Collection(['create', 'show']));

        $this->build();

        $this->assertEquals(
            (new Obj(Config::get('enso.tables.buttons.global.create')))
                ->put('name', 'create')
                ->except('routeSuffix'),
            $this->template->get('buttons')->get('global')->first()->except('route')
        );

        $this->assertEquals(
            (new Obj(Config::get('enso.tables.buttons.row.show')))
                ->put('name', 'show')
                ->except('routeSuffix'),
            $this->template->get('buttons')->get('row')->first()->except('route')
        );

        $this->assertEquals(
            '.'.Config::get('enso.tables.buttons.global.create.routeSuffix'),
            $this->template->get('buttons')->get('global')->first()->get('route')
        );

        $this->assertEquals(
            '.'.Config::get('enso.tables.buttons.row.show.routeSuffix'),
            $this->template->get('buttons')->get('row')->first()->get('route')
        );
    }

    private function build(): void
    {
        (new Buttons($this->template))->build();
    }
}
