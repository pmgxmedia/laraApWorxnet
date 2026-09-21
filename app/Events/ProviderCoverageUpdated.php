<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Broadcast live updates for a single provider's position on the homepage
 * coverage radar: distance from the searched location, availability, and
 * "path progress" (0-100) simulating how far along an en-route job the
 * provider currently is. Pushed over the public "coverage" websocket
 * channel so every visitor viewing the coverage map updates in real time.
 */
class ProviderCoverageUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $providerId,
        public string $providerName,
        public float $distanceKm,
        public float $bearingDeg,
        public bool $availableNow,
        public int $responseMinutes,
        public int $pathProgress,
        public string $status,
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('coverage');
    }

    public function broadcastAs(): string
    {
        return 'provider.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->providerId,
            'name' => $this->providerName,
            'distance_km' => round($this->distanceKm, 1),
            'bearing_deg' => round($this->bearingDeg, 1),
            'available_now' => $this->availableNow,
            'response_minutes' => $this->responseMinutes,
            'path_progress' => $this->pathProgress,
            'status' => $this->status,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
