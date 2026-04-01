<header>
    <nav class="header-nav">
        <div class="header-left">
            <a href="{{ route('home') }}">{{ __('ui.home') }}</a>
            <a href="{{ route('cart.index') }}">{{ __('ui.cart') }}</a>
        </div>

        <div class="search-form">
            <form
                action="{{ route('product.search') }}"
                method="GET"
                data-base-url="{{ route('products.index') }}"
            >
                <span class="search-icon" aria-label="{{ __('ui.find') }}">🔍</span>
                <input type="text" name="query" placeholder="{{ __('ui.search_products') }}">
                <button type="submit">{{ __('ui.find') }}</button>
            </form>
        </div>

        <div class="header-right">
            <div class="language-switcher">
                <button type="button" class="language-trigger" aria-label="{{ __('ui.language') }}">
                    <span class="language-globe" aria-hidden="true">🌐</span>
                    <span class="language-code">{{ strtoupper(app()->getLocale()) }}</span>
                </button>
                <div class="language-dropdown">
                    @foreach (config('localization.supported_locales', []) as $localeCode => $localeLabel)
                        <a href="{{ route('locale.switch', ['locale' => $localeCode]) }}" class="language-option {{ app()->getLocale() === $localeCode ? 'active' : '' }}">
                            {{ $localeLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            @guest
                <a href="{{ route('login') }}" class="auth-btn btn-login">{{ __('ui.login') }}</a>
                <a href="{{ route('register') }}" class="auth-btn btn-register">{{ __('ui.register') }}</a>
            @else
                <a href="{{ route('dashboard') }}" class="auth-btn btn-account">{{ __('ui.account') }}</a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="auth-btn btn-logout">{{ __('ui.logout') }}</button>
                </form>
            @endguest
        </div>
    </nav>
</header>
