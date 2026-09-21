@extends('layouts.app')

@section('title', 'Conversation | WorxNet')

@section('content')
<div style="max-width:680px;margin:0 auto;">
    <a href="{{ route('provider.inbox.index') }}" class="muted" style="display:inline-block;margin-bottom:16px;text-decoration:none;">&larr; Back to inbox</a>

    <h1 style="margin-bottom:4px;">{{ $conversation->customer->name ?? 'Customer' }}</h1>
    <p class="muted" style="margin-bottom:24px;">Conversation about {{ class_basename($conversation->subject_type) }} #{{ $conversation->subject_id }}</p>

    <div style="display:grid;gap:12px;">
        @forelse ($conversation->messages as $message)
            @php($isMine = $message->sender_id === auth()->id())
            <div style="max-width:75%;{{ $isMine ? 'margin-left:auto;' : '' }}background:{{ $isMine ? 'var(--surface-alt)' : '#fff' }};border:1px solid var(--border);border-radius:var(--radius-md);padding:12px 16px;">
                <p style="margin:0;font-size:0.78rem;font-weight:700;color:var(--muted);">{{ $message->sender->name ?? 'Unknown' }}</p>
                <p style="margin:6px 0 0;">{{ $message->body }}</p>
                <p style="margin:6px 0 0;font-size:0.72rem;" class="muted">{{ $message->created_at->format('d M Y, H:i') }}</p>
            </div>
        @empty
            <div class="empty-state">No messages in this conversation yet.</div>
        @endforelse
    </div>
</div>
@endsection
