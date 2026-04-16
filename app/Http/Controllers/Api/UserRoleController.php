<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AssignRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

/**
 * Gère l'attribution et le retrait des rôles sur les utilisateurs.
 */
class UserRoleController extends Controller
{
    /**
     * Ajoute un rôle à un utilisateur.
     */
    public function store(AssignRoleRequest $request, User $user): JsonResponse
    {
        // assignRole ajoute le rôle s'il n'est pas déjà présent.
        $user->assignRole($request->validated('role'));

        return response()->json([
            'message' => 'Rôle attribué avec succès.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ]);
    }

    /**
     * Retire un rôle à un utilisateur.
     */
    public function destroy(User $user, Role $role): JsonResponse
    {
        // Ici on retire un rôle précis à l'utilisateur ciblé.
        $user->removeRole($role);

        return response()->json([
            'message' => 'Rôle retiré avec succès.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ]);
    }
}
