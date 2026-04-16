<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Valide les données envoyées pour l'inscription.
 *
 * L'intérêt d'un FormRequest est de sortir la validation du contrôleur
 * pour garder un code plus clair.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie quelques champs avant la validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // On met l'email en minuscules pour éviter les doublons avec des majuscules.
            'email' => Str::lower((string) $this->input('email')),
        ]);
    }

    /**
     * Règles de validation de l'inscription.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                // On impose un mot de passe un peu solide pour le sujet sécurité.
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
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
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être un texte.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.string' => 'L’adresse e-mail doit être un texte.',
            'email.email' => 'L’adresse e-mail n’est pas valide.',
            'email.max' => 'L’adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.password.mixed' => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.password.symbols' => 'Le mot de passe doit contenir au moins un symbole.',
        ];
    }
}
