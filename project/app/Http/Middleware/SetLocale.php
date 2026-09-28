<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('languages.supported', ['en' => []]));
        $isStudent = auth()->check() && auth()->user()->isStudent();
        $locale = $isStudent ? session('locale', 'en') : 'en';

        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        $native = config('languages.native', ['en']);
        // Student pages use Google Translate as the single translation engine.
        // Keep their rendered source text in English so the widget can translate it.
        $appLocale = $isStudent ? 'en' : (in_array($locale, $native, true) ? $locale : 'en');
        app()->setLocale($appLocale);
        $langMeta = config('languages.supported.'.$locale, config('languages.supported.en'));

        view()->share([
            'appLocale' => $locale,
            'appDir' => in_array($locale, config('languages.rtl', []), true) ? 'rtl' : 'ltr',
            'appLanguages' => config('languages.supported'),
            'googleLang' => $langMeta['gt'] ?? 'en',
            'useGoogleTranslate' => $isStudent && ! in_array($locale, $native, true),
        ]);

        return $next($request);
    }
}
