<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie les cas principaux de l'authentification.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // On recharge les rôles, permissions et l'admin à chaque test.
        $this->seed();
    }

    public function test_un_utilisateur_peut_s_inscrire(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Alice Martin',
            'email' => 'alice@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Inscription réussie.')
            ->assertJsonPath('data.user.email', 'alice@example.com')
            ->assertJsonPath('data.user.roles.0', 'user')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'name' => 'Alice Martin',
            'is_active' => true,
        ]);
    }

    public function test_l_inscription_retourne_des_erreurs_si_les_donnees_sont_invalides(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_un_utilisateur_peut_se_connecter_avec_de_bons_identifiants(): void
    {
        $user = User::factory()->create([
            'email' => 'bob@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'bob@example.com',
            'password' => 'Password123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Connexion réussie.')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token_type', 'Bearer');
        $response->assertCookie(config('jwt.cookie_key_name', 'jwt_token'));
        $response->assertCookie('XSRF-TOKEN');
    }

    public function test_la_connexion_retourne_401_si_les_identifiants_sont_invalides(): void
    {
        User::factory()->create([
            'email' => 'bob@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'bob@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Identifiants invalides.');
    }

    public function test_la_connexion_est_limitee_apres_trop_de_tentatives(): void
    {
        // Les 5 premières tentatives passent jusqu'au contrôleur mais échouent en 401.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', [
                'email' => 'missing@example.com',
                'password' => 'WrongPassword123!',
            ])->assertUnauthorized();
        }

        // La 6e doit être bloquée par le rate limiter.
        $response = $this->postJson('/api/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response
            ->assertStatus(429)
            ->assertJsonPath('message', 'Trop de tentatives de connexion. Veuillez réessayer plus tard.');
    }

    public function test_un_utilisateur_connecte_peut_recuperer_son_profil(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_un_utilisateur_connecte_peut_recuperer_son_profil_avec_le_cookie_jwt(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->tokenById($user->id);

        $response = $this->withCredentials()
            ->withUnencryptedCookie(config('jwt.cookie_key_name', 'jwt_token'), $token)
            ->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_la_deconnexion_invalide_le_token_courant(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $response->assertNoContent();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_une_requete_write_authentifiee_par_cookie_sans_csrf_est_refusee(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->tokenById($user->id);

        $response = $this->withCredentials()
            ->withUnencryptedCookie(config('jwt.cookie_key_name', 'jwt_token'), $token)
            ->postJson('/api/auth/logout');

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'Jeton CSRF manquant ou invalide.');
    }

    public function test_une_requete_write_authentifiee_par_cookie_avec_csrf_valide_est_acceptee(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->tokenById($user->id);
        $csrf = 'csrf-token-de-test';

        $response = $this->withCredentials()
            ->withUnencryptedCookie(config('jwt.cookie_key_name', 'jwt_token'), $token)
            ->withUnencryptedCookie('XSRF-TOKEN', $csrf)
            ->withHeader('X-CSRF-TOKEN', $csrf)
            ->postJson('/api/auth/logout');

        $response->assertNoContent();
    }

    public function test_me_demande_une_authentification(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }
}
