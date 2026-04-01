<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('ui.admin_panel') }} | {{ config('app.name') }}</title>

        @vite(['resources/css/admin.css'])
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/solid.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/fontawesome.min.css">

        @stack('styles')

    </head>
    <body class="admin-body @if(config('app.debug')) debug @endif">
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <div class="admin-brand">
                    <i class="fas fa-cogs"></i>
                    <span>{{ __('ui.admin_panel_upper') }}</span>
                </div>

                <nav class="admin-menu">
                    <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>{{ __('ui.home') }}</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-bag"></i>
                        <span>{{ __('ui.products') }}</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-list-alt"></i>
                        <span>{{ __('ui.categories') }}</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>{{ __('ui.orders') }}</span>
                    </a>

                    <a href="{{ route('admin.shipping_methods.index') }}" class="menu-item {{ request()->routeIs('admin.shipping_methods.*') ? 'active' : '' }}">
                        <i class="fas fa-truck"></i>
                        <span>{{ __('ui.shipping_methods') }}</span>
                    </a>

                    <a href="{{ route('admin.reviews.index') }}" class="menu-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <i class="fas fa-star"></i>
                        <span>{{ __('ui.reviews') }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>{{ __('ui.users') }}</span>
                    </a>



                    <a href="#" class="menu-item disabled">
                        <i class="fas fa-chart-bar"></i>
                        <span>{{ __('ui.analytics') }}</span>
                    </a>
                    <a href="#" class="menu-item disabled">
                        <i class="fas fa-cog"></i>
                        <span>{{ __('ui.settings') }}</span>
                    </a>
                </nav>
            </aside>

            <div class="admin-content">
                <header class="admin-header">
                    <div class="admin-header-content">
                        <div class="admin-info">
                            <div class="admin-avatar">
                                @auth('admin')
                                    @if(Auth::guard('admin')->user()->avatar_url)
                                        <img src="{{ Auth::guard('admin')->user()->avatar_url }}"
                                             alt="{{ Auth::guard('admin')->user()->display_name }}"
                                             class="admin-avatar-img">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ mb_substr(Auth::guard('admin')->user()->display_name, 0, 1) }}
                                        </div>
                                    @endif
                                @endauth
                            </div>

                            <div class="admin-details">
                                @auth('admin')
                                    <span class="admin-name">{{ Auth::guard('admin')->user()->display_name }}</span>
                                    <span class="admin-role">
                                        {{ Auth::guard('admin')->user()->is_super_admin ? __('ui.super_admin') : __('ui.admin') }}
                                    </span>
                                @endauth
                            </div>
                        </div>

                        <div class="admin-language-switcher">
                            <button type="button" class="admin-language-trigger" aria-label="{{ __('ui.language') }}">
                                🌐 {{ strtoupper(app()->getLocale()) }}
                            </button>
                            <div class="admin-language-dropdown">
                                @foreach (config('localization.supported_locales', []) as $localeCode => $localeLabel)
                                    <a href="{{ route('locale.switch', ['locale' => $localeCode]) }}" class="admin-language-option {{ app()->getLocale() === $localeCode ? 'active' : '' }}">
                                        {{ $localeLabel }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="logout-btn" title="{{ __('ui.exit') }}">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="logout-text">{{ __('ui.exit') }}</span>
                            </button>
                        </form>
                    </div>
                </header>

                <main class="admin-main">
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')

        @stack('chart-scripts')
        @if(request()->routeIs('admin.dashboard'))
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @endif

        @vite(['resources/js/admin.js'])
    </body>
</html>
