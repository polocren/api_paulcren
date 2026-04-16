<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie le CRUD utilisateurs et les accès associés.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // La base repart propre pour chaque test.
        $this->seed();
    }

    public function test_un_admin_peut_lister_les_utilisateurs(): void
    {
        User::factory()->count(2)->create();

        $response = $this->getJsonCommeAdmin('/api/users');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Utilisateurs récupérés avec succès.')
            ->assertJsonCount(3, 'data.users');
    }

    public function test_un_admin_peut_voir_un_utilisateur(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
        ]);

        $response = $this->getJsonCommeAdmin("/api/users/{$user->id}");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Utilisateur récupéré avec succès.')
            ->assertJsonPath('data.user.email', 'student@example.com');
    }

    public function test_un_admin_peut_creer_un_utilisateur(): void
    {
        $response = $this->postJsonCommeAdmin('/api/users', [
            'name' => 'Nina Dupont',
            'email' => 'nina@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Utilisateur créé avec succès.')
            ->assertJsonPath('data.user.email', 'nina@example.com')
            ->assertJsonPath('data.user.roles.0', 'user');

        $this->assertDatabaseHas('users', [
            'name' => 'Nina Dupont',
            'email' => 'nina@example.com',
            'is_active' => true,
        ]);
    }

    public function test_un_admin_peut_mettre_a_jour_un_utilisateur(): void
    {
        $user = User::factory()->create([
            'name' => 'Ancien Nom',
            'email' => 'ancien@example.com',
            'is_active' => true,
        ]);

        $response = $this->putJsonCommeAdmin("/api/users/{$user->id}", [
            'name' => 'Nouveau Nom',
            'email' => 'nouveau@example.com',
            'is_active' => false,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Utilisateur mis à jour avec succès.')
            ->assertJsonPath('data.user.name', 'Nouveau Nom')
            ->assertJsonPath('data.user.email', 'nouveau@example.com')
            ->assertJsonPath('data.user.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nouveau Nom',
            'email' => 'nouveau@example.com',
            'is_active' => false,
        ]);
    }

    public function test_un_admin_peut_supprimer_un_utilisateur(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJsonCommeAdmin("/api/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_un_utilisateur_sans_permission_ne_peut_pas_lister_les_utilisateurs(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/users');

        $response->assertForbidden();
    }

    public function test_la_creation_d_un_utilisateur_retourne_422_si_les_donnees_sont_invalides(): void
    {
        $response = $this->postJsonCommeAdmin('/api/users', [
            'name' => '',
            'email' => 'email-invalide',
            'password' => 'court',
            'password_confirmation' => 'different',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_la_mise_a_jour_verifie_que_l_email_reste_unique(): void
    {
        $firstUser = User::factory()->create([
            'email' => 'first@example.com',
        ]);

        $secondUser = User::factory()->create([
            'email' => 'second@example.com',
        ]);

        $response = $this->putJsonCommeAdmin("/api/users/{$secondUser->id}", [
            'email' => 'first@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_voir_un_utilisateur_inexistant_retourne_404(): void
    {
        $response = $this->getJsonCommeAdmin('/api/users/999999');

        $response->assertNotFound();
    }

    private function getJsonCommeAdmin(string $uri): \Illuminate\Testing\TestResponse
    {
        // Ici on réutilise l'admin seedé pour rester proche du vrai projet.
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->getJson($uri);
    }

    private function postJsonCommeAdmin(string $uri, array $data): \Illuminate\Testing\TestResponse
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->postJson($uri, $data);
    }

    private function putJsonCommeAdmin(string $uri, array $data): \Illuminate\Testing\TestResponse
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->putJson($uri, $data);
    }

    private function deleteJsonCommeAdmin(string $uri): \Illuminate\Testing\TestResponse
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->deleteJson($uri);
    }
}
