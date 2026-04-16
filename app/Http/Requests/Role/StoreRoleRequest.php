<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Valide la création d'un rôle.
 */
class StoreRoleRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie le nom du rôle avant validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::lower(trim((string) $this->input('name'))),
        ]);
    }

    /**
     * Règles de validation pour créer un rôle.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
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
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.string' => 'Le nom du rôle doit être un texte.',
            'name.max' => 'Le nom du rôle ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Ce rôle existe déjà.',
        ];
    }
}
