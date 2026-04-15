<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestAuthLocale
{
    /**
     * Utilise une locale dédiée pour l’authentification invité (ex. login),
     * afin que les messages de validation / auth restent lisibles même si APP_LOCALE=en.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.guest_auth_locale');

        if (is_string($locale) && $locale !== '') {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
