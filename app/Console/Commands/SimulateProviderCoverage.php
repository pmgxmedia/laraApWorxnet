<?php

namespace App\Console\Commands;

use App\Events\ProviderCoverageUpdated;
use App\Models\ProviderCompany;
use Illuminate\Console\Command;

class SimulateProviderCoverage extends Command
{
    /**
     * php artisan coverage:simulate
     * Run this as a long-lived process (alongside `php artisan websockets:serve`)
     * to feed the homepage coverage radar with live distance, availability,
     * and "en route" path-progress updates over the "coverage" websocket channel.
     */
    protected $signature = 'coverage:simulate
        {--lat=-26.1076 : Reference latitude (defaults to Sandton, Gauteng)}
        {--lng=28.0567 : Reference longitude}
        {--interval=4 : Seconds between broadcast ticks}
        {--ticks=0 : Number of ticks to run (0 = run forever)}';

    protected $description = 'Continuously broadcast simulated live provider distance/availability/path-progress updates for the homepage coverage radar.';

    /** @var array<int, array{progress:int, direction:int}> */
    private array $state = [];

    public function handle(): int
    {
        $lat = (float) $this->option('lat');
        $lng = (float) $this->option('lng');
        $interval = max(1, (int) $this->option('interval'));
        $maxTicks = (int) $this->option('ticks');

        $providers = ProviderCompany::query()
            ->bookable()
            ->nearby($lat, $lng, false)
            ->with('availabilities')
            ->take(8)
            ->get();

        if ($providers->isEmpty()) {
            $this->warn('No bookable providers found — seed the database first (php artisan db:seed).');

            return self::FAILURE;
        }

        $this->info("Simulating live coverage for {$providers->count()} provider(s). Press Ctrl+C to stop.");

        foreach ($providers as $provider) {
            $this->state[$provider->id] = [
                'progress' => random_int(0, 40),
                'direction' => 1,
                'jitter' => (random_int(-150, 150) / 1000), // small km jitter per tick
            ];
        }

        $tick = 0;

        while (true) {
            $tick++;

            foreach ($providers as $provider) {
                $this->broadcastTick($provider, $lat, $lng);
            }

            if ($maxTicks > 0 && $tick >= $maxTicks) {
                break;
            }

            sleep($interval);
        }

        return self::SUCCESS;
    }

    private function broadcastTick(ProviderCompany $provider, float $lat, float $lng): void
    {
        $state = &$this->state[$provider->id];

        // Nudge "en route" progress back and forth between 0-100 to simulate a job in progress.
        $state['progress'] += $state['direction'] * random_int(4, 12);
        if ($state['progress'] >= 100) {
            $state['progress'] = 100;
            $state['direction'] = -1;
        } elseif ($state['progress'] <= 0) {
            $state['progress'] = 0;
            $state['direction'] = 1;
        }

        $baseDistance = (float) ($provider->distance_km ?? 0);
        $distance = max(0.2, $baseDistance + $state['jitter'] * random_int(1, 3));

        $availableNow = $provider->isAvailableNow();
        $status = ! $availableNow
            ? 'Offline'
            : ($state['progress'] > 0 && $state['progress'] < 100 ? 'En route' : 'Available');

        $bearing = $this->bearing($lat, $lng, (float) $provider->base_lat, (float) $provider->base_lng);

        broadcast(new ProviderCoverageUpdated(
            providerId: $provider->id,
            providerName: $provider->display_name,
            distanceKm: $distance,
            bearingDeg: $bearing,
            availableNow: $availableNow,
            responseMinutes: (int) $provider->response_minutes_avg,
            pathProgress: (int) $state['progress'],
            status: $status,
        ));

        $this->line("  → #{$provider->id} {$provider->display_name}: {$distance}km, {$state['progress']}%, {$status}");
    }

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
