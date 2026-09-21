<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'service_category_id', 'title', 'description', 'property_type', 'urgency',
        'area_label', 'lat', 'lng', 'exact_address', 'budget_min', 'budget_max', 'status',
        'accepted_provider_company_id',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function media()
    {
        return $this->hasMany(ServiceRequestMedia::class);
    }

    public function matches()
    {
        return $this->hasMany(ProviderMatch::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function acceptedProvider()
    {
        return $this->belongsTo(ProviderCompany::class, 'accepted_provider_company_id');
    }
}
