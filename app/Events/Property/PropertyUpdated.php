<?php

namespace App\Events\Property;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class PropertyUpdated
{
    use Dispatchable, SerializesModels;

    public Property $property;
    public array $changes;

    public function __construct(Property $property, array $changes)
    {
        $this->property = $property;
        $this->changes = $changes;
    }
}
