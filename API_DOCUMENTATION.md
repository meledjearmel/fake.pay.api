# Documentation API - Système de Gestion de Factures

## 📋 Table des matières

1. [Authentification](#authentification)
2. [Endpoints disponibles](#endpoints-disponibles)
3. [Format des réponses](#format-des-réponses)
4. [Gestion des erreurs](#gestion-des-erreurs)

---

## 🔐 Authentification

### Système de vérification

Cette API utilise un système d'authentification simple basé sur l'email et le mot de passe hashé. **Chaque requête** doit inclure ces informations dans le body JSON.

### Format de l'authentification

Pour **chaque endpoint**, vous devez inclure dans le body JSON :

```json
{
  "email": "example@mail.fr",
  "password": "hash_du_mot_de_pass"
}
```

### Comment ça fonctionne

1. L'API vérifie que l'email et le password sont présents dans le body
2. L'API recherche l'utilisateur par email dans la base de données
3. L'API compare le hash du mot de passe fourni avec celui stocké
4. Si la vérification réussit, la requête est traitée
5. Si la vérification échoue, une erreur 401 est retournée

### Exemple d'erreur d'authentification

```json
{
  "error": "Utilisateur non trouvé"
}
```

ou

```json
{
  "error": "Mot de passe incorrect"
}
```

---

## 📡 Endpoints disponibles

### Companies (Entreprises)

#### 1. Liste des entreprises

**Endpoint :** `GET /api/companies`

**Description :** Récupère une liste paginée des entreprises avec leurs factures associées.

**Body JSON requis :**
```json
{
  "email": "example@mail.fr",
  "password": "hash_du_mot_de_pass"
}
```

**Paramètres de requête (optionnels) :**

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `search` | string | Recherche par nom d'entreprise (recherche partielle) | `Acme` |
| `sort_by` | string | Colonne de tri. Valeurs: `id`, `name`, `code`, `created_at`, `updated_at` | `name` |
| `sort_order` | string | Ordre de tri: `asc` (croissant) ou `desc` (décroissant) | `desc` |
| `per_page` | int | Nombre d'éléments par page (min: 1, max: 100) | `20` |

**Exemple de requête :**
```bash
GET /api/companies?search=Acme&sort_by=name&sort_order=desc&per_page=20
Body: {"email": "user@example.com", "password": "hash"}
```

**Réponse réussie (200) :**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Acme Corporation",
      "code": "ACM001",
      "billings": [...],
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

#### 2. Liste des factures d'une entreprise

**Endpoint :** `GET /api/companies/{company_id}/billings`

**Description :** Récupère toutes les factures d'une entreprise spécifique avec système de tri et filtres avancés.

**Body JSON requis :**
```json
{
  "email": "example@mail.fr",
  "password": "hash_du_mot_de_pass"
}
```

**Paramètres d'URL :**

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `company_id` | int | ID de l'entreprise | `1` |

**Paramètres de requête (optionnels) :**

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `status` | string | Filtrer par statut: `active`, `inactive` | `active` |
| `payment_state` | string | Filtrer par état de paiement: `pending`, `partial`, `paid` | `pending` |
| `type` | string | Filtrer par type: `icpe`, `lce`, `port` | `icpe` |
| `year` | int | Filtrer par année | `2024` |
| `search` | string | Recherche par code de facture (recherche partielle) | `BILL-2024` |
| `sort_by` | string | Colonne de tri. Valeurs: `id`, `code`, `amount`, `year`, `status`, `payment_state`, `type`, `created_at`, `updated_at` | `amount` |
| `sort_order` | string | Ordre de tri: `asc` ou `desc` | `desc` |
| `per_page` | int | Nombre d'éléments par page (min: 1, max: 100) | `20` |

**Exemple de requête :**
```bash
GET /api/companies/1/billings?status=active&payment_state=pending&sort_by=amount&sort_order=desc&per_page=20
Body: {"email": "user@example.com", "password": "hash"}
```

**Réponse réussie (200) :**
```json
{
  "company": {
    "id": 1,
    "name": "Acme Corporation",
    "code": "ACM001"
  },
  "billings": {
    "data": [
      {
        "id": 1,
        "code": "BILL-2024-0001",
        "amount": 1500.50,
        "year": 2024,
        "status": "active",
        "payment_state": "pending",
        "type": "icpe",
        "company": {...}
      }
    ],
    "links": {...},
    "meta": {...}
  }
}
```

---

### Billings (Factures)

#### 3. Liste des factures

**Endpoint :** `GET /api/billings`

**Description :** Récupère une liste paginée de toutes les factures avec leurs entreprises associées.

**Body JSON requis :**
```json
{
  "email": "example@mail.fr",
  "password": "hash_du_mot_de_pass"
}
```

**Paramètres de requête (optionnels) :**

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `company_id` | int | Filtrer par ID d'entreprise | `1` |
| `status` | string | Filtrer par statut: `active`, `inactive` | `active` |
| `payment_state` | string | Filtrer par état de paiement: `pending`, `partial`, `paid` | `pending` |
| `type` | string | Filtrer par type: `icpe`, `lce`, `port` | `icpe` |
| `year` | int | Filtrer par année | `2024` |
| `search` | string | Recherche par code de facture (recherche partielle) | `BILL-2024` |
| `sort_by` | string | Colonne de tri. Valeurs: `id`, `code`, `amount`, `year`, `status`, `payment_state`, `type`, `providence`, `is_registered`, `created_at`, `updated_at`, `edited_at`, `last_paid_at` | `amount` |
| `sort_order` | string | Ordre de tri: `asc` ou `desc` | `desc` |
| `per_page` | int | Nombre d'éléments par page (min: 1, max: 100) | `20` |

**Exemple de requête :**
```bash
GET /api/billings?company_id=1&status=active&payment_state=pending&sort_by=amount&sort_order=desc
Body: {"email": "user@example.com", "password": "hash"}
```

**Réponse réussie (200) :**
```json
{
  "data": [
    {
      "id": 1,
      "code": "BILL-2024-0001",
      "amount": 1500.50,
      "year": 2024,
      "status": "active",
      "payment_state": "pending",
      "type": "icpe",
      "company": {
        "id": 1,
        "name": "Acme Corporation",
        "code": "ACM001"
      }
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

#### 4. Afficher une facture

**Endpoint :** `GET /api/billings/{billing_id}`

**Description :** Récupère les détails complets d'une facture spécifique avec son entreprise associée.

**Body JSON requis :**
```json
{
  "email": "example@mail.fr",
  "password": "hash_du_mot_de_pass"
}
```

**Paramètres d'URL :**

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `billing_id` | int | ID de la facture | `1` |

**Exemple de requête :**
```bash
GET /api/billings/1
Body: {"email": "user@example.com", "password": "hash"}
```

**Réponse réussie (200) :**
```json
{
  "id": 1,
  "code": "BILL-2024-0001",
  "amount": 1500.50,
  "year": 2024,
  "company_id": 1,
  "status": "active",
  "is_registered": false,
  "providence": "Genuis",
  "type": "icpe",
  "payment_state": "pending",
  "edited_at": null,
  "last_paid_at": null,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "company": {
    "id": 1,
    "name": "Acme Corporation",
    "code": "ACM001"
  }
}
```

---

#### 5. Mettre à jour une facture

**Endpoint :** `PUT /api/billings/{billing_id}` ou `PATCH /api/billings/{billing_id}`

**Description :** Met à jour uniquement les champs modifiables d'une facture existante.

**⚠️ Important :** Seuls ces trois champs peuvent être modifiés :
- `is_registered` (boolean)
- `payment_state` (string: `pending`, `partial`, `paid`)
- `last_paid_at` (datetime ou null)

Les autres champs (code, amount, year, etc.) **ne peuvent pas** être modifiés via cet endpoint.

**Body JSON requis :**
```json
{
  "email": "example@mail.fr",
  "password": "hash_du_mot_de_pass",
  "is_registered": true,
  "payment_state": "paid",
  "last_paid_at": "2024-01-20 14:00:00"
}
```

**Paramètres d'URL :**

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `billing_id` | int | ID de la facture à mettre à jour | `1` |

**Paramètres du body (optionnels) :**

| Paramètre | Type | Description | Valeurs acceptées | Exemple |
|-----------|------|-------------|------------------|---------|
| `is_registered` | boolean | Indique si la facture est enregistrée | `true`, `false` | `true` |
| `payment_state` | string | État de paiement de la facture | `pending`, `partial`, `paid` | `paid` |
| `last_paid_at` | string | Date et heure du dernier paiement | Format datetime (YYYY-MM-DD HH:mm:ss) ou date ISO | `2024-01-20 14:00:00` |

**Exemple de requête :**
```bash
PUT /api/billings/1
Body: {
  "email": "user@example.com",
  "password": "hash",
  "is_registered": true,
  "payment_state": "paid",
  "last_paid_at": "2024-01-20 14:00:00"
}
```

**Réponse réussie (200) :**
```json
{
  "id": 1,
  "code": "BILL-2024-0001",
  "amount": 1500.50,
  "year": 2024,
  "company_id": 1,
  "status": "active",
  "is_registered": true,
  "providence": "Genuis",
  "type": "icpe",
  "payment_state": "paid",
  "edited_at": null,
  "last_paid_at": "2024-01-20T14:00:00.000000Z",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-20T14:00:00.000000Z",
  "company": {
    "id": 1,
    "name": "Acme Corporation",
    "code": "ACM001"
  }
}
```

---

## 📦 Format des réponses

### Réponse de liste paginée

Toutes les réponses de liste sont paginées avec la structure suivante :

```json
{
  "data": [...],
  "links": {
    "first": "http://example.com/api/endpoint?page=1",
    "last": "http://example.com/api/endpoint?page=10",
    "prev": null,
    "next": "http://example.com/api/endpoint?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "path": "http://example.com/api/endpoint",
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

### Réponse d'un élément unique

Les réponses pour un élément unique retournent directement l'objet :

```json
{
  "id": 1,
  "field1": "value1",
  "field2": "value2",
  ...
}
```

---

## ⚠️ Gestion des erreurs

### Codes de statut HTTP

| Code | Description |
|------|-------------|
| `200` | Succès - Requête traitée avec succès |
| `401` | Non autorisé - Échec de l'authentification |
| `404` | Non trouvé - Ressource introuvable |
| `422` | Erreur de validation - Données invalides |
| `500` | Erreur serveur - Erreur interne |

### Format des erreurs

**Erreur d'authentification (401) :**
```json
{
  "error": "Utilisateur non trouvé"
}
```

ou

```json
{
  "error": "Mot de passe incorrect"
}
```

**Erreur de validation (422) :**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Le champ field_name est requis."
    ]
  }
}
```

**Erreur 404 :**
```json
{
  "message": "No query results for model [App\\Models\\Billing] 1"
}
```

---

## 🔍 Système de tri

### Colonnes disponibles par endpoint

**Companies (index) :**
- `id`
- `name`
- `code`
- `created_at`
- `updated_at`

**Companies Billings :**
- `id`
- `code`
- `amount`
- `year`
- `status`
- `payment_state`
- `type`
- `created_at`
- `updated_at`

**Billings (index) :**
- `id`
- `code`
- `amount`
- `year`
- `status`
- `payment_state`
- `type`
- `providence`
- `is_registered`
- `created_at`
- `updated_at`
- `edited_at`
- `last_paid_at`

### Utilisation

Pour trier les résultats, utilisez les paramètres `sort_by` et `sort_order` :

```
?sort_by=amount&sort_order=desc
```

- `sort_by` : Nom de la colonne à trier
- `sort_order` : `asc` (croissant) ou `desc` (décroissant)

---

## 📝 Notes importantes

1. **Authentification obligatoire** : Tous les endpoints nécessitent l'authentification via email/password dans le body JSON.

2. **Mise à jour limitée** : L'endpoint de mise à jour des factures ne permet de modifier que `is_registered`, `payment_state` et `last_paid_at`.

3. **Pagination** : Toutes les listes sont paginées par défaut (15 éléments par page, maximum 100).

4. **Recherche** : Les paramètres de recherche utilisent une recherche partielle (LIKE) et sont insensibles à la casse.

5. **Format des dates** : Les dates doivent être au format `YYYY-MM-DD HH:mm:ss` ou format ISO 8601.

---

## 🚀 Accès à la documentation interactive

Une documentation interactive générée automatiquement par Scramble est disponible à :

```
http://localhost/docs/api
```

(Cette documentation n'est accessible qu'en environnement local)
