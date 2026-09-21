@extends('layouts.app')

@section('title', 'Log In | WorxNet')

@section('content')
<div class="auth-card">
    <h1>Log in to WorxNet</h1>
    <p class="muted">Access your account to manage your provider application.</p>

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <div class="field @error('email') has-error @enderror">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="field @error('password') has-error @enderror">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="field" style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" id="remember" name="remember" style="width:auto;">
            <label for="remember" style="margin:0;font-weight:600;">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>

    <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Apply as a Provider</a></p>
</div>
@endsection
