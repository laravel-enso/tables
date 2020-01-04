<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Export;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\App\Services\Data\Computors\OptimalChunk;
use Tests\TestCase;

class OptimalChunkTest extends TestCase
{
    /** @test */
    public function can_get_correct_chunk_within_limits()
    {
        $limits = OptimalChunk::ChunkPerLimit;

        (new Collection($limits))->map(fn ($limit, $index) => [
            $index ? $limits[$index - 1][0] : 0, ...$limit
        ])->each(fn ($limit) => $this->assertCorrectChunk($limit));
    }

    /** @test */
    public function can_get_maximal_chunk_above_limits()
    {
        $limit = (new Collection(OptimalChunk::ChunkPerLimit))->pop();

        $this->assertEquals(OptimalChunk::get($limit[0] + 1), OptimalChunk::MaxChunk);
    }

    private function assertCorrectChunk($limit)
    {
        $count = rand($limit[0], $limit[1]);

        $this->assertEquals(OptimalChunk::get($count), $limit[2]);
    }
}
