<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProviderCompany;
use App\Models\Quote;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * JSON API for platform resources.
 *
 * Public read models are exposed without authentication; customer/provider
 * scoped data requires a Sanctum bearer token issued by POST /api/auth/token.
 */
class PlatformApiController extends Controller
{
    /** GET /api */
    public function index(): JsonResponse
    {
        return response()->json([
            'name' => 'WorxNet Platform API',
            'version' => '1.0',
            'auth' => [
                'POST /api/auth/token' => 'Exchange email + password for a Sanctum bearer token.',
            ],
            'public' => [
                'GET /api/services',
                'GET /api/providers',
                'GET /api/providers/{provider}',
                'GET /api/providers/{provider}/reviews',
                'GET /api/projects',
                'GET /api/projects/{project}',
            ],
            'authenticated' => [
                'GET /api/me',
                'GET /api/service-requests',
                'GET /api/service-requests/{serviceRequest}',
                'POST /api/service-requests',
                'GET /api/quotes',
                'GET /api/appointments',
                'GET /api/reviews',
                'GET /api/inbox',
                'POST /api/projects',
            ],
        ]);
    }

    /** POST /api/auth/token */
    public function token(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credentials)) {
            return response()->json(['message' => 'These credentials do not match our records.'], 422);
        }

        $user = Auth::guard('web')->user();
        $token = $user->createToken('platform-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    /** GET /api/me */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->userPayload($request->user('sanctum'))]);
    }

    /** GET /api/services */
    public function services(): JsonResponse
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'description' => $category->description,
                'supports_emergency' => (bool) $category->supports_emergency,
            ])->values();

        return response()->json([
            'data' => $categories,
            'count' => $categories->count(),
        ]);
    }

    /**
     * GET /api/providers
     *
     * Query params: service (slug or id), lat, lng, available=1,
     * q (free text area/name), featured=1.
     */
    public function providers(Request $request): JsonResponse
    {
        $hasCoords = $request->filled('lat') && $request->filled('lng');

        $query = ProviderCompany::query()->bookable();

        if ($request->filled('service')) {
            $query->offeringService((string) $request->query('service'));
        }

        if ($request->filled('q')) {
            $needle = trim((string) $request->query('q'));
            $query->where(function (Builder $search) use ($needle) {
                $search->where('display_name', 'like', "%{$needle}%")
                    ->orWhere('primary_area', 'like', "%{$needle}%")
                    ->orWhereHas('serviceAreas', function (Builder $area) use ($needle) {
                        $area->where('suburb', 'like', "%{$needle}%")
                            ->orWhere('municipality', 'like', "%{$needle}%")
                            ->orWhere('province', 'like', "%{$needle}%");
                    });
            });
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->boolean('available')) {
            $now = Carbon::now();
            $day = (int) $now->dayOfWeekIso;
            $time = $now->format('H:i:s');
            $query->whereHas('availabilities', function (Builder $availability) use ($day, $time) {
                $availability->where('day_of_week', $day)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time);
            });
        }

        if ($hasCoords) {
            $query->nearby((float) $request->query('lat'), (float) $request->query('lng'));
        }

        $providers = $query
            ->with(['serviceCategories', 'availabilities'])
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'data' => collect($providers->items())->map(fn (ProviderCompany $provider) => $this->providerPayload($provider, $hasCoords)),
            'meta' => $this->paginationMeta($providers),
        ]);
    }

    /** GET /api/providers/{provider} */
    public function providerDetail(Request $request, ProviderCompany $provider): JsonResponse
    {
        $provider = ProviderCompany::query()->bookable()->with(['serviceCategories', 'serviceAreas', 'availabilities'])->find($provider->id);

        if (! $provider) {
            return response()->json(['message' => 'Provider not found.'], 404);
        }

        $hasCoords = $request->filled('lat') && $request->filled('lng');
        $payload = $this->providerPayload($provider, $hasCoords);

        $payload['bio'] = $provider->bio;
        $payload['registered_name'] = $provider->display_name;
        $payload['service_areas'] = $provider->serviceAreas->map(fn ($area) => [
            'suburb' => $area->suburb,
            'municipality' => $area->municipality,
            'province' => $area->province,
        ])->values();
        $payload['travel_radius_km'] = $provider->travel_radius_km;
        $payload['completed_jobs'] = $provider->completed_jobs;
        $payload['response_minutes'] = $provider->response_minutes_avg;

        return response()->json(['data' => $payload]);
    }

    /** GET /api/providers/{provider}/reviews */
    public function providerReviews(ProviderCompany $provider): JsonResponse
    {
        $reviews = Review::query()
            ->where('provider_company_id', $provider->id)
            ->where('status', 'published')
            ->with('customer')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($reviews->items())->map(fn (Review $review) => [
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'customer_name' => $review->customer?->name,
                'submitted_at' => $review->created_at?->toIso8601String(),
            ])->values(),
            'meta' => $this->paginationMeta($reviews),
        ]);
    }

    /** GET /api/projects */
    public function projects(): JsonResponse
    {
        $projects = Project::query()
            ->where('is_public', true)
            ->whereIn('status', ['live', 'awarded', 'booked', 'in_progress', 'completed'])
            ->with(['customer', 'serviceCategory', 'bids' => fn ($query) => $query->orderBy('amount'), 'bids.providerCompany'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($projects->items())->map(fn (Project $project) => $this->projectPayload($project))->values(),
            'meta' => $this->paginationMeta($projects),
        ]);
    }

    /** GET /api/projects/{project} */
    public function projectDetail(Project $project): JsonResponse
    {
        if (! $project->is_public) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->load(['customer', 'serviceCategory', 'bids', 'bids.providerCompany']);

        return response()->json(['data' => $this->projectPayload($project)]);
    }

    /** POST /api/projects */
    public function storeProject(Request $request): JsonResponse
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
        $budgetVisible = $budgetType !== 'quote';

        $project = Project::create([
            'customer_id' => $request->user('sanctum')->id,
            'service_category_id' => $category->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'property_type' => $validated['property_type'] ?? 'home',
            'budget_type' => $budgetType,
            'budget_min' => $budgetType === 'quote' ? null : ($validated['budget_min'] ?? null),
            'budget_max' => $budgetType === 'quote' ? null : ($validated['budget_max'] ?? null),
            'budget_visible_to_providers' => $budgetVisible,
            'area_label' => $validated['location'],
            'status' => 'live',
            'is_public' => true,
        ]);

        return response()->json(['data' => $this->projectPayload($project->load(['serviceCategory']))], 201);
    }

    /**
     * GET /api/service-requests
     *
     * Customers see their own requests; provider accounts see requests
     * matched, quoted, or accepted by their company.
     */
    public function serviceRequests(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $query = ServiceRequest::query()->with(['serviceCategory', 'quotes', 'appointments']);

        if ($user->provider_company_id) {
            $query->where(function (Builder $company) use ($user) {
                $company->where('accepted_provider_company_id', $user->provider_company_id)
                    ->orWhereHas('matches', fn (Builder $match) => $match->where('provider_company_id', $user->provider_company_id))
                    ->orWhereHas('quotes', fn (Builder $quote) => $quote->where('provider_company_id', $user->provider_company_id));
            });
        } else {
            $query->where('customer_id', $user->id);
        }

        $requests = $query->latest()->paginate(20);

        return response()->json([
            'data' => collect($requests->items())->map(fn (ServiceRequest $serviceRequest) => $this->serviceRequestPayload($serviceRequest))->values(),
            'meta' => $this->paginationMeta($requests),
        ]);
    }

    /** GET /api/service-requests/{serviceRequest} */
    public function serviceRequestDetail(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $this->ownsServiceRequest($user, $serviceRequest)) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $serviceRequest->load(['serviceCategory', 'customer', 'quotes.providerCompany', 'appointments', 'media']);

        return response()->json(['data' => $this->serviceRequestPayload($serviceRequest, true)]);
    }

    /** POST /api/service-requests */
    public function storeServiceRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'exists:service_categories,slug'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'property_type' => ['nullable', 'in:home,complex,business,office,retail,estate'],
            'urgency' => ['nullable', 'in:24h,3days,week,planning'],
            'area_label' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'exact_address' => ['nullable', 'string', 'max:255'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $category = ServiceCategory::where('slug', $validated['category'])->firstOrFail();

        $serviceRequest = ServiceRequest::create([
            'customer_id' => $request->user('sanctum')->id,
            'service_category_id' => $category->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'property_type' => $validated['property_type'] ?? 'home',
            'urgency' => $validated['urgency'] ?? 'planning',
            'area_label' => $validated['area_label'],
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'exact_address' => $validated['exact_address'] ?? null,
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'status' => 'submitted',
        ]);

        return response()->json(['data' => $this->serviceRequestPayload($serviceRequest)], 201);
    }

    /**
     * GET /api/quotes
     *
     * Customers see quotes for their own requests; providers see quotes
     * submitted by their company.
     */
    public function quotes(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $query = Quote::query()->with(['serviceRequest.serviceCategory', 'providerCompany']);

        if ($user->provider_company_id) {
            $query->where('provider_company_id', $user->provider_company_id);
        } else {
            $query->whereHas('serviceRequest', fn (Builder $sr) => $sr->where('customer_id', $user->id));
        }

        $quotes = $query->latest()->paginate(20);

        return response()->json([
            'data' => collect($quotes->items())->map(fn (Quote $quote) => [
                'id' => $quote->id,
                'service_request_id' => $quote->service_request_id,
                'service_request_title' => $quote->serviceRequest?->title,
                'provider_name' => $quote->providerCompany?->display_name,
                'provider_verified' => $quote->providerCompany?->isVerified() ?? false,
                'amount' => (float) $quote->amount,
                'line_items' => $quote->line_items,
                'vat_included' => (bool) $quote->vat_included,
                'warranty_terms' => $quote->warranty_terms,
                'valid_until' => $quote->valid_until?->toDateString(),
                'status' => $quote->status,
                'submitted_at' => $quote->created_at?->toIso8601String(),
            ])->values(),
            'meta' => $this->paginationMeta($quotes),
        ]);
    }

    /** GET /api/appointments */
    public function appointments(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $query = Appointment::query()->with(['project', 'serviceRequest', 'providerCompany']);

        if ($user->provider_company_id) {
            $query->where('provider_company_id', $user->provider_company_id);
        } else {
            $query->where('customer_id', $user->id);
        }

        $appointments = $query->latest('scheduled_at')->paginate(20);

        return response()->json([
            'data' => collect($appointments->items())->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'title' => $appointment->project?->title ?? $appointment->serviceRequest?->title,
                'provider_name' => $appointment->providerCompany?->display_name,
                'scheduled_at' => $appointment->scheduled_at?->toIso8601String(),
                'status' => $appointment->status,
                'payment_status' => $appointment->payment_status,
                'payment_method' => $appointment->payment_method,
                'final_amount' => $appointment->final_amount === null ? null : (float) $appointment->final_amount,
            ])->values(),
            'meta' => $this->paginationMeta($appointments),
        ]);
    }

    /** GET /api/reviews */
    public function reviews(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $query = Review::query()->with(['customer', 'providerCompany']);

        if ($user->provider_company_id) {
            $query->where('provider_company_id', $user->provider_company_id);
        } else {
            $query->where('customer_id', $user->id);
        }

        $reviews = $query->latest()->paginate(20);

        return response()->json([
            'data' => collect($reviews->items())->map(fn (Review $review) => [
                'id' => $review->id,
                'provider_name' => $review->providerCompany?->display_name,
                'customer_name' => $review->customer?->name,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'status' => $review->status,
                'submitted_at' => $review->created_at?->toIso8601String(),
            ])->values(),
            'meta' => $this->paginationMeta($reviews),
        ]);
    }

    /**
     * GET /api/inbox
     *
     * Provider inbox: recent conversations for the provider's company plus
     * the unread message and unseen bid counts.
     */
    public function inbox(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user->provider_company_id) {
            return response()->json(['message' => 'Available to provider accounts only.'], 403);
        }

        $conversations = Conversation::query()
            ->where('provider_company_id', $user->provider_company_id)
            ->with(['customer', 'messages.sender'])
            ->orderByDesc('last_message_at')
            ->take(25)
            ->get();

        return response()->json([
            'data' => [
                'unread_messages' => $user->unreadMessageCount(),
                'unseen_bid_updates' => $user->unseenBidUpdateCount(),
                'conversations' => $conversations->map(fn (Conversation $conversation) => [
                    'id' => $conversation->id,
                    'customer_name' => $conversation->customer?->name,
                    'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                    'unread' => $conversation->messages->whereNull('read_at')->where('sender_id', '!=', $user->id)->count(),
                    'messages' => $conversation->messages->take(20)->map(fn (Message $message) => [
                        'sender' => $message->sender?->name,
                        'body' => $message->body,
                        'sent_at' => $message->created_at?->toIso8601String(),
                    ])->values(),
                ])->values(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Shared payload builders
    |--------------------------------------------------------------------------
    */

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'status' => $user->status,
            'provider_company_id' => $user->provider_company_id,
            'is_admin' => $user->isAdmin(),
            'is_provider' => $user->isProvider(),
        ];
    }

    private function providerPayload(ProviderCompany $provider, bool $hasCoords): array
    {
        $distanceKm = $hasCoords ? round((float) ($provider->distance_km ?? 0), 1) : null;

        return [
            'id' => $provider->id,
            'name' => $provider->display_name,
            'area' => $provider->primary_area,
            'rating' => round((float) $provider->rating_average, 1),
            'reviews' => $provider->rating_count,
            'distance_km' => $distanceKm,
            'available_now' => $provider->isAvailableNow(),
            'verified' => $provider->isVerified(),
            'featured' => (bool) $provider->is_featured,
            'emergency_ready' => (bool) $provider->emergency_available,
            'services' => $provider->serviceCategories->pluck('slug'),
        ];
    }

    private function projectPayload(Project $project): array
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'category' => $project->serviceCategory?->slug,
            'category_label' => $project->serviceCategory?->name,
            'location' => $project->area_label,
            'status' => $project->status,
            'posted_at' => $project->created_at?->toIso8601String(),
            'customer_first_name' => Str::before($project->customer?->name ?? 'Customer', ' '),
            'bids_count' => $project->bids->count(),
            'bids' => $project->bids->map(fn ($bid) => [
                'id' => $bid->id,
                'provider_name' => $bid->providerCompany?->display_name ?? 'Provider',
                'provider_rating' => $bid->providerCompany ? round((float) $bid->providerCompany->rating_average, 1) : null,
                'provider_verified' => $bid->providerCompany?->isVerified() ?? false,
                'amount' => (float) $bid->amount,
                'scope' => $bid->scope_included,
                'warranty' => $bid->warranty_terms,
                'submitted_at' => $bid->created_at?->toIso8601String(),
            ])->values(),
        ];
    }

    private function serviceRequestPayload(ServiceRequest $serviceRequest, bool $full = false): array
    {
        $payload = [
            'id' => $serviceRequest->id,
            'title' => $serviceRequest->title,
            'description' => $serviceRequest->description,
            'category' => $serviceRequest->serviceCategory?->slug,
            'category_label' => $serviceRequest->serviceCategory?->name,
            'urgency' => $serviceRequest->urgency,
            'area_label' => $serviceRequest->area_label,
            'status' => $serviceRequest->status,
            'budget_min' => $serviceRequest->budget_min === null ? null : (float) $serviceRequest->budget_min,
            'budget_max' => $serviceRequest->budget_max === null ? null : (float) $serviceRequest->budget_max,
            'submitted_at' => $serviceRequest->created_at?->toIso8601String(),
            'quotes_count' => $serviceRequest->quotes->count(),
            'appointments_count' => $serviceRequest->appointments->count(),
        ];

        if ($full) {
            $payload['quotes'] = $serviceRequest->quotes->map(fn (Quote $quote) => [
                'id' => $quote->id,
                'provider_name' => $quote->providerCompany?->display_name,
                'amount' => (float) $quote->amount,
                'status' => $quote->status,
                'submitted_at' => $quote->created_at?->toIso8601String(),
            ])->values();
            $payload['appointments'] = $serviceRequest->appointments->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'scheduled_at' => $appointment->scheduled_at?->toIso8601String(),
                'status' => $appointment->status,
            ])->values();
        }

        return $payload;
    }

    private function ownsServiceRequest(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($serviceRequest->customer_id === $user->id) {
            return true;
        }

        return $user->provider_company_id !== null
            && ($serviceRequest->accepted_provider_company_id === $user->provider_company_id
                || $serviceRequest->matches->contains('provider_company_id', $user->provider_company_id)
                || $serviceRequest->quotes->contains('provider_company_id', $user->provider_company_id));
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }
}