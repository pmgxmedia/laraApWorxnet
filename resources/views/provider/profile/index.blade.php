@extends('layouts.app')

@section('title', 'Profile | WorxNet')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <h1 style="margin-bottom:4px;">Profile</h1>
    <p class="muted" style="margin-bottom:20px;">Your account details, ratings and earnings, at a glance.</p>

    <nav class="account-tabs" aria-label="Account sections">
        <a href="{{ route('provider.inbox.index') }}">Inbox</a>
        <a href="{{ route('provider.profile') }}" class="is-active">Profile</a>
    </nav>

    @if (! $company)
        <div class="empty-state">
            Your provider profile isn't set up yet. <a href="{{ route('provider.apply') }}">Apply as a provider</a> to get started.
        </div>
    @else
        {{-- Account details --}}
        <div class="pros-card" style="margin-bottom:28px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                <div>
                    <h2 style="font-size:1.15rem;margin-bottom:2px;">{{ $company->display_name }}</h2>
                    <p class="muted" style="margin:0;font-size:0.9rem;">{{ ucfirst($company->type) }} provider &middot; {{ $company->primary_area ?? 'Area not set' }}</p>
                </div>
                <span class="badge" style="text-transform:capitalize;">
                    {{ str_replace('_', ' ', $company->verification_status) }}
                    @if ($company->is_featured) &middot; Featured @endif
                </span>
            </div>

            <dl>
                <div>
                    <dt>Owner</dt>
                    <dd>{{ $company->owner->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $company->owner->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Phone</dt>
                    <dd>{{ $company->owner->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Travel radius</dt>
                    <dd>{{ $company->travel_radius_km }} km</dd>
                </div>
                <div>
                    <dt>Emergency call-outs</dt>
                    <dd>{{ $company->emergency_available ? 'Available' : 'Not available' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Key stats --}}
        <h2 style="font-size:1.05rem;margin-bottom:12px;">Analytics &amp; statistics</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">Average rating</span>
                <span class="stat-value">{{ $stats['rating_average'] ?: '—' }} @if ($stats['rating_average']) <span style="font-size:1rem;">★</span> @endif</span>
                <span class="stat-sub">{{ $stats['rating_count'] }} {{ Str::plural('review', $stats['rating_count']) }}</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total earnings</span>
                <span class="stat-value">R{{ number_format($stats['total_earnings'], 2) }}</span>
                <span class="stat-sub">From completed &amp; paid jobs</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Completed jobs</span>
                <span class="stat-value">{{ $stats['completed_jobs'] }}</span>
                <span class="stat-sub">All-time</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Awarded bids</span>
                <span class="stat-value">{{ $stats['awarded_bids'] }}</span>
                <span class="stat-sub">R{{ number_format($stats['pipeline_value'], 2) }} contracted</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active bids</span>
                <span class="stat-value">{{ $stats['active_bids'] }}</span>
                <span class="stat-sub">Submitted / shortlisted</span>
            </div>
        </div>

        {{-- Rating breakdown --}}
        <h2 style="font-size:1.05rem;margin-bottom:12px;">Rating breakdown</h2>
        <div class="pros-card" style="margin-bottom:28px;display:grid;gap:10px;">
            @for ($star = 5; $star >= 1; $star--)
                @php
                    $count = $ratingBreakdown[$star] ?? 0;
                    $pct = $stats['rating_count'] ? round(($count / $stats['rating_count']) * 100) : 0;
                @endphp
                <div class="rating-bar-row">
                    <span style="width:44px;flex-shrink:0;">{{ $star }} ★</span>
                    <span class="rating-bar-track"><span class="rating-bar-fill" style="width:{{ $pct }}%;"></span></span>
                    <span style="width:32px;flex-shrink:0;text-align:right;">{{ $count }}</span>
                </div>
            @endfor
        </div>

        {{-- Recent reviews --}}
        <h2 style="font-size:1.05rem;margin-bottom:12px;">Recent reviews</h2>
        @if ($recentReviews->isEmpty())
            <div class="empty-state">No reviews yet.</div>
        @else
            <div style="display:grid;gap:10px;">
                @foreach ($recentReviews as $review)
                    <div class="review-item">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                            <p style="margin:0;font-weight:700;">{{ $review->customer->name ?? 'Customer' }}</p>
                            <span class="review-stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="muted" style="margin:6px 0 0;font-size:0.9rem;">{{ $review->comment }}</p>
                        @endif
                        <p class="muted" style="margin:6px 0 0;font-size:0.78rem;">{{ $review->created_at->format('d M Y') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
