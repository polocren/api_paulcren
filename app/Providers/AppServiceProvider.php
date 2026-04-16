<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Configure ce qui doit être prêt au démarrage.
     */
    public function boot(): void
    {
        // On retire l'enveloppe "data" automatique de Laravel Resources.
        // Comme ça, on contrôle nous-mêmes la structure JSON dans les contrôleurs.
        JsonResource::withoutWrapping();

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            // La limite se base sur l'email + l'IP pour freiner le brute force.
            $key = sprintf('%s|%s', $email, $request->ip());

            return Limit::perMinute(5)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    // On retourne un JSON propre au lieu d'une réponse HTML par défaut.
                    return response()->json([
                        'message' => 'Trop de tentatives de connexion. Veuillez réessayer plus tard.',
                    ], 429, $headers);
                });
        });
    }
}
