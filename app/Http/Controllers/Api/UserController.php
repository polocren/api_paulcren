<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gère le CRUD des utilisateurs.
 *
 * Les contrôles d'accès ne sont pas faits ici directement.
 * Ils sont appliqués dans les routes avec les permissions Spatie.
 */
class UserController extends Controller
{
    /**
     * Retourne la liste des utilisateurs.
     */
    public function index(): JsonResponse
    {
        // On garde un ordre simple pour l'affichage dans Postman et dans les tests.
        $users = User::query()
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Utilisateurs récupérés avec succès.',
            'data' => [
                'users' => UserResource::collection($users),
            ],
        ]);
    }

    /**
     * Retourne un utilisateur précis.
     */
    public function show(User $user): JsonResponse
    {
        // Le model binding Laravel transforme automatiquement {user} en objet User.
        return response()->json([
            'message' => 'Utilisateur récupéré avec succès.',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Crée un nouvel utilisateur depuis l'espace d'administration.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        // Le mot de passe sera hashé automatiquement grâce au cast "hashed" dans le modèle User.
        $user = User::create($request->validated());

        // On donne le rôle de base au nouvel utilisateur.
        $user->assignRole('user');

        /** @var \App\Models\User|null $actor */
        $actor = $request->user();

        Log::info('Création d’un utilisateur par un administrateur.', [
            'actor_id' => $actor?->id,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    /**
     * Met à jour un utilisateur.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // On met à jour uniquement les champs envoyés et validés.
        $user->update($request->validated());

        /** @var \App\Models\User|null $actor */
        $actor = $request->user();

        Log::info('Mise à jour d’un utilisateur.', [
            'actor_id' => $actor?->id,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ]);
    }

    /**
     * Supprime un utilisateur.
     */
    public function destroy(Request $request, User $user): Response
    {
        /** @var \App\Models\User|null $actor */
        $actor = $request->user();

        // On nettoie les liaisons RBAC avant suppression.
        // Les tables de Spatie ne sont pas toutes liées par clé étrangère au modèle User.
        $user->syncRoles([]);
        $user->syncPermissions([]);
        $user->delete();

        Log::info('Suppression d’un utilisateur.', [
            'actor_id' => $actor?->id,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        return response()->noContent();
    }
}
