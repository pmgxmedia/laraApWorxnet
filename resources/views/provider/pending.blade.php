@extends('layouts.app')

@section('title', 'Application Submitted | WorxNet')

@section('content')
<div class="auth-card" style="text-align:center;">
    <div class="success-icon" aria-hidden="true" style="font-size:2.5rem;color:var(--success);margin-bottom:12px;">&check;</div>
    <h1>Application submitted</h1>
    <p class="muted">
        Thanks, {{ $company->display_name }}! Your provider application is
        <strong>{{ str_replace('_', ' ', $company->verification_status) }}</strong>.
        Our team will review your details and verification documents, then notify you by email once approved.
    </p>

    <dl style="text-align:left;margin-top:24px;">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
            <dt class="muted">Business name</dt><dd>{{ $company->display_name }}</dd>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
            <dt class="muted">Primary area</dt><dd>{{ $company->primary_area }}</dd>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;">
            <dt class="muted">Status</dt><dd>{{ ucfirst($company->verification_status) }}</dd>
        </div>
    </dl>

    <a href="{{ url('/') }}" class="btn btn-primary btn-block" style="margin-top:24px;">Back to Homepage</a>
</div>
@endsection
