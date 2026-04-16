<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\SyncRolePermissionsRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

/**
 * Gère le lien entre un rôle et ses permissions.
 */
class RolePermissionController extends Controller
{
    /**
     * Remplace les permissions d'un rôle par la liste reçue.
     */
    public function update(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        // syncPermissions remplace la liste existante par celle reçue.
        // C'est pratique pour garder l'état exact d'un rôle.
        $role->syncPermissions($request->validated('permissions'));

        return response()->json([
            'message' => 'Permissions du rôle mises à jour avec succès.',
            'data' => [
                'role' => new RoleResource($role->load('permissions')),
            ],
        ]);
    }
}
