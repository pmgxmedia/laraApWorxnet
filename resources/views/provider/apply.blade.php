@extends('layouts.app')

@section('title', 'Apply as a Provider | WorxNet')

@section('content')
<div class="apply-card">
    <span class="eyebrow">Step 2 of 2</span>
    <h1>Apply as a WorxNet provider</h1>
    <p class="muted">Tell us about your business — our team will review your application and verification documents before you go live.</p>

    @if ($errors->any())
        <div class="alert-error">
            <strong>Please fix the following:</strong>
            <ul style="margin:8px 0 0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.apply.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <h2 class="form-section-title">Business details</h2>

        <div class="field">
            <label>Provider type</label>
            <div class="radio-row">
                <label><input type="radio" name="type" value="individual" {{ old('type', 'individual') === 'individual' ? 'checked' : '' }}> Individual / Sole trader</label>
                <label><input type="radio" name="type" value="company" {{ old('type') === 'company' ? 'checked' : '' }}> Registered company</label>
            </div>
        </div>

        <div class="field @error('display_name') has-error @enderror">
            <label for="display_name">Business / display name</label>
            <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}" required>
        </div>

        <div class="field-row">
            <div class="field @error('registration_number') has-error @enderror">
                <label for="registration_number">Registration number (optional)</label>
                <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number') }}">
            </div>
            <div class="field @error('vat_number') has-error @enderror">
                <label for="vat_number">VAT number (optional)</label>
                <input type="text" id="vat_number" name="vat_number" value="{{ old('vat_number') }}">
            </div>
        </div>

        <div class="field @error('bio') has-error @enderror">
            <label for="bio">Tell customers about your business</label>
            <textarea id="bio" name="bio" rows="4" required>{{ old('bio') }}</textarea>
        </div>

        <hr class="form-divider">
        <h2 class="form-section-title">Services offered</h2>
        <p class="muted" style="margin-bottom:14px;font-size:0.9rem;">Select every service category you provide.</p>

        <div class="field @error('service_categories') has-error @enderror">
            <div class="checkbox-grid">
                @foreach ($categories as $category)
                    <label>
                        <input type="checkbox" name="service_categories[]" value="{{ $category->id }}"
                            {{ in_array($category->id, old('service_categories', [])) ? 'checked' : '' }}>
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="field-row">
            <div class="field @error('years_experience') has-error @enderror">
                <label for="years_experience">Years of experience</label>
                <input type="number" id="years_experience" name="years_experience" min="0" max="60" value="{{ old('years_experience') }}">
            </div>
            <div class="field">
                <label style="display:flex;align-items:center;gap:8px;margin-top:26px;">
                    <input type="checkbox" name="emergency_available" value="1" style="width:auto;" {{ old('emergency_available') ? 'checked' : '' }}>
                    Available for emergency call-outs
                </label>
            </div>
        </div>

        <div class="field @error('speciality_notes') has-error @enderror">
            <label for="speciality_notes">Specialities / brands you work with (optional)</label>
            <textarea id="speciality_notes" name="speciality_notes" rows="2">{{ old('speciality_notes') }}</textarea>
        </div>

        <hr class="form-divider">
        <h2 class="form-section-title">Coverage area</h2>

        <div class="field @error('primary_area') has-error @enderror">
            <label for="primary_area">Primary area (shown to customers)</label>
            <input type="text" id="primary_area" name="primary_area" placeholder="e.g. Sandton, Gauteng" value="{{ old('primary_area') }}" required>
        </div>

        <div class="field-row">
            <div class="field @error('province') has-error @enderror">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" value="{{ old('province') }}" required>
            </div>
            <div class="field @error('municipality') has-error @enderror">
                <label for="municipality">Municipality (optional)</label>
                <input type="text" id="municipality" name="municipality" value="{{ old('municipality') }}">
            </div>
        </div>

        <div class="field-row">
            <div class="field @error('suburb') has-error @enderror">
                <label for="suburb">Suburb (optional)</label>
                <input type="text" id="suburb" name="suburb" value="{{ old('suburb') }}">
            </div>
            <div class="field @error('radius_km') has-error @enderror">
                <label for="radius_km">Service area radius (km)</label>
                <input type="number" id="radius_km" name="radius_km" min="1" max="500" value="{{ old('radius_km', 20) }}" required>
            </div>
        </div>

        <div class="field @error('travel_radius_km') has-error @enderror">
            <label for="travel_radius_km">Maximum travel radius (km)</label>
            <input type="number" id="travel_radius_km" name="travel_radius_km" min="1" max="500" value="{{ old('travel_radius_km', 20) }}" required>
        </div>

        <hr class="form-divider">
        <h2 class="form-section-title">Verification documents</h2>
        <p class="muted" style="margin-bottom:14px;font-size:0.9rem;">Upload documents so our team can verify your identity and business. PDF, JPG or PNG, max 5MB each.</p>

        <div class="field @error('identity_document') has-error @enderror">
            <label for="identity_document">ID document / passport</label>
            <input type="file" id="identity_document" name="identity_document" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>

        <div class="field @error('business_registration_document') has-error @enderror">
            <label for="business_registration_document">Business registration certificate (if applicable)</label>
            <input type="file" id="business_registration_document" name="business_registration_document" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="field @error('vat_certificate_document') has-error @enderror">
            <label for="vat_certificate_document">VAT certificate (if applicable)</label>
            <input type="file" id="vat_certificate_document" name="vat_certificate_document" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top:12px;">Submit Application</button>
        <p class="field-hint" style="text-align:center;margin-top:10px;">By submitting, you agree to WorxNet's Provider Agreement and verification process.</p>
    </form>
</div>
@endsection
