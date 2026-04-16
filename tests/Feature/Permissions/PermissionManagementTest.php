<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie la gestion des permissions.
 */
class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_un_admin_peut_lister_les_permissions(): void
    {
        $response = $this->getJsonCommeAdmin('/api/permissions');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Permissions récupérées avec succès.')
            ->assertJsonPath('data.permissions.0.name', 'permissions.create');
    }

    public function test_un_admin_peut_creer_une_permission(): void
    {
        $response = $this->postJsonCommeAdmin('/api/permissions', [
            'name' => 'reports.export',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Permission créée avec succès.')
            ->assertJsonPath('data.permission.name', 'reports.export');

        $this->assertDatabaseHas('permissions', [
            'name' => 'reports.export',
            'guard_name' => 'web',
        ]);
    }

    public function test_un_utilisateur_sans_permission_ne_peut_pas_lister_les_permissions(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/permissions');

        $response->assertForbidden();
    }

    public function test_la_creation_d_une_permission_verifie_l_unicite(): void
    {
        $response = $this->postJsonCommeAdmin('/api/permissions', [
            'name' => 'users.viewAny',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    private function getJsonCommeAdmin(string $uri): \Illuminate\Testing\TestResponse
    {
        // On fabrique un admin temporaire pour chaque scénario.
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
}
