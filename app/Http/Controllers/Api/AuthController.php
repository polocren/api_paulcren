<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gère l'authentification de base de l'API.
 *
 * Ici, on couvre les besoins AAA côté auth :
 * - inscription
 * - connexion
 * - déconnexion
 * - récupération du profil connecté
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly CookieFactory $cookieFactory,
    ) {
    }

    /**
     * Inscrit un nouvel utilisateur puis crée son JWT.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Les données sont déjà validées dans RegisterRequest.
        $user = User::create($request->validated());

        // Chaque nouvel inscrit reçoit le rôle de base.
        $user->assignRole('user');

        // On génère un JWT utilisable en Bearer token et aussi en cookie HttpOnly.
        $token = $this->jwtGuard()->login($user);

        Log::info('Inscription utilisateur réussie.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return $this->authenticatedResponse($request, $user, $token, 'Inscription réussie.', 201);
    }

    /**
     * Vérifie les identifiants puis crée un JWT.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // On récupère l'utilisateur par son e-mail pour vérifier ensuite son mot de passe.
        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        // On refuse la connexion si l'utilisateur n'existe pas, est inactif ou si le mot de passe est faux.
        if (! $user || ! $user->is_active || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning('Tentative de connexion invalide.', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Identifiants invalides.',
            ], 401);
        }

        // Si tout est bon, on ouvre une session API via un JWT.
        $token = $this->jwtGuard()->login($user);

        Log::info('Connexion utilisateur réussie.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return $this->authenticatedResponse($request, $user, $token, 'Connexion réussie.');
    }

    /**
     * Retourne l'utilisateur connecté.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $this->jwtGuard()->user();

        // Cette route est pratique pour vérifier que le token envoyé est bien valide.
        return response()->json([
            'message' => 'Utilisateur connecté récupéré avec succès.',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Déconnecte l'utilisateur en invalidant le JWT courant.
     */
    public function logout(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $this->jwtGuard()->user();

        // Le package place le JWT dans une blacklist pour empêcher sa réutilisation.
        $this->jwtGuard()->logout();

        Log::info('Déconnexion utilisateur réussie.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->noContent()
            ->withCookie($this->forgetCookie($this->jwtCookieName()))
            ->withCookie($this->forgetCookie('XSRF-TOKEN'));
    }

    /**
     * Construit la réponse standard après authentification.
     */
    private function authenticatedResponse(
        Request $request,
        User $user,
        string $token,
        string $message,
        int $status = 200,
    ): JsonResponse {
        $response = response()->json([
            'message' => $message,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $this->jwtGuard()->factory()->getTTL() * 60,
            ],
        ], $status);

        return $response
            ->withCookie($this->authCookie($token))
            ->withCookie($this->xsrfCookie());
    }

    /**
     * Cookie HttpOnly qui porte le JWT pour un client web.
     */
    private function authCookie(string $token): Cookie
    {
        return $this->cookieFactory->make(
            $this->jwtCookieName(),
            $token,
            $this->jwtTtl(),
            '/',
            $this->jwtCookieDomain(),
            $this->jwtCookieSecure(),
            true,
            false,
            $this->jwtCookieSameSite(),
        );
    }

    /**
     * Cookie lisible par le navigateur pour la protection CSRF.
     */
    private function xsrfCookie(): Cookie
    {
        return $this->cookieFactory->make(
            'XSRF-TOKEN',
            Str::random(40),
            $this->jwtTtl(),
            '/',
            $this->jwtCookieDomain(),
            $this->jwtCookieSecure(),
            false,
            false,
            $this->jwtCookieSameSite(),
        );
    }

    /**
     * Crée un cookie expiré pour le logout.
     */
    private function forgetCookie(string $name): Cookie
    {
        return $this->cookieFactory->forget(
            $name,
            '/',
            $this->jwtCookieDomain(),
        );
    }

    private function jwtTtl(): int
    {
        return (int) config('jwt.ttl', 60);
    }

    private function jwtCookieName(): string
    {
        return (string) config('jwt.cookie_key_name', 'jwt_token');
    }

    private function jwtCookieSameSite(): string
    {
        return (string) config('jwt.cookie_same_site', 'strict');
    }

    private function jwtCookieSecure(): bool
    {
        return (bool) config('jwt.cookie_secure', false);
    }

    private function jwtCookieDomain(): ?string
    {
        $domain = config('jwt.cookie_domain');

        return is_string($domain) && $domain !== '' ? $domain : null;
    }

    /**
     * Retourne explicitement le guard JWT pour éviter les erreurs de typage dans l'IDE.
     */
    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }
}
