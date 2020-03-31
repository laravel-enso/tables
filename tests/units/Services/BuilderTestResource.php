<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Illuminate\Http\Resources\Json\JsonResource;

class BuilderTestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'relation' => 'relation',
        ];
    }
}
