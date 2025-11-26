# 📱 APIs Mobile - Résumé Complet

## ✅ Endpoints Créés

### 🔐 Authentification (`/api/auth`)
- ✅ `POST /auth/login` - Connexion
- ✅ `POST /auth/register` - Inscription
- ✅ `POST /auth/logout` - Déconnexion (protégé)

### 👤 Utilisateur (`/api/user`)
- ✅ `GET /user/profile` - Profil utilisateur (protégé)
- ✅ `PUT /user/profile` - Mise à jour profil (protégé)
- ✅ `POST /user/photo` - Upload photo (protégé)

### 📂 Catégories (`/api/categories`)
- ✅ `GET /categories` - Liste des catégories actives
- ✅ `GET /categories/{id}` - Détails d'une catégorie
- ✅ `GET /sous-categories` - Liste des sous-catégories
- ✅ `GET /categories/{id}/sous-categories` - Sous-catégories d'une catégorie

### 👷 Prestataires (`/api/prestataires`)
- ✅ `GET /prestataires` - Liste des prestataires (avec filtres)
- ✅ `GET /prestataires/search` - Recherche avancée
- ✅ `GET /prestataires/{id}` - Détails d'un prestataire

**Filtres disponibles pour la recherche :**
- `keyword` - Recherche par nom ou métier
- `categorie_id` - Filtrer par catégorie
- `latitude` / `longitude` - Filtrer par distance
- `max_distance` - Distance maximale (km, défaut: 50)
- `min_rating` - Note minimale
- `min_price` / `max_price` - Fourchette de prix
- `sort_by` - Tri (distance, rating, price_asc, price_desc)
- `per_page` - Nombre de résultats par page (défaut: 15)

### 📦 Commandes (`/api/commandes`)
- ✅ `GET /commandes` - Liste des commandes de l'utilisateur (protégé)
- ✅ `POST /commandes` - Créer une commande (protégé, client uniquement)
- ✅ `GET /commandes/{id}` - Détails d'une commande (protégé)
- ✅ `PUT /commandes/{id}/status` - Mettre à jour le statut (protégé)

**Filtres pour GET /commandes :**
- `statut` - Filtrer par statut
- `type` - Filtrer par type (immediate, programmee)
- `per_page` - Nombre de résultats par page

**Statuts disponibles :**
- `en_attente` - En attente de confirmation
- `acceptee` - Acceptée par le prestataire
- `refusee` - Refusée
- `en_route` - Prestataire en route
- `arrivee` - Prestataire arrivé
- `en_cours` - Intervention en cours
- `terminee` - Terminée
- `annulee` - Annulée

## 📋 Format des Réponses

### Succès
```json
{
  "success": true,
  "message": "Message optionnel",
  "data": { ... }
}
```

### Erreur
```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": { ... } // Optionnel pour les erreurs de validation
}
```

### Pagination
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

## 🔐 Authentification

Pour les routes protégées, inclure le token dans le header :
```
Authorization: Bearer {token}
```

Le token est obtenu via `/api/auth/login` ou `/api/auth/register`.

## 📝 Exemples d'Utilisation

### 1. Connexion
```bash
POST /api/auth/login
{
  "identifier": "email@example.com" ou "+221771234567",
  "password": "password"
}
```

### 2. Recherche de prestataires
```bash
GET /api/prestataires/search?keyword=plombier&latitude=14.7167&longitude=-17.4677&max_distance=10&min_rating=4
```

### 3. Créer une commande
```bash
POST /api/commandes
Authorization: Bearer {token}
{
  "prestataire_id": 1,
  "categorie_service_id": 1,
  "sous_categorie_service_id": 2,
  "type_commande": "immediate",
  "description": "Fuite d'eau dans la salle de bain",
  "adresse_intervention": "123 Rue Example, Dakar",
  "latitude": 14.7167,
  "longitude": -17.4677,
  "montant_total": 5000
}
```

### 4. Mettre à jour le statut d'une commande
```bash
PUT /api/commandes/1/status
Authorization: Bearer {token}
{
  "statut": "acceptee"
}
```

## ⚠️ Notes Importantes

1. **Type vs Role** : L'API retourne `type` au lieu de `role` pour la compatibilité mobile
2. **Photos** : Les photos sont stockées dans `storage/app/public/` et retournées avec l'URL complète
3. **Distance** : Le calcul de distance utilise la formule Haversine (en km)
4. **Permissions** : Les clients ne peuvent créer que des commandes, les prestataires peuvent mettre à jour les statuts
5. **Validation** : Seuls les prestataires avec `statut_inscription = 'valide'` et `disponible = true` apparaissent dans les recherches

## 🚀 Prochaines Étapes

Endpoints à créer :
- [ ] Avis (`/api/avis`)
- [ ] Transactions/Paiements (`/api/payment`)
- [ ] Notifications (`/api/notifications`)
- [ ] Chat/Messages (`/api/messages`)
- [ ] Profil Prestataire (gestion) (`/api/prestataire/profile`)
- [ ] Profil Client (gestion) (`/api/client/profile`)

## 📁 Fichiers Créés

1. `routes/api.php` - Routes API
2. `app/Http/Controllers/Api/AuthController.php` - Authentification
3. `app/Http/Controllers/Api/CategorieController.php` - Catégories
4. `app/Http/Controllers/Api/PrestataireController.php` - Prestataires
5. `app/Http/Controllers/Api/CommandeController.php` - Commandes

## 🔧 Installation Requise

N'oubliez pas d'installer Laravel Sanctum :
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Puis décommenter dans `app/Models/User.php` :
```php
use Laravel\Sanctum\HasApiTokens;
// Et dans la classe :
use HasApiTokens;
```

