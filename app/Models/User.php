<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    // JWT gère l'authentification API.
    // Spatie gère les rôles et permissions.
    use HasFactory, HasRoles, Notifiable;

    // On utilise le guard "web" pour rester cohérent avec Sanctum et Spatie.
    // Même pour une API token, Spatie a besoin d'un guard de référence.
    protected string $guard_name = 'web';

    /**
     * Définit les conversions automatiques de certains champs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            // "hashed" évite d'oublier de sécuriser le mot de passe avant sauvegarde.
            'password' => 'hashed',
        ];
    }

    /**
     * Identifiant qui sera stocké dans le claim "sub" du JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Claims personnalisés ajoutés au JWT.
     *
     * On reste volontairement simple pour éviter de figer des infos
     * qui pourraient changer après la création du token.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
