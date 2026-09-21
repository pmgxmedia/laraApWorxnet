<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    protected $fillable = [
        'provider_company_id', 'day_of_week', 'start_time', 'end_time', 'is_emergency_slot',
    ];

    protected $casts = [
        'is_emergency_slot' => 'boolean',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }
}
