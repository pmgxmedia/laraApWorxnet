<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'customer_id', 'provider_company_id', 'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
