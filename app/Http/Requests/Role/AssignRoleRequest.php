<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Valide l'attribution d'un rôle à un utilisateur.
 */
class AssignRoleRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie le rôle envoyé par le client.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => Str::lower(trim((string) $this->input('role'))),
        ]);
    }

    /**
     * Règles de validation pour attribuer un rôle.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'exists:roles,name'],
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
            'role.required' => 'Le rôle est obligatoire.',
            'role.string' => 'Le rôle doit être un texte.',
            'role.exists' => 'Le rôle demandé n’existe pas.',
        ];
    }
}
