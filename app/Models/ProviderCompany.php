<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProviderCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id', 'type', 'display_name', 'slug', 'registration_number', 'vat_number',
        'bio', 'logo_path', 'primary_area', 'base_lat', 'base_lng', 'travel_radius_km',
        'emergency_available', 'rating_average', 'rating_count', 'completed_jobs',
        'response_minutes_avg', 'verification_status', 'is_featured', 'is_active', 'verified_at',
    ];

    protected $casts = [
        'emergency_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'base_lat' => 'float',
        'base_lng' => 'float',
        'rating_average' => 'float',
        'verified_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(ProviderTeamMember::class);
    }

    public function services()
    {
        return $this->hasMany(ProviderService::class);
    }

    public function serviceCategories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'provider_services');
    }

    public function serviceAreas()
    {
        return $this->hasMany(ProviderServiceArea::class);
    }

    public function availabilities()
    {
        return $this->hasMany(ProviderAvailability::class);
    }

    public function verifications()
    {
        return $this->hasMany(ProviderVerification::class);
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

    public function projectBids()
    {
        return $this->hasMany(ProjectBid::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Scope: only providers that are active and fully verified,
     * i.e. safe to surface in public search results.
     */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('verification_status', 'verified');
    }

    /**
     * Scope: filter to providers offering a given service category
     * (accepts a slug or numeric id).
     */
    public function scopeOfferingService(Builder $query, string|int $category): Builder
    {
        return $query->whereHas('serviceCategories', function (Builder $q) use ($category) {
            if (is_numeric($category)) {
                $q->where('service_categories.id', $category);
            } else {
                $q->where('service_categories.slug', $category);
            }
        });
    }

    /**
     * Scope: annotate each result with a `distance_km` column computed via the
     * Haversine great-circle formula from the given lat/lng, and (optionally)
     * restrict results to providers whose travel radius covers that distance.
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, bool $withinTravelRadius = true): Builder
    {
        $haversine = '(6371 * acos(least(1, greatest(-1,
            cos(radians(?)) * cos(radians(provider_companies.base_lat)) *
            cos(radians(provider_companies.base_lng) - radians(?)) +
            sin(radians(?)) * sin(radians(provider_companies.base_lat))
        ))))';

        $query->whereNotNull('provider_companies.base_lat')
            ->whereNotNull('provider_companies.base_lng')
            ->selectRaw('provider_companies.*, '.$haversine.' as distance_km', [$lat, $lng, $lat]);

        if ($withinTravelRadius) {
            $query->havingRaw('distance_km <= provider_companies.travel_radius_km');
        }

        return $query->orderBy('distance_km');
    }

    /**
     * Whether the provider has an availability slot covering right now
     * (based on the current day of week and time in the app timezone).
     */
    public function isAvailableNow(): bool
    {
        $now = Carbon::now();
        $dayOfWeek = (int) $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $time = $now->format('H:i:s');

        return $this->availabilities
            ->where('day_of_week', $dayOfWeek)
            ->contains(function ($slot) use ($time) {
                return $slot->start_time <= $time && $slot->end_time >= $time;
            });
    }
}
