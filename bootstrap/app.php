<?php

use App\Http\Middleware\EnsureJwtCookieCsrfIsValid;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // On déclare ici les alias des middlewares Spatie
        // pour pouvoir écrire par exemple "permission:users.viewAny" dans les routes.
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'jwt.cookie.csrf' => EnsureJwtCookieCsrfIsValid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié.',
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Action non autorisée.',
                ], 403);
            }
        });

        $exceptions->render(function (TokenExpiredException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Le token JWT a expiré.',
                ], 401);
            }
        });

        $exceptions->render(function (TokenInvalidException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Le token JWT est invalide.',
                ], 401);
            }
        });

        $exceptions->render(function (TokenBlacklistedException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Le token JWT a été invalidé.',
                ], 401);
            }
        });

        $exceptions->render(function (JWTException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Token JWT manquant ou illisible.',
                ], 401);
            }
        });
    })->create();
