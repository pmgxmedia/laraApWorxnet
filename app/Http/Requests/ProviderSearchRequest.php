<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderSearchRequest extends FormRequest
{
    /**
     * Anyone (including guests) is allowed to search for providers.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category' => ['required', 'string', 'max:100'],
            'urgency' => ['nullable', 'string', 'in:emergency,24h,week,planned'],
            'property_type' => ['nullable', 'string', 'max:50'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:-180,180'],
            'location_text' => ['nullable', 'string', 'max:150', 'required_without_all:lat,lng'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_category.required' => 'Please select a service to search for.',
            'location_text.required_without_all' => 'Please enter your area, or share your live location.',
            'lat.required_with' => 'Both latitude and longitude are required.',
            'lng.required_with' => 'Both latitude and longitude are required.',
        ];
    }
}
