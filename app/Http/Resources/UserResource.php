<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formate toujours un utilisateur de la même façon dans les réponses JSON.
 */
class UserResource extends JsonResource
{
    /**
     * Formate un utilisateur pour la réponse JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Le but est d'avoir une sortie homogène partout dans l'API.
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            // On renvoie directement les noms des rôles pour simplifier la lecture côté client.
            'roles' => $this->getRoleNames()->values(),
            // Les permissions effectives incluent celles héritées via les rôles.
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
