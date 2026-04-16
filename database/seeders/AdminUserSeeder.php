<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Crée le compte administrateur de base.
     */
    public function run(): void
    {
        // On crée un admin de démonstration pour tester rapidement avec Postman.
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'Admin123!',
                'is_active' => true,
            ]
        );

        // On s'assure que le compte admin possède toujours le rôle admin.
        $admin->syncRoles(['admin']);
    }
}
