<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Lance les seeders principaux du projet.
     */
    public function run(): void
    {
        $this->call([
            // On crée d'abord les permissions et rôles,
            // puis le compte admin qui dépend du rôle "admin".
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
