@extends('layouts.app')

@push('styles')
    @vite('resources/css/auth.css')
@endpush

@section('content')
    <div class="container auth-page">
        <h1 class="auth-title">{{ __('ui.login_to_system') }}</h1>

        <form action="{{ route('login.perform') }}" method="POST" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-with-icon">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <input type="email" name="email" id="email" required
                           class="form-control with-icon"
                           value="{{ old('email') }}"
                           placeholder="your@email.com">
                </div>
                @error('email')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('ui.password') }}</label>
                <div class="input-with-icon has-eye">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" name="password" id="password" required
                           class="form-control with-icon"
                           placeholder="••••••••">
                    <span class="password-toggle"
                          aria-label="{{ __('ui.password') }}"
                          role="button"
                          tabindex="0">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </span>
                </div>
                @error('password')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group flex items-center mb-6">
                <input type="checkbox" name="remember" id="remember" class="form-checkbox">
                <label for="remember" class="ml-2 text-slate-600">{{ __('ui.remember_me') }}</label>
            </div>

            <button type="submit" class="btn-auth">
                <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                {{ __('ui.login') }}
            </button>

            <div class="auth-links">
                <a href="#">{{ __('ui.forgot_password') }}</a>
                <a href="{{ route('register') }}">{{ __('ui.no_account_register') }}</a>
            </div>
        </form>
    </div>
@endsection
