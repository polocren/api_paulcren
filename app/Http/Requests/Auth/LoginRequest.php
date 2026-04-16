<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Valide les données envoyées pour la connexion.
 */
class LoginRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie l'e-mail avant validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower((string) $this->input('email')),
        ]);
    }

    /**
     * Règles de validation de la connexion.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
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
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.string' => 'L’adresse e-mail doit être un texte.',
            'email.email' => 'L’adresse e-mail n’est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être un texte.',
        ];
    }
}
