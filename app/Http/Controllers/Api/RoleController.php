<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

/**
 * Gère les rôles du système RBAC.
 */
class RoleController extends Controller
{
    /**
     * Retourne la liste des rôles avec leurs permissions.
     */
    public function index(): JsonResponse
    {
        // On charge aussi les permissions pour avoir une vue complète d'un rôle.
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Rôles récupérés avec succès.',
            'data' => [
                'roles' => RoleResource::collection($roles),
            ],
        ]);
    }

    /**
     * Crée un nouveau rôle.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::query()->create([
            'name' => $request->validated('name'),
            // On garde le même guard que pour les utilisateurs authentifiés.
            // Comme ça, Sanctum et Spatie restent cohérents entre eux.
            'guard_name' => 'web',
        ]);

        return response()->json([
            'message' => 'Rôle créé avec succès.',
            'data' => [
                'role' => new RoleResource($role->load('permissions')),
            ],
        ], 201);
    }
}
