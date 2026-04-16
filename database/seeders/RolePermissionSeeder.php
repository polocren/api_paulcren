<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Crée les rôles et permissions de base du projet.
     */
    public function run(): void
    {
        // On vide le cache Spatie pour repartir sur un état propre.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Liste minimale utile pour le projet scolaire.
        $permissions = [
            'users.viewAny',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.viewAny',
            'roles.create',
            'roles.assign',
            'roles.permissions.update',
            'permissions.viewAny',
            'permissions.create',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Deux rôles de base suffisent pour démarrer :
        // - admin : gère tout
        // - user : utilisateur standard
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        // L'admin récupère toutes les permissions de base.
        $adminRole->syncPermissions($permissions);

        // Le rôle user reste vide au départ.
        // Ses permissions pourront être ajoutées plus tard si besoin.
        $userRole->syncPermissions([]);
    }
}
