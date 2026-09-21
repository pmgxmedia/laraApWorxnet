<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderSearchRequest;
use App\Models\ProviderCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ProviderSearchController extends Controller
{
    /**
     * Search for verified, active service providers matching the given
     * service category, urgency, and location (precise lat/lng or a
     * free-text area fallback), ranked by a blended match score.
     */
    public function search(ProviderSearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $hasCoords = isset($data['lat']) && isset($data['lng']);
        $emergency = ($data['urgency'] ?? null) === 'emergency';

        $query = ProviderCompany::query()
            ->bookable()
            ->offeringService($data['service_category']);

        if ($emergency) {
            $query->where('emergency_available', true);
        }

        if ($hasCoords) {
            $query->nearby((float) $data['lat'], (float) $data['lng']);
        } elseif (! empty($data['location_text'])) {
            $needle = $data['location_text'];
            $query->where(function (Builder $q) use ($needle) {
                $q->where('primary_area', 'like', "%{$needle}%")
                    ->orWhereHas('serviceAreas', function (Builder $sq) use ($needle) {
                        $sq->where('suburb', 'like', "%{$needle}%")
                            ->orWhere('municipality', 'like', "%{$needle}%")
                            ->orWhere('province', 'like', "%{$needle}%");
                    });
            });
        }

        $providers = $query
            ->with(['serviceCategories', 'availabilities'])
            ->get();

        $results = $providers->map(function (ProviderCompany $provider) use ($hasCoords, $emergency) {
            $distanceKm = $hasCoords ? round((float) $provider->distance_km, 1) : null;
            $availableNow = $provider->isAvailableNow();

            $matchScore = 0
                + ($provider->isVerified() ? 20 : 0)
                + ($availableNow ? 25 : 0)
                + ($emergency && $provider->emergency_available ? 30 : 0)
                + ($provider->rating_average * 6)
                + ($distanceKm !== null ? max(0, 100 - $distanceKm) * 0.3 : 0);

            return [
                'id' => $provider->id,
                'name' => $provider->display_name,
                'area' => $provider->primary_area,
                'rating' => round((float) $provider->rating_average, 1),
                'reviews' => $provider->rating_count,
                'distance_km' => $distanceKm,
                'response_minutes' => $provider->response_minutes_avg,
                'available_now' => $availableNow,
                'verified' => $provider->isVerified(),
                'featured' => (bool) $provider->is_featured,
                'emergency_ready' => (bool) $provider->emergency_available,
                'services' => $provider->serviceCategories->pluck('slug'),
                'match_score' => round($matchScore, 2),
            ];
        })
            ->sortByDesc('match_score')
            ->values();

        return response()->json([
            'count' => $results->count(),
            'used_coordinates' => $hasCoords,
            'providers' => $results,
        ]);
    }
}
