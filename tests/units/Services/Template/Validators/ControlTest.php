<?php

namespace Services\Template\Validators;

use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Services\Template\Validators\Controls;

class ControlTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->template = new Obj(['controls' => []]);
    }

    /** @test */
    public function cannot_validate_with_wrong_button_type()
    {
        $this->template->get('controls')->push('WRONG_CONTROL');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('Unknown control(s) Found: "WRONG_CONTROL"');

        $this->validate();
    }

    /** @test */
    public function can_validate()
    {
        $this->createRoute();

        $this->template->get('controls')->push('columns');

        $this->validate();

        $this->assertTrue(true);
    }

    private function validate()
    {
        $this->validator = new Controls($this->template);

        $this->validator->validate();
    }

    private function createRoute()
    {
        Route::any('/test.button.create')->name('test.create');
        Route::getRoutes()->refreshNameLookups();

        return 'test.create';
    }
}
