@extends('layouts.app')

@section('title', 'Apply as a Provider — Create Account | WorxNet')

@section('content')
<div class="auth-card">
    <span class="eyebrow">Step 1 of 2</span>
    <h1>Create your WorxNet account</h1>
    <p class="muted">Register to start your provider application. It only takes a minute.</p>

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="field @error('name') has-error @enderror">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="field @error('email') has-error @enderror">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="field @error('phone') has-error @enderror">
            <label for="phone">Phone number</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="e.g. +27 82 000 0000">
        </div>

        <div class="field-row">
            <div class="field @error('password') has-error @enderror">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create Account &amp; Continue</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
</div>
@endsection
