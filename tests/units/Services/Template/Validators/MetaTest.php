<?php

namespace Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Services\Template\Validators\Meta;

class MetaTest extends TestCase
{
    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->validate(
            [
                'meta' => [
                    'wrong_attribute' => 'r'
                ],
            ],
            false
        );
    }

    /** @test */
    public function cannot_validate_nested_column_with_sortable()
    {
        $this->validate(
            [
                'name' => 'parent.child',
                'meta' => [
                    'sortable'
                ]
            ],
            false
        );
    }

    /** @test */
    public function can_validate()
    {
        $this->validate(
            [
                'name' => 'column',
                'meta' => [
                    'sortable'
                ]
            ],
            true
        );

    }


    private function validate(array $column, bool $isValid)
    {
        try {
            Meta::validate(new Obj($column));

            if (!$isValid)
                $this->fail('should throw TemplateException');
        } catch (TemplateException $e) {
            if ($isValid)
                $this->fail('should not throw TemplateException' . PHP_EOL . $e);
        }

        $this->assertTrue(true);

    }

}
