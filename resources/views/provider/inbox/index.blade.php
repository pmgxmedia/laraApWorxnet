@extends('layouts.app')

@section('title', 'Inbox | WorxNet')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <h1 style="margin-bottom:4px;">Inbox</h1>
    <p class="muted" style="margin-bottom:20px;">Messages and bid updates for your projects, all in one place.</p>

    <nav class="account-tabs" aria-label="Account sections">
        <a href="{{ route('provider.inbox.index') }}" class="is-active">Inbox</a>
        <a href="{{ route('provider.profile') }}">Profile</a>
    </nav>

    @if ($bidUpdates->count())
        <h2 style="font-size:1.05rem;margin-bottom:12px;">Bid updates</h2>
        <div style="display:grid;gap:10px;margin-bottom:32px;">
            @foreach ($bidUpdates as $bid)
                <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius-md);padding:16px 18px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <p style="margin:0;font-weight:700;">{{ $bid->project->title ?? 'Project #'.$bid->project_id }}</p>
                        <p class="muted" style="margin:2px 0 0;font-size:0.9rem;">
                            Your bid was
                            <strong>{{ str_replace('_', ' ', $bid->status) }}</strong>
                            &middot; R{{ number_format($bid->amount, 2) }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('provider.inbox.bid.seen', $bid) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="white-space:nowrap;">Mark seen</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <h2 style="font-size:1.05rem;margin-bottom:12px;">Conversations</h2>

    @if ($conversations->isEmpty())
        <div class="empty-state">You have no conversations yet.</div>
    @else
        <div style="display:grid;gap:10px;">
            @foreach ($conversations as $conversation)
                <a href="{{ route('provider.inbox.conversation', $conversation) }}"
                   style="display:flex;justify-content:space-between;align-items:center;gap:12px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-md);padding:16px 18px;text-decoration:none;color:inherit;">
                    <div>
                        <p style="margin:0;font-weight:700;">{{ $conversation->customer->name ?? 'Customer' }}</p>
                        <p class="muted" style="margin:2px 0 0;font-size:0.9rem;">
                            {{ Str::limit($conversation->latest_message->body ?? 'No messages yet', 70) }}
                        </p>
                    </div>
                    @if ($conversation->unread_count > 0)
                        <span class="inbox-badge" style="position:static;box-shadow:none;">{{ $conversation->unread_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
