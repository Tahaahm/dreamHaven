<?php

namespace App\Events\Property;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyViewed
{
    use Dispatchable, SerializesModels;

    public Property $property;
    public ?string $userId;
    public string $ipAddress;

    public function __construct(Property $property, ?string $userId = null, string $ipAddress = '127.0.0.1')
    {
        $this->property = $property;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
    }
}
