@auth
    @if (auth()->user()->isProvider())
        <a href="{{ route('provider.inbox.index') }}" class="inbox-icon-link" aria-label="Inbox notifications">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h15A1.5 1.5 0 0 1 21 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.5v-13Z"/>
                <path d="m3.5 6 8.03 6.43a.75.75 0 0 0 .94 0L20.5 6"/>
            </svg>
            @php($inboxCount = auth()->user()->inboxNotificationCount())
            @if ($inboxCount > 0)
                <span class="inbox-badge">{{ $inboxCount > 9 ? '9+' : $inboxCount }}</span>
            @endif
        </a>
    @endif
    <span class="user-greeting">Hi, {{ Str::before(auth()->user()->name, ' ') }}</span>
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-ghost">Log Out</button>
    </form>
@else
    <a href="{{ route('login') }}" class="btn btn-ghost">Log In</a>
    <a href="{{ $joinRoute }}" class="btn btn-ghost">{{ $joinLabel }}</a>
@endauth
