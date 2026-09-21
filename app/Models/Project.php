<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'service_category_id', 'title', 'description', 'property_type',
        'budget_type', 'budget_min', 'budget_max', 'budget_visible_to_providers',
        'area_label', 'lat', 'lng', 'exact_address', 'bid_deadline', 'status',
        'awarded_provider_company_id', 'is_public',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'budget_visible_to_providers' => 'boolean',
        'is_public' => 'boolean',
        'bid_deadline' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function bids()
    {
        return $this->hasMany(ProjectBid::class);
    }

    public function awardedProvider()
    {
        return $this->belongsTo(ProviderCompany::class, 'awarded_provider_company_id');
    }

    public function isBiddingOpen(): bool
    {
        if ($this->status !== 'live') {
            return false;
        }

        return ! $this->bid_deadline || $this->bid_deadline->isFuture();
    }
}
