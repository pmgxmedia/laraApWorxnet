<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'provider_company_id', 'service_category_id',
        'target_province', 'placement', 'starts_at', 'ends_at', 'status', 'created_by',
        'impressions', 'clicks', 'conversions',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function codes()
    {
        return $this->hasMany(PromotionCode::class);
    }

    public function isLive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        return (! $this->starts_at || $this->starts_at->lte($now))
            && (! $this->ends_at || $this->ends_at->gte($now));
    }
}
