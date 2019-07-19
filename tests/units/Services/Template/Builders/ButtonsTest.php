<?php

namespace Services\Template\Builders;

use App\User;
use Mockery;
use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template\Builders\Buttons;

class ButtonsTest extends TestCase
{
    private $meta;

    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        $this->meta = new Obj([]);
    }


    /** @test */
    public function can_build_with_type()
    {
        $this->template = $this->basicTemplate([
            [
                'type'=>'row',
            ]
        ]);

        $this->build();

        $this->assertEquals(1, $this->template['buttons']['row']->count());
        $this->assertEquals(0, $this->template['buttons']['global']->count());
    }

    /** @test */
    public function when_there_is_row_type_then_should_set_actions_on_template()
    {
        $this->template = $this->basicTemplate([
            [
                'type'=>'row',
            ]
        ]);

        $this->build();

        $this->assertTrue($this->template['actions']);
    }


    /** @test */
    public function cannot_build_when_user_cannot_access_to_route()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('cannot')->andReturn(true);
        $this->actingAs($user);

        $this->template = $this->basicTemplate([
            [
                'action' => '',
                'type' => 'row',
                'fullRoute' => 'test',
            ],
            'create'
        ], true);


        $this->build();

        $this->assertEmpty($this->template['buttons']['global']);
        $this->assertEmpty($this->template['buttons']['row']);
    }

    /** @test */
    public function can_build_with_route()
    {
        Route::any('test')->name('test');
        Route::getRoutes()->refreshNameLookups();

        $this->template = $this->basicTemplate([
            [
                'action' => 'ajax',
                'type' => 'row',
                'fullRoute' => 'test',
            ]
        ]);

        $this->build();

        $this->assertEquals('/test?dtRowId', $this->template['buttons']['row'][0]['path']);
    }

    /** @test */
    public function can_build_with_predefined_button()
    {
        $this->template = $this->basicTemplate([
            'create',
        ]);

        $this->build();

        $this->assertEquals(config('enso.tables.buttons.global.create.label'), $this->template['buttons']['global'][0]['label']);
    }


    private function basicTemplate($buttons = [], $auth = false)
    {
        $template = [
            'auth' => $auth,
            'buttons' => $buttons,
        ];
        return new Obj($template);
    }

    private function build(): void
    {
        $structure = new Buttons(
            $this->template,
            $this->meta
        );

        $structure->build();
    }

}

