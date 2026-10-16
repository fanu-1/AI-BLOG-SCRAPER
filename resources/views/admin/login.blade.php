@extends('layouts.site')

@section('title', 'Admin Login')

@section('content')
<section class="section">
    <div class="container" style="max-width: 560px;">
        <div class="panel">
            <h1 style="margin-top: 0;">Admin Login</h1>
            <p class="muted">Sign in to manage sources, AI providers, and publishing workflows.</p>

            @if ($errors->any())
                <div class="alert" style="background: rgba(239,68,68,.12); border-color: rgba(248,113,113,.18);">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <label>
                    Email
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" required>
                </label>
                <label style="display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="remember" value="1" style="width:auto;">
                    Remember me
                </label>
                <button class="btn btn-primary" type="submit">Login</button>
            </form>
            <p class="small" style="margin-top:14px;">Default seed: <code>admin@example.com</code> / <code>password</code></p>
        </div>
    </div>
</section>
@endsection
