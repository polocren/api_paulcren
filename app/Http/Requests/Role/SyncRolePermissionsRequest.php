<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

/**
 * Valide la liste des permissions à affecter à un rôle.
 */
class SyncRolePermissionsRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie chaque permission avant validation.
     */
    protected function prepareForValidation(): void
    {
        $permissions = Collection::make($this->input('permissions', []))
            ->map(fn (mixed $permission): string => trim((string) $permission))
            ->values()
            ->all();

        $this->merge([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Règles de validation pour synchroniser les permissions d'un rôle.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'distinct', 'exists:permissions,name'],
        ];
    }

    /**
     * Messages de validation en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permissions.required' => 'La liste des permissions est obligatoire.',
            'permissions.array' => 'Les permissions doivent être envoyées sous forme de tableau.',
            'permissions.*.string' => 'Chaque permission doit être un texte.',
            'permissions.*.distinct' => 'Une permission est présente plusieurs fois.',
            'permissions.*.exists' => 'Une des permissions demandées n’existe pas.',
        ];
    }
}
