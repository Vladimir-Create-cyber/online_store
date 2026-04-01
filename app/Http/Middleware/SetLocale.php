<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('localization.supported_locales', ['ru' => 'Русский']));
        $defaultLocale = config('localization.default_locale', config('app.locale', 'ru'));
        $cookieLocale = $request->cookie('locale');
        $sessionLocale = $request->hasSession() ? $request->session()->get('locale') : null;
        $locale = $cookieLocale ?: ($sessionLocale ?: $defaultLocale);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        // Синхронизируем сессию и текущую локаль для корректного отображения в UI.
        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }
        app()->setLocale($locale);

        return $next($request);
    }
}

