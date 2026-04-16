<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Les routes AAA seront ajoutées progressivement:
| - Authentification
| - Utilisateurs
| - Rôles
| - Permissions
|
*/

Route::prefix('auth')->group(function (): void {
    // Routes publiques d'authentification.
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware(['auth:api', 'jwt.cookie.csrf'])->group(function (): void {
        // Routes accessibles avec un JWT valide.
        // Le middleware CSRF ne s'active réellement que si l'auth passe par cookie.
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:api', 'jwt.cookie.csrf'])->group(function (): void {
    // CRUD utilisateurs protégé par permissions.
    Route::get('/users', [UserController::class, 'index'])->middleware('can:users.viewAny');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:users.view');
    Route::post('/users', [UserController::class, 'store'])->middleware('can:users.create');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:users.update');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('can:users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete');

    // Gestion des rôles et de leurs permissions.
    Route::get('/roles', [RoleController::class, 'index'])->middleware('can:roles.viewAny');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('can:roles.create');
    Route::put('/roles/{role}/permissions', [RolePermissionController::class, 'update'])
        ->middleware('can:roles.permissions.update');

    // Gestion des permissions disponibles dans l'application.
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('can:permissions.viewAny');
    Route::post('/permissions', [PermissionController::class, 'store'])->middleware('can:permissions.create');

    // Attribution ou retrait de rôle sur un utilisateur.
    Route::post('/users/{user}/roles', [UserRoleController::class, 'store'])->middleware('can:roles.assign');
    Route::delete('/users/{user}/roles/{role}', [UserRoleController::class, 'destroy'])->middleware('can:roles.assign');
});
