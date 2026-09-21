<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'reason', 'changes', 'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public static function record(?int $userId, string $action, $subject = null, ?string $reason = null, array $changes = []): self
    {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'reason' => $reason,
            'changes' => $changes,
            'ip_address' => request()->ip(),
        ]);
    }
}
