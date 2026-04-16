<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

/**
 * Gère les permissions unitaires du système RBAC.
 */
class PermissionController extends Controller
{
    /**
     * Retourne la liste des permissions.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Permissions récupérées avec succès.',
            'data' => [
                'permissions' => PermissionResource::collection($permissions),
            ],
        ]);
    }

    /**
     * Crée une nouvelle permission.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::query()->create([
            'name' => $request->validated('name'),
            // Même guard que le reste de l'application pour éviter les conflits de permissions.
            'guard_name' => 'web',
        ]);

        return response()->json([
            'message' => 'Permission créée avec succès.',
            'data' => [
                'permission' => new PermissionResource($permission),
            ],
        ], 201);
    }
}
