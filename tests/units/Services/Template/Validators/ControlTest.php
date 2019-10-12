<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\ControlException;
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

        $this->expectException(ControlException::class);

        $this->expectExceptionMessage(ControlException::undefined('WRONG_CONTROL')->getMessage());

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
