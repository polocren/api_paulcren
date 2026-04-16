# Cas et scénarios de tests

## Objectif

Les tests vérifient :
- le bon fonctionnement fonctionnel de l'API
- le respect des codes HTTP
- le contrôle d'accès RBAC
- la sécurité de base autour de l'authentification JWT

## Tests d'authentification

- inscription valide retourne `201`
- inscription invalide retourne `422`
- connexion valide retourne `200`
- connexion invalide retourne `401`
- brute force sur login retourne `429`
- `/api/auth/me` avec Bearer JWT retourne `200`
- `/api/auth/me` avec cookie JWT retourne `200`
- `/api/auth/me` sans authentification retourne `401`
- logout invalide le JWT courant
- requête write avec cookie JWT sans CSRF retourne `403`
- requête write avec cookie JWT et CSRF valide retourne `204`

## Tests RBAC rôles

- un admin peut lister les rôles
- un admin peut créer un rôle
- un admin peut attribuer un rôle à un utilisateur
- un admin peut retirer un rôle à un utilisateur
- un admin peut synchroniser les permissions d'un rôle
- un utilisateur sans permission reçoit `403`
- la création d'un rôle déjà existant retourne `422`

## Tests RBAC permissions

- un admin peut lister les permissions
- un admin peut créer une permission
- un utilisateur sans permission reçoit `403`
- la création d'une permission déjà existante retourne `422`

## Tests gestion des utilisateurs

- un admin peut lister les utilisateurs
- un admin peut voir un utilisateur
- un admin peut créer un utilisateur
- un admin peut modifier un utilisateur
- un admin peut supprimer un utilisateur
- un utilisateur sans permission reçoit `403`
- données invalides en création retournent `422`
- unicité de l'e-mail en modification retourne `422`
- utilisateur inexistant retourne `404`

## Stratégie retenue

- tests fonctionnels HTTP avec `php artisan test`
- base recréée à chaque test avec `RefreshDatabase`
- seeders rejoués automatiquement pour disposer des rôles, permissions et de l'admin

## Commande d'exécution

```bash
php artisan test
```
