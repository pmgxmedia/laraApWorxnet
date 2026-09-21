<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\ProjectBid;
use App\Models\Review;
use Illuminate\Http\Request;

class ProviderInboxController extends Controller
{
    /**
     * Show the provider's inbox: unread conversations and bid status updates.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->provider_company_id;

        $conversations = collect();
        $bidUpdates = collect();

        if ($companyId) {
            $conversations = Conversation::where('provider_company_id', $companyId)
                ->with(['customer', 'subject', 'messages' => function ($query) {
                    $query->latest();
                }])
                ->orderByDesc('last_message_at')
                ->get()
                ->map(function (Conversation $conversation) use ($user) {
                    $conversation->unread_count = $conversation->messages
                        ->where('sender_id', '!=', $user->id)
                        ->whereNull('read_at')
                        ->count();
                    $conversation->latest_message = $conversation->messages->first();

                    return $conversation;
                });

            $bidUpdates = ProjectBid::where('provider_company_id', $companyId)
                ->whereIn('status', ProjectBid::NOTIFIABLE_STATUSES)
                ->with('project')
                ->orderByDesc('updated_at')
                ->get();
        }

        return view('provider.inbox.index', [
            'conversations' => $conversations,
            'bidUpdates' => $bidUpdates,
        ]);
    }

    /**
     * Show a single conversation's thread and mark its messages as read.
     */
    public function showConversation(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless($conversation->provider_company_id === $user->provider_company_id, 403);

        $conversation->load(['customer', 'subject', 'messages.sender']);

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('provider.inbox.conversation', [
            'conversation' => $conversation,
        ]);
    }

    /**
     * Show the provider's profile: account details, ratings and earnings analytics.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $company = $user->providerCompany;

        $stats = [
            'rating_average' => 0,
            'rating_count' => 0,
            'completed_jobs' => 0,
            'total_earnings' => 0,
            'pipeline_value' => 0,
            'active_bids' => 0,
            'awarded_bids' => 0,
        ];
        $recentReviews = collect();
        $ratingBreakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if ($company) {
            $publishedReviews = Review::where('provider_company_id', $company->id)
                ->where('status', 'published');

            $stats['rating_count'] = (clone $publishedReviews)->count();
            $stats['rating_average'] = $stats['rating_count']
                ? round((clone $publishedReviews)->avg('rating'), 2)
                : 0;

            foreach ($ratingBreakdown as $star => $_) {
                $ratingBreakdown[$star] = (clone $publishedReviews)->where('rating', $star)->count();
            }

            $recentReviews = (clone $publishedReviews)
                ->with('customer')
                ->latest()
                ->take(5)
                ->get();

            $stats['completed_jobs'] = Appointment::where('provider_company_id', $company->id)
                ->where('status', 'completed')
                ->count();

            $stats['total_earnings'] = (float) Appointment::where('provider_company_id', $company->id)
                ->where('status', 'completed')
                ->where('payment_status', 'confirmed_by_provider')
                ->sum('final_amount');

            $stats['pipeline_value'] = (float) ProjectBid::where('provider_company_id', $company->id)
                ->where('status', 'awarded')
                ->sum('amount');

            $stats['active_bids'] = ProjectBid::where('provider_company_id', $company->id)
                ->whereIn('status', ['submitted', 'shortlisted'])
                ->count();

            $stats['awarded_bids'] = ProjectBid::where('provider_company_id', $company->id)
                ->where('status', 'awarded')
                ->count();
        }

        return view('provider.profile.index', [
            'company' => $company,
            'stats' => $stats,
            'recentReviews' => $recentReviews,
            'ratingBreakdown' => $ratingBreakdown,
        ]);
    }

    /**
     * Mark a bid update as seen and return the provider to the inbox.
     */
    public function markBidSeen(Request $request, ProjectBid $bid)
    {
        $user = $request->user();

        abort_unless($bid->provider_company_id === $user->provider_company_id, 403);

        if (! $bid->seen_at) {
            $bid->update(['seen_at' => now()]);
        }

        return redirect()->route('provider.inbox.index');
    }
}
