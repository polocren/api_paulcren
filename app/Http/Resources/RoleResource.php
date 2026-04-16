<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formate un rôle pour éviter de répéter la même structure JSON.
 */
class RoleResource extends JsonResource
{
    /**
     * Formate un rôle pour la réponse JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Si la relation est déjà chargée, on évite une requête SQL en plus.
        $permissions = $this->relationLoaded('permissions')
            ? $this->permissions->pluck('name')->values()
            : $this->permissions()->pluck('name')->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $permissions,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
