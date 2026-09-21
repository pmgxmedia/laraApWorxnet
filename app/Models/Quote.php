<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'service_request_id', 'provider_company_id', 'amount', 'line_items', 'vat_included',
        'exclusions', 'warranty_terms', 'valid_until', 'status',
    ];

    protected $casts = [
        'line_items' => 'array',
        'vat_included' => 'boolean',
        'valid_until' => 'date',
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
