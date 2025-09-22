<?php

namespace App\Events\Property;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyFavorited
{
    use Dispatchable, SerializesModels;

    public Property $property;
    public string $userId;

    public function __construct(Property $property, string $userId)
    {
        $this->property = $property;
        $this->userId = $userId;
    }
}
