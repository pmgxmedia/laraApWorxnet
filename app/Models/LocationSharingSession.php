<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSharingSession extends Model
{
    protected $fillable = [
        'appointment_id', 'sharer_user_id', 'lat', 'lng', 'started_at', 'expires_at', 'stopped_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function sharer()
    {
        return $this->belongsTo(User::class, 'sharer_user_id');
    }

    public function isActive(): bool
    {
        return ! $this->stopped_at && $this->expires_at->isFuture();
    }
}
