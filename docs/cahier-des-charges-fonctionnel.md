# Cahier des charges fonctionnel

## Objet

Le projet consiste à fournir une API REST AAA pour :
- gérer des comptes utilisateurs
- authentifier les utilisateurs avec JWT
- autoriser les accès par rôles et permissions
- appliquer un modèle RBAC simple et lisible

L'API est pensée pour être consommée par :
- un client lourd ou Postman via Bearer token JWT
- une application web via cookie JWT HttpOnly

## Mesures de sécurité retenues

- mots de passe hachés automatiquement par Laravel
- validation centralisée avec Form Requests
- limitation anti brute force sur la connexion
- JWT signé via `JWT_SECRET`
- cookie web en `HttpOnly`
- protection CSRF en mode cookie via `XSRF-TOKEN` + header `X-CSRF-TOKEN`
- aucun SQL brut dans le code métier
- journalisation des événements importants

## Règles HTTP

- `200` : lecture ou modification réussie
- `201` : ressource créée
- `204` : suppression ou logout réussi
- `401` : non authentifié ou JWT invalide
- `403` : action interdite ou CSRF invalide
- `404` : ressource introuvable
- `422` : validation invalide
- `429` : trop de tentatives de connexion
- `500` : erreur interne non prévue

## Authentification

### 1. Inscription

- Méthode : `POST`
- URL : `/api/auth/register`
- Auth requise : non
- Codes attendus : `201`, `422`

Entrée :

```json
{
  "name": "Alice Martin",
  "email": "alice@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

Sortie :

```json
{
  "message": "Inscription réussie.",
  "data": {
    "user": {
      "id": 2,
      "name": "Alice Martin",
      "email": "alice@example.com",
      "is_active": true,
      "email_verified_at": null,
      "roles": ["user"],
      "permissions": [],
      "created_at": "2026-04-16T12:00:00.000000Z",
      "updated_at": "2026-04-16T12:00:00.000000Z"
    },
    "token": "jwt...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### 2. Connexion

- Méthode : `POST`
- URL : `/api/auth/login`
- Auth requise : non
- Codes attendus : `200`, `401`, `422`, `429`

Entrée :

```json
{
  "email": "admin@example.com",
  "password": "Admin123!"
}
```

Sortie :

```json
{
  "message": "Connexion réussie.",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "is_active": true,
      "email_verified_at": null,
      "roles": ["admin"],
      "permissions": [
        "users.viewAny",
        "users.view",
        "users.create"
      ],
      "created_at": "2026-04-16T12:00:00.000000Z",
      "updated_at": "2026-04-16T12:00:00.000000Z"
    },
    "token": "jwt...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### 3. Profil connecté

- Méthode : `GET`
- URL : `/api/auth/me`
- Auth requise : oui
- Codes attendus : `200`, `401`

### 4. Déconnexion

- Méthode : `POST`
- URL : `/api/auth/logout`
- Auth requise : oui
- Codes attendus : `204`, `401`, `403`

## Gestion des utilisateurs

### 1. Lister les utilisateurs

- Méthode : `GET`
- URL : `/api/users`
- Permission : `users.viewAny`
- Codes attendus : `200`, `401`, `403`

### 2. Voir un utilisateur

- Méthode : `GET`
- URL : `/api/users/{user}`
- Permission : `users.view`
- Codes attendus : `200`, `401`, `403`, `404`

### 3. Créer un utilisateur

- Méthode : `POST`
- URL : `/api/users`
- Permission : `users.create`
- Codes attendus : `201`, `401`, `403`, `422`

Entrée :

```json
{
  "name": "Nina Dupont",
  "email": "nina@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "is_active": true
}
```

### 4. Modifier un utilisateur

- Méthode : `PUT` ou `PATCH`
- URL : `/api/users/{user}`
- Permission : `users.update`
- Codes attendus : `200`, `401`, `403`, `404`, `422`

### 5. Supprimer un utilisateur

- Méthode : `DELETE`
- URL : `/api/users/{user}`
- Permission : `users.delete`
- Codes attendus : `204`, `401`, `403`, `404`

## Gestion des rôles

### 1. Lister les rôles

- Méthode : `GET`
- URL : `/api/roles`
- Permission : `roles.viewAny`

### 2. Créer un rôle

- Méthode : `POST`
- URL : `/api/roles`
- Permission : `roles.create`

### 3. Attribuer un rôle à un utilisateur

- Méthode : `POST`
- URL : `/api/users/{user}/roles`
- Permission : `roles.assign`

Entrée :

```json
{
  "role": "manager"
}
```

### 4. Retirer un rôle à un utilisateur

- Méthode : `DELETE`
- URL : `/api/users/{user}/roles/{role}`
- Permission : `roles.assign`

## Gestion des permissions

### 1. Lister les permissions

- Méthode : `GET`
- URL : `/api/permissions`
- Permission : `permissions.viewAny`

### 2. Créer une permission

- Méthode : `POST`
- URL : `/api/permissions`
- Permission : `permissions.create`

Entrée :

```json
{
  "name": "reports.export"
}
```

### 3. Affecter des permissions à un rôle

- Méthode : `PUT`
- URL : `/api/roles/{role}/permissions`
- Permission : `roles.permissions.update`

Entrée :

```json
{
  "permissions": [
    "users.viewAny",
    "permissions.viewAny"
  ]
}
```

## Modes d'appel

### Mode Bearer Token

Header :

```text
Authorization: Bearer <jwt>
```

### Mode Cookie web

Cookies :

```text
jwt_token=<jwt>
XSRF-TOKEN=<token-csrf>
```

Header obligatoire sur les requêtes `POST`, `PUT`, `PATCH`, `DELETE` :

```text
X-CSRF-TOKEN: <valeur du cookie XSRF-TOKEN>
```
