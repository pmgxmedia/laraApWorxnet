<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderMatch extends Model
{
    protected $table = 'provider_matches';

    protected $fillable = [
        'service_request_id', 'provider_company_id', 'match_score', 'distance_km', 'status',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }
}
