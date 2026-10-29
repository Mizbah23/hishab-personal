<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/hishab-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-decoration"></div>
    <main class="login-card">
        <div class="login-brand"><img class="brand-logo" src="{{ asset('images/hishab-logo.png') }}" alt="Hishab logo"><strong>Hishab</strong></div>
        <p class="eyebrow">Personal accounts</p>
        <h1>Welcome back</h1>
        <p class="login-copy">Sign in to manage your personal accounts.</p>

        <form method="POST" action="{{ route('login.store') }}" class="login-form">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email') <span class="field-error">{{ $message }}</span> @enderror

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            @error('password') <span class="field-error">{{ $message }}</span> @enderror

            <label class="checkbox-label"><input type="checkbox" name="remember"> Remember me</label>
            <button class="primary-button" type="submit">Sign in <span>→</span></button>
        </form>
    </main>
</body>
</html>
