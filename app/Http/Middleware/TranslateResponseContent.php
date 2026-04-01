<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslateResponseContent
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $locale = app()->getLocale();
        $defaultLocale = config('localization.default_locale', 'ru');

        if ($locale === $defaultLocale) {
            return $response;
        }

        $dictionary = lang_path($locale . DIRECTORY_SEPARATOR . 'auto.php');
        if (! file_exists($dictionary)) {
            return $response;
        }

        /** @var array<string,string> $map */
        $map = include $dictionary;
        if (! is_array($map) || $map === []) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '') {
            return $response;
        }

        $response->setContent(strtr($content, $map));

        return $response;
    }
}

