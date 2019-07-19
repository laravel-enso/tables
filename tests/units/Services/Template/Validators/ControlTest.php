<?php

namespace Services\Template\Validators;

use Route;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Services\Template\Validators\Controls;

class ControlTest extends TestCase
{


    /** @test */
    public function cannot_validate_with_wrong_button_type()
    {
        $this->validate(
            [
                'controls' => [
                    'WRONG_CONTROL'
                ],
            ],
            false
        );

    }

    /** @test */
    public function can_validate()
    {
        $this->createRoute();

        $this->validate(
            [
                'controls' => [
                    'columns'
                ],
            ],
            true
        );
    }


    private function validate(array $template, bool $isValid)
    {
        $validator = new Controls(
            new Obj($template)
        );

        try {
            $validator->validate();

            if (!$isValid)
                $this->fail('should throw TemplateException');
        } catch (TemplateException $e) {
            if ($isValid)
                $this->fail('should not throw TemplateException' . PHP_EOL . $e);
        }

        $this->assertTrue(true);
    }

    private function createRoute()
    {
        Route::any('/test.button.create')->name('test.create');
        Route::getRoutes()->refreshNameLookups();

        return 'test.create';
    }

}
