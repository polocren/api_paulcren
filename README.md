# AAA API

Projet scolaire AAA réalisé avec Laravel.

L'API couvre :
- l'authentification JWT
- l'autorisation RBAC
- la gestion des utilisateurs
- des tests fonctionnels
- un environnement Docker

## Stack

- Laravel 13
- JWT avec `php-open-source-saver/jwt-auth`
- `spatie/laravel-permission`
- PHPUnit
- MySQL / MariaDB
- Docker / Docker Compose

## Modes d'authentification

Deux modes sont disponibles :
- client lourd / Postman : Bearer token JWT dans l'en-tête `Authorization`
- application web : cookie JWT HttpOnly + cookie `XSRF-TOKEN` + header `X-CSRF-TOKEN`

## Démarrage local

```bash
cd aaa-api
php artisan serve
```

API locale :

```text
http://127.0.0.1:8000
```

## Démarrage Docker

```bash
cp .env.docker.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret --force
docker compose exec app php artisan migrate --seed
```

API Docker :

```text
http://localhost:8080
```

Base MySQL Docker :

```text
localhost:3307
```

## Compte admin

```text
email: admin@example.com
mot de passe: Admin123!
```

## Logs

Les logs sont écrits :
- dans `storage/logs/laravel.log`
- dans la console grâce à `LOG_STACK=single,stderr`

## Tests

Local :

```bash
php artisan test
```

Docker :

```bash
docker compose exec app php artisan test
```

## Documentation

Les livrables écrits sont dans :
- [docs/cahier-des-charges-fonctionnel.md](/Users/paul/Code/TDD/aaa-api/docs/cahier-des-charges-fonctionnel.md:1)
- [docs/scenarios-de-tests.md](/Users/paul/Code/TDD/aaa-api/docs/scenarios-de-tests.md:1)

## Postman

À importer :
- `postman/aaa-api.postman_collection.json`
- `postman/aaa-api.local.postman_environment.json`

Pour Docker, mets `base_url` sur :

```text
http://localhost:8080
```
