<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'service_request_id', 'project_id', 'customer_id', 'provider_company_id', 'scheduled_at',
        'status', 'payment_method', 'payment_status', 'final_amount',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function locationSharingSessions()
    {
        return $this->hasMany(LocationSharingSession::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
