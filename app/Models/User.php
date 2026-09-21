<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'avatar_path',
        'preferred_area',
        'last_known_lat',
        'last_known_lng',
        'location_shared_at',
        'marketing_opt_in',
        'provider_company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_known_lat' => 'float',
        'last_known_lng' => 'float',
        'location_shared_at' => 'datetime',
        'marketing_opt_in' => 'boolean',
    ];

    public function ownedProviderCompany()
    {
        return $this->hasOne(ProviderCompany::class, 'owner_user_id');
    }

    public function providerCompany()
    {
        return $this->belongsTo(ProviderCompany::class, 'provider_company_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'customer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isProvider(): bool
    {
        return in_array($this->role, ['provider', 'provider_team'], true);
    }

    /**
     * Number of unread messages across all conversations belonging to
     * this provider's company (messages sent by someone else, not yet read).
     */
    public function unreadMessageCount(): int
    {
        if (! $this->provider_company_id) {
            return 0;
        }

        return Message::whereHas('conversation', function ($query) {
            $query->where('provider_company_id', $this->provider_company_id);
        })
            ->whereNull('read_at')
            ->where('sender_id', '!=', $this->id)
            ->count();
    }

    /**
     * Number of project bids belonging to this provider's company that have
     * moved to a notifiable status (shortlisted/awarded/declined) since the
     * provider last viewed them.
     */
    public function unseenBidUpdateCount(): int
    {
        if (! $this->provider_company_id) {
            return 0;
        }

        return ProjectBid::where('provider_company_id', $this->provider_company_id)
            ->whereIn('status', ProjectBid::NOTIFIABLE_STATUSES)
            ->whereNull('seen_at')
            ->count();
    }

    /**
     * Combined badge count shown on the inbox icon in the header.
     */
    public function inboxNotificationCount(): int
    {
        return $this->unreadMessageCount() + $this->unseenBidUpdateCount();
    }

    /**
     * Determine whether the user can access the Filament admin panel.
     * Only admin and super_admin roles have full platform privileges.
     */
    public function canAccessFilament(): bool
    {
        return $this->isAdmin() && $this->status === 'active';
    }
}
