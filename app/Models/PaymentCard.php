<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model
{
    use HasFactory;

    protected $primaryKey = 'card_id';

    protected $fillable = [
        'cardholder_name',
        'card_number',
        'expiry_date',
        'cvv',
        'agent_id',
        'office_id'
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'agent_id');
    }

    public function office()
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id', 'office_id');
    }
}
