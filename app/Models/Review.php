<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'provider_company_id', 'customer_id', 'appointment_id', 'rating', 'comment',
        'status', 'moderation_reason', 'moderated_by',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
