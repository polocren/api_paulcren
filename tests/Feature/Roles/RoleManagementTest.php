<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Vérifie la gestion des rôles.
 */
class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_un_admin_peut_lister_les_roles(): void
    {
        $response = $this->getJsonCommeAdmin('/api/roles');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Rôles récupérés avec succès.')
            ->assertJsonPath('data.roles.0.name', 'admin');
    }

    public function test_un_admin_peut_creer_un_role(): void
    {
        $response = $this->postJsonCommeAdmin('/api/roles', [
            'name' => 'manager',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Rôle créé avec succès.')
            ->assertJsonPath('data.role.name', 'manager');

        $this->assertDatabaseHas('roles', [
            'name' => 'manager',
            'guard_name' => 'web',
        ]);
    }

    public function test_un_admin_peut_attribuer_un_role_a_un_utilisateur(): void
    {
        $user = User::factory()->create();

        $response = $this->postJsonCommeAdmin("/api/users/{$user->id}/roles", [
            'role' => 'user',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Rôle attribué avec succès.')
            ->assertJsonPath('data.user.roles.0', 'user');
    }

    public function test_un_admin_peut_retirer_un_role_a_un_utilisateur(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $role = Role::query()->where('name', 'user')->firstOrFail();

        $response = $this->deleteJsonCommeAdmin("/api/users/{$user->id}/roles/{$role->id}");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Rôle retiré avec succès.')
            ->assertJsonCount(0, 'data.user.roles');
    }

    public function test_un_admin_peut_mettre_a_jour_les_permissions_d_un_role(): void
    {
        $role = Role::query()->where('name', 'user')->firstOrFail();

        $response = $this->putJsonCommeAdmin("/api/roles/{$role->id}/permissions", [
            'permissions' => [
                'permissions.viewAny',
                'users.viewAny',
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Permissions du rôle mises à jour avec succès.')
            ->assertJsonPath('data.role.name', 'user')
            ->assertJsonCount(2, 'data.role.permissions');
    }

    public function test_un_utilisateur_sans_permission_ne_peut_pas_gerer_les_roles(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/roles');

        $response->assertForbidden();
    }

    public function test_la_creation_d_un_role_verifie_l_unicite(): void
    {
        $response = $this->postJsonCommeAdmin('/api/roles', [
            'name' => 'admin',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    private function getJsonCommeAdmin(string $uri): \Illuminate\Testing\TestResponse
    {
        // On crée un admin de test avec un token pour simuler un vrai appel API.
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->getJson($uri);
    }

    private function postJsonCommeAdmin(string $uri, array $data): \Illuminate\Testing\TestResponse
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->postJson($uri, $data);
    }

    private function putJsonCommeAdmin(string $uri, array $data): \Illuminate\Testing\TestResponse
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->putJson($uri, $data);
    }

    private function deleteJsonCommeAdmin(string $uri): \Illuminate\Testing\TestResponse
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        return $this->withHeader('Authorization', 'Bearer '.$token)->deleteJson($uri);
    }
}
