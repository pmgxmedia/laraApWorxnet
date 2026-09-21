<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBid extends Model
{
    protected $fillable = [
        'project_id', 'provider_company_id', 'amount', 'scope_included', 'exclusions',
        'warranty_terms', 'estimated_start', 'estimated_duration_days', 'valid_until', 'notes', 'status',
        'seen_at',
    ];

    protected $casts = [
        'estimated_start' => 'date',
        'valid_until' => 'date',
        'seen_at' => 'datetime',
    ];

    /**
     * Bid statuses that represent an update the provider should be notified about
     * (i.e. the customer/platform has moved the bid on from "submitted").
     */
    public const NOTIFIABLE_STATUSES = ['shortlisted', 'awarded', 'declined'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }
}
