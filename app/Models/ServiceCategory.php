<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'icon', 'description', 'supports_emergency', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'supports_emergency' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class);
    }

    public function providerCompanies()
    {
        return $this->belongsToMany(ProviderCompany::class, 'provider_services');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
