<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    /**
     * Persist an enquiry/request for help submitted from the homepage
     * "Request Help" modal. Requires a logged-in customer so the request
     * is attached to an account that can later be matched to providers.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_category' => ['required', 'string', 'exists:service_categories,slug'],
            'property_type' => ['nullable', 'string', 'in:home,complex,business,office,retail,estate'],
            'urgency' => ['nullable', 'string', 'in:emergency,24h,week,planned'],
            'area_label' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'provider_name' => ['nullable', 'string', 'max:255'],
        ]);

        $category = ServiceCategory::where('slug', $validated['service_category'])->firstOrFail();

        $title = $validated['provider_name']
            ? "Enquiry for {$validated['provider_name']}"
            : "Request help \u2014 {$category->name}";

        $description = trim($validated['notes'] ?? '');
        if ($validated['provider_name']) {
            $description .= ($description ? "\n\n" : '')."Who you'd like to contact: {$validated['provider_name']}";
        }

        $serviceRequest = ServiceRequest::create([
            'customer_id' => $request->user()->id,
            'service_category_id' => $category->id,
            'title' => Str::limit($title, 255),
            'description' => Str::limit($description, 2000),
            'property_type' => $validated['property_type'] ?? 'home',
            'urgency' => $validated['urgency'] ?? '24h',
            'area_label' => $validated['area_label'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'status' => 'submitted',
        ]);

        return response()->json([
            'service_request' => [
                'id' => $serviceRequest->id,
                'title' => $serviceRequest->title,
            ],
        ], 201);
    }
}
