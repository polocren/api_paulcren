<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège les requêtes mutantes quand l'auth se fait par cookie JWT.
 *
 * Le principe est simple :
 * - si le client utilise un Bearer token dans l'en-tête Authorization, on ne demande pas de CSRF
 * - si le client utilise le cookie JWT HttpOnly, on exige un header CSRF qui recopie le cookie XSRF-TOKEN
 */
class EnsureJwtCookieCsrfIsValid
{
    /**
     * Vérifie la présence et la validité du token CSRF.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || $request->isMethod('OPTIONS')) {
            return $next($request);
        }

        if ($request->bearerToken()) {
            return $next($request);
        }

        $csrfCookie = (string) $request->cookie('XSRF-TOKEN', '');
        $csrfHeader = (string) ($request->header('X-CSRF-TOKEN') ?? $request->header('X-XSRF-TOKEN') ?? '');

        if ($csrfCookie === '' || $csrfHeader === '' || ! hash_equals($csrfCookie, $csrfHeader)) {
            return new JsonResponse([
                'message' => 'Jeton CSRF manquant ou invalide.',
            ], 403);
        }

        return $next($request);
    }
}
