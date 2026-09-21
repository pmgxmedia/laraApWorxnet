<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'WorxNet')</title>
    <meta name="theme-color" content="#1424a3">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="top">
    <div class="wrap header-wrap">
        <a href="{{ url('/') }}" class="brand">
            <span class="brand-text">Worx<em>Net</em></span>
        </a>

        <nav class="main-nav" id="mainNav" aria-label="Primary">
            <a href="{{ url('/') }}#find">Find a Specialist</a>
            <a href="{{ url('/') }}#categories">Services</a>
            <a href="{{ url('/') }}#projects">Post a Project</a>
            <a href="{{ url('/') }}#how">How it Works</a>
            <a href="{{ url('/') }}#pros">For Providers</a>
        </nav>

        <div class="header-actions">
            @include('layouts.partials.header-actions', ['joinRoute' => route('provider.apply'), 'joinLabel' => 'Apply as a Provider'])
            @if (auth()->check() && auth()->user()->isProvider())
                <a href="{{ url('/') }}#projects" class="btn btn-primary">Find Projects</a>
            @else
                <a href="{{ url('/') }}#find" class="btn btn-primary">Find Help Now</a>
            @endif
        </div>
    </div>
</header>

<main id="main">
    <div class="wrap" style="padding-top:48px; padding-bottom:80px;">
        @if (session('status'))
            <div class="alert-success" role="status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</main>

<footer class="site-footer">
    <div class="wrap footer-bottom footer-bottom-brand">
        <p>&copy; {{ date('Y') }} WorxNet. worxnet.co.za — Coming soon nationwide.</p>
    </div>
</footer>

</body>
</html>
