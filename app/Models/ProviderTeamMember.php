<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderTeamMember extends Model
{
    protected $fillable = ['provider_company_id', 'user_id', 'job_title', 'can_accept_jobs', 'is_active'];

    protected $casts = [
        'can_accept_jobs' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
