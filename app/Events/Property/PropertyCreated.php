<?php

namespace App\Events\Property;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyCreated
{
    use Dispatchable, SerializesModels;

    public Property $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }
}
