<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderServiceArea extends Model
{
    protected $fillable = [
        'provider_company_id', 'province', 'municipality', 'suburb', 'center_lat', 'center_lng', 'radius_km',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }
}
