<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide la création d'une permission.
 */
class StorePermissionRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie le nom de la permission.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    /**
     * Règles de validation pour créer une permission.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
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
            'name.required' => 'Le nom de la permission est obligatoire.',
            'name.string' => 'Le nom de la permission doit être un texte.',
            'name.max' => 'Le nom de la permission ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Cette permission existe déjà.',
        ];
    }
}
