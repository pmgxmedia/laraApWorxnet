<?php

namespace App\Http\Controllers;

use App\Models\ProviderCompany;
use App\Models\ProviderService;
use App\Models\ProviderServiceArea;
use App\Models\ProviderVerification;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderApplicationController extends Controller
{
    /**
     * Show the "apply as a provider" form.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->provider_company_id) {
            return redirect()->route('provider.pending');
        }

        $categories = ServiceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('provider.apply', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store the provider application.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->provider_company_id) {
            return redirect()->route('provider.pending');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:individual,company'],
            'display_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'vat_number' => ['nullable', 'string', 'max:100'],
            'bio' => ['required', 'string', 'max:2000'],
            'primary_area' => ['required', 'string', 'max:255'],
            'travel_radius_km' => ['required', 'integer', 'min:1', 'max:500'],
            'emergency_available' => ['nullable', 'boolean'],

            'service_categories' => ['required', 'array', 'min:1'],
            'service_categories.*' => ['integer', 'exists:service_categories,id'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'speciality_notes' => ['nullable', 'string', 'max:1000'],

            'province' => ['required', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'suburb' => ['nullable', 'string', 'max:255'],
            'radius_km' => ['required', 'integer', 'min:1', 'max:500'],

            'identity_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'business_registration_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'vat_certificate_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $company = DB::transaction(function () use ($validated, $user, $request) {
            $slug = Str::slug($validated['display_name']).'-'.Str::lower(Str::random(6));

            $company = ProviderCompany::create([
                'owner_user_id' => $user->id,
                'type' => $validated['type'],
                'display_name' => $validated['display_name'],
                'slug' => $slug,
                'registration_number' => $validated['registration_number'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'bio' => $validated['bio'],
                'primary_area' => $validated['primary_area'],
                'travel_radius_km' => $validated['travel_radius_km'],
                'emergency_available' => $request->boolean('emergency_available'),
                'verification_status' => 'pending',
                'is_active' => false,
            ]);

            foreach ($validated['service_categories'] as $categoryId) {
                ProviderService::create([
                    'provider_company_id' => $company->id,
                    'service_category_id' => $categoryId,
                    'speciality_notes' => $validated['speciality_notes'] ?? null,
                    'years_experience' => $validated['years_experience'] ?? null,
                ]);
            }

            ProviderServiceArea::create([
                'provider_company_id' => $company->id,
                'province' => $validated['province'],
                'municipality' => $validated['municipality'] ?? null,
                'suburb' => $validated['suburb'] ?? null,
                'radius_km' => $validated['radius_km'],
            ]);

            $documents = [
                'identity_document' => 'identity',
                'business_registration_document' => 'business_registration',
                'vat_certificate_document' => 'vat_certificate',
            ];

            foreach ($documents as $field => $documentType) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store(
                        'provider-verifications/'.$company->id,
                        'public'
                    );

                    ProviderVerification::create([
                        'provider_company_id' => $company->id,
                        'document_type' => $documentType,
                        'file_path' => $path,
                        'status' => 'pending',
                    ]);
                }
            }

            $user->forceFill([
                'role' => 'provider',
                'provider_company_id' => $company->id,
            ])->save();

            return $company;
        });

        return redirect()->route('provider.pending')
            ->with('status', 'Your application has been submitted and is pending review.');
    }

    /**
     * Show a confirmation/status page after applying.
     */
    public function pending(Request $request)
    {
        $user = $request->user();
        $company = $user->providerCompany;

        if (! $company) {
            return redirect()->route('provider.apply');
        }

        return view('provider.pending', [
            'company' => $company,
        ]);
    }
}
