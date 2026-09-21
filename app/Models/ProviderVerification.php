<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderVerification extends Model
{
    protected $fillable = [
        'provider_company_id', 'document_type', 'file_path', 'status',
        'reviewer_notes', 'reviewed_by', 'expires_at', 'reviewed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
