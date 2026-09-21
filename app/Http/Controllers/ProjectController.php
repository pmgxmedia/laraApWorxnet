<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Public, read-only feed of live posted projects for the homepage
     * "Post a project" listing — includes the customer's first name,
     * live bid counts, and full bidder details (amount, scope, provider
     * rating) for the expandable bid list.
     */
    public function index(): JsonResponse
    {
        $projects = Project::query()
            ->where('is_public', true)
            ->whereIn('status', ['live', 'awarded', 'booked', 'in_progress', 'completed'])
            ->with([
                'customer',
                'serviceCategory',
                'bids' => function ($query) {
                    $query->orderBy('amount');
                },
                'bids.providerCompany',
            ])
            ->latest()
            ->take(12)
            ->get();

        $results = $projects->map(function (Project $project) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'category' => $project->serviceCategory?->slug,
                'category_label' => $project->serviceCategory?->name,
                'location' => $project->area_label,
                'budget_label' => $this->budgetLabel($project),
                'posted_at' => $project->created_at?->diffForHumans(),
                'customer_first_name' => \Illuminate\Support\Str::before($project->customer?->name ?? 'Customer', ' '),
                'bids_count' => $project->bids->count(),
                'lowest_bid' => $project->bids->first()?->amount,
                'bids' => $project->bids->map(function ($bid) {
                    return [
                        'id' => $bid->id,
                        'provider_name' => $bid->providerCompany?->display_name ?? 'Provider',
                        'provider_rating' => $bid->providerCompany ? round((float) $bid->providerCompany->rating_average, 1) : null,
                        'provider_reviews' => $bid->providerCompany?->rating_count,
                        'provider_verified' => $bid->providerCompany?->isVerified() ?? false,
                        'amount' => (float) $bid->amount,
                        'scope' => $bid->scope_included,
                        'warranty' => $bid->warranty_terms,
                        'submitted_at' => $bid->created_at?->diffForHumans(),
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'count' => $results->count(),
            'projects' => $results,
        ]);
    }

    /**
     * Persist a project posted from the homepage "Post a project" form.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'exists:service_categories,slug'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'property_type' => ['nullable', 'in:home,complex,business,office,retail,estate'],
            'budget_type' => ['nullable', 'in:fixed,range,quote'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $category = ServiceCategory::where('slug', $validated['category'])->firstOrFail();

        $budgetType = $validated['budget_type'] ?? 'range';
        $budgetMin = ! empty($validated['budget_min']) ? $validated['budget_min'] : null;
        $budgetMax = ! empty($validated['budget_max']) ? $validated['budget_max'] : null;

        if ($budgetType === 'quote') {
            $budgetMin = null;
            $budgetMax = null;
        }

        $project = Project::create([
            'customer_id' => $request->user()->id,
            'service_category_id' => $category->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'property_type' => $validated['property_type'] ?? 'home',
            'budget_type' => $budgetType,
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'budget_visible_to_providers' => $budgetType !== 'quote',
            'area_label' => $validated['location'],
            'status' => 'live',
            'is_public' => true,
        ]);

        return response()->json([
            'project' => [
                'id' => $project->id,
                'title' => $project->title,
            ],
        ], 201);
    }

    private function budgetLabel(Project $project): string
    {
        if ($project->budget_type === 'quote' || ! $project->budget_visible_to_providers) {
            return 'Request quotations';
        }

        if ($project->budget_type === 'fixed' && $project->budget_min) {
            return 'R '.number_format((float) $project->budget_min, 0).' fixed';
        }

        if ($project->budget_min || $project->budget_max) {
            $min = $project->budget_min ? number_format((float) $project->budget_min, 0) : '0';
            $max = $project->budget_max ? number_format((float) $project->budget_max, 0) : '?';

            return "R {$min} – R {$max}";
        }

        return 'Request quotations';
    }
}
