<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    protected $fillable = [
        'provider_company_id', 'service_category_id', 'speciality_notes', 'brands', 'years_experience',
    ];

    protected $casts = [
        'brands' => 'array',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}
