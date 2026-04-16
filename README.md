# AAA API

API REST d'authentification et d'autorisation réalisée dans le cadre du projet de Qualité Logicielle et Tests, en transversalité avec le module de Secure Programming.

Le sujet d'origine prévoyait une implémentation en Node/Express. Ce projet a été réalisé en Laravel, avec validation préalable de ce choix.

## Objectif

Le projet consiste à développer un module AAA :
- `Authentication` : inscription, connexion, déconnexion, profil connecté
- `Authorization` : rôles, permissions, contrôle d'accès
- `Account management` : gestion des comptes utilisateurs

L'API a été pensée pour être réutilisable dans une application multi-service ou dans un contexte orienté API.

## Fonctionnalités réalisées

- inscription d'un utilisateur
- connexion avec JWT
- déconnexion
- route `/api/auth/me`
- CRUD utilisateurs
- gestion des rôles
- gestion des permissions
- attribution et retrait de rôles
- affectation de permissions à un rôle
- collection Postman
- tests fonctionnels automatisés
- environnement Docker / Docker Compose

## Choix techniques

- Framework : Laravel 13
- Authentification API : JWT avec `php-open-source-saver/jwt-auth`
- RBAC : `spatie/laravel-permission`
- Base de données : MySQL / MariaDB en Docker, SQLite possible en local
- Tests : PHPUnit
- Conteneurisation : Docker + Docker Compose

## Modes d'authentification

L'API supporte deux modes d'authentification :

### 1. Bearer Token JWT

Prévu pour :
- Postman
- client lourd
- consommation directe de l'API

Exemple :

```http
Authorization: Bearer <token_jwt>
```

### 2. Cookie JWT HttpOnly

Prévu pour :
- une application web

Le mode cookie repose sur :
- un cookie JWT `HttpOnly`
- un cookie `XSRF-TOKEN`
- un header `X-CSRF-TOKEN` pour les requêtes write

Ce choix permet de répondre à la consigne sur :
- l'utilisation d'un Bearer Token JWT
- l'utilisation d'un cookie HttpOnly pour les clients web
- la prise en compte du risque CSRF

## Sécurité mise en place

- mots de passe hachés automatiquement
- validation centralisée avec des `FormRequest`
- limitation anti brute force sur `/api/auth/login`
- gestion des rôles et permissions par RBAC
- JWT signé via `JWT_SECRET`
- cookie web `HttpOnly`
- contrôle CSRF sur les requêtes write en mode cookie
- aucune requête SQL brute dans le code métier
- logs applicatifs exploitables
- configuration via `.env`
- mode debug disponible

## Codes HTTP utilisés

Les principaux codes utilisés dans l'API sont :

- `200` : lecture ou mise à jour réussie
- `201` : création réussie
- `204` : suppression ou déconnexion réussie
- `401` : non authentifié / token invalide
- `403` : accès interdit / CSRF invalide
- `404` : ressource introuvable
- `422` : erreur de validation
- `429` : trop de tentatives de connexion
- `500` : erreur interne non prévue

## Architecture du projet

Le projet suit une structure Laravel simple et lisible :

- `app/Http/Controllers/Api` : logique des endpoints
- `app/Http/Requests` : validation des entrées
- `app/Http/Resources` : formatage des réponses JSON
- `app/Http/Middleware` : sécurité complémentaire
- `app/Models` : modèles Eloquent
- `database/migrations` : structure de base de données
- `database/seeders` : rôles, permissions et compte admin
- `routes/api.php` : routes REST de l'API
- `tests/Feature` : scénarios de tests fonctionnels
- `docker/` : configuration Docker
- `postman/` : collection et environnement Postman
- `docs/` : documents de cadrage et de tests

## Compte administrateur par défaut

Le seeder crée un compte administrateur de démonstration :

```text
email: admin@example.com
mot de passe: Admin123!
```

## Lancement du projet en local

Le projet peut être lancé localement avec SQLite :

```bash
cd aaa-api
php artisan serve
```

URL locale :

```text
http://127.0.0.1:8000
```

## Lancement avec Docker

### 1. Préparer l'environnement

```bash
cp .env.docker.example .env
```

### 2. Construire et démarrer les conteneurs

```bash
docker compose up -d --build
```

### 3. Générer les clés nécessaires

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret --force
```

### 4. Initialiser la base de données

```bash
docker compose exec app php artisan migrate --seed
```

### 5. Accès

API :

```text
http://localhost:8080
```

Base MySQL :

```text
localhost:3307
```

## Logs

Les logs sont disponibles :
- dans `storage/logs/laravel.log`
- dans la console grâce à `LOG_STACK=single,stderr`

## Tests

Les tests couvrent :
- l'authentification
- les permissions
- les rôles
- la gestion des utilisateurs
- le contrôle CSRF en mode cookie

Commande locale :

```bash
php artisan test
```

Commande Docker :

```bash
docker compose exec app php artisan test
```

État actuel :

```text
32 tests passés
```

## Documentation du projet

Les documents attendus pour le rendu sont disponibles dans le dossier `docs/` :

- [Cahier des charges fonctionnel](docs/cahier-des-charges-fonctionnel.md)
- [Cas et scénarios de tests](docs/scenarios-de-tests.md)

## Postman

Les fichiers Postman sont disponibles dans `postman/` :

- `postman/aaa-api.postman_collection.json`
- `postman/aaa-api.local.postman_environment.json`

Procédure conseillée :

1. Importer la collection et l'environnement
2. Sélectionner l'environnement local
3. Lancer `Login Admin`
4. Tester ensuite `Me`, `Users`, `Roles` et `Permissions`

Base URL locale :

```text
http://127.0.0.1:8000
```

Base URL Docker :

```text
http://localhost:8080
```

## Fichiers importants pour la lecture du projet

- `routes/api.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Middleware/EnsureJwtCookieCsrfIsValid.php`
- `app/Models/User.php`
- `database/seeders/RolePermissionSeeder.php`
- `tests/Feature/Auth/AuthenticationTest.php`

## Conclusion

Ce projet fournit une API AAA simple, documentée et testée, avec :
- authentification JWT
- contrôle d'accès RBAC
- prise en compte des bonnes pratiques de sécurité
- environnement de développement et d'exécution reproductible

Il est prêt pour une démonstration avec Postman, une exécution Docker et une présentation à l'oral.
