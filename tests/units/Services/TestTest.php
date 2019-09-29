<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Builders;

use Tests\TestCase;

class TestTest extends TestCase
{
    /** @test */
    public function test()
    {
        //$class = Enum::class;
        //$class::columns();
        //$this->assertTrue(true);

        $json = json_encode([
            'name'=>false,
        ]);

        $this->assertFalse(json_decode('false'));
        $this->assertFalse(json_decode($json, true)['name']);
    }
}
