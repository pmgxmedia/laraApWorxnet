<?php

namespace App\Http\Controllers;

use App\Models\ProviderCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoverageController extends Controller
{
    /**
     * Public, read-only snapshot of nearby providers for the homepage
     * "coverage" radar — distance + bearing from the searched location,
     * live availability, and travel radius, used to seed the map before
     * live websocket updates (see App\Events\ProviderCoverageUpdated)
     * take over with real-time distance/progress changes.
     */
    public function index(Request $request): JsonResponse
    {
        $lat = (float) $request->query('lat', -26.1076); // default: Sandton, Gauteng
        $lng = (float) $request->query('lng', 28.0567);

        $providers = ProviderCompany::query()
            ->bookable()
            ->nearby($lat, $lng, false)
            ->with('availabilities')
            ->take(8)
            ->get();

        $results = $providers->map(function (ProviderCompany $provider) use ($lat, $lng) {
            $distanceKm = round((float) $provider->distance_km, 1);

            return [
                'id' => $provider->id,
                'name' => $provider->display_name,
                'distance_km' => $distanceKm,
                'bearing_deg' => $this->bearing($lat, $lng, (float) $provider->base_lat, (float) $provider->base_lng),
                'travel_radius_km' => $provider->travel_radius_km,
                'available_now' => $provider->isAvailableNow(),
                'response_minutes' => $provider->response_minutes_avg,
                'verified' => $provider->isVerified(),
                'path_progress' => 0,
            ];
        });

        return response()->json([
            'center' => ['lat' => $lat, 'lng' => $lng],
            'count' => $results->count(),
            'providers' => $results,
        ]);
    }

    /**
     * Initial bearing (degrees, 0 = north, 90 = east) from one lat/lng
     * point to another, used to place providers around the "You" pin.
     */
    private function bearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLng = deg2rad($lng2 - $lng1);

        $y = sin($deltaLng) * cos($lat2Rad);
        $x = cos($lat1Rad) * sin($lat2Rad) - sin($lat1Rad) * cos($lat2Rad) * cos($deltaLng);

        return fmod(rad2deg(atan2($y, $x)) + 360, 360);
    }
}
