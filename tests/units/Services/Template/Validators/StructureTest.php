<?php

namespace Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Structure as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Structure;

class StructureTest extends TestCase
{

    /** @test */
    public function cannot_validate_without_mandatory_attribute()
    {
        $this->validate(
            [
                'routePrefix' => 'r',
            ],
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->validate(
            $this->basicTemplate([
                'wrong_attribute' => 'r',
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_format()
    {
        $this->validate(
            $this->basicTemplate([
                'lengthMenu' => 'NOT_ARRAY',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'debounce' => 'NOT_NUMBER',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'method' => 'NOT_METHOD',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'selectable' => 'NOT_BOOL',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'comparisonOperator' => 'NOT_LIKE',
            ]),
            false
        );
    }

    /** @test */
    public function can_validate()
    {
        $this->validate(
            $this->basicTemplate([
                'lengthMenu' => new Obj([]),
                'debounce' => 10,
                'method' => 'POST',
                'selectable' => true,
                'comparisonOperator' => 'LIKE',
                'name' => 'name',
            ]),
            true
        );

    }

    private function basicTemplate($template = [])
    {
        $baseTemplate = array_flip(Attributes::Mandatory);
        return array_merge($baseTemplate, $template);
    }

    private function validate(array $template, bool $isValid)
    {
        $validator = new Structure(
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

}
