<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Valide les données pour modifier un utilisateur existant.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Autorise la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie les données avant validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower((string) $this->input('email')),
            ]);
        }
    }

    /**
     * Règles de validation pour mettre à jour un utilisateur.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var \App\Models\User|null $user */
        $user = $this->route('user');

        return [
            // "sometimes" permet d'accepter une mise à jour partielle.
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                // On ignore l'utilisateur courant pour autoriser son e-mail actuel.
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
            'is_active' => ['sometimes', 'boolean'],
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
            'name.string' => 'Le nom doit être un texte.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'email.string' => 'L’adresse e-mail doit être un texte.',
            'email.email' => 'L’adresse e-mail n’est pas valide.',
            'email.max' => 'L’adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.password.mixed' => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.password.symbols' => 'Le mot de passe doit contenir au moins un symbole.',
            'is_active.boolean' => 'Le champ is_active doit être vrai ou faux.',
        ];
    }
}
