# 📱 Résumé des APIs Mobile Créées

## ✅ Ce qui a été fait

### 1. Routes API (`routes/api.php`)
- ✅ Créé le fichier avec toutes les routes d'authentification
- ✅ Routes publiques : login, register
- ✅ Routes protégées : logout, profile, updateProfile, uploadPhoto

### 2. Contrôleur API (`app/Http/Controllers/Api/AuthController.php`)
- ✅ **login()** : Connexion avec email ou téléphone
- ✅ **register()** : Inscription avec validation
- ✅ **logout()** : Déconnexion
- ✅ **profile()** : Récupérer le profil utilisateur
- ✅ **updateProfile()** : Mettre à jour le profil
- ✅ **uploadPhoto()** : Upload de photo de profil

### 3. Configuration
- ✅ Routes API ajoutées dans `bootstrap/app.php`
- ✅ Modèle User préparé (nécessite installation de Sanctum)

## ⚠️ Action Requise : Installer Sanctum

**IMPORTANT** : Avant de pouvoir utiliser les APIs, vous devez installer Laravel Sanctum :

```bash
cd dash-liggueyalma
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Ensuite, décommenter dans `app/Models/User.php` :
```php
use Laravel\Sanctum\HasApiTokens;
// Dans la classe :
use HasApiTokens;
```

## 📋 Endpoints Disponibles

### POST `/api/auth/login`
**Body:**
```json
{
  "identifier": "email@example.com" ou "+221771234567",
  "password": "password"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "token": "1|xxxxxxxxxxxx",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": null,
      "photo": "http://...",
      "status": "active",
      "type": "client",
      ...
    }
  }
}
```

### POST `/api/auth/register`
**Body:**
```json
{
  "name": "John Doe",
  "identifier": "email@example.com" ou "+221771234567",
  "password": "password123",
  "password_confirmation": "password123",
  "photo": null (optionnel, fichier image)
}
```

### GET `/api/user/profile`
**Headers:**
```
Authorization: Bearer {token}
```

### PUT `/api/user/profile`
**Headers:**
```
Authorization: Bearer {token}
```
**Body:**
```json
{
  "name": "New Name",
  "email": "new@example.com",
  "phone": "+221771234567"
}
```

### POST `/api/user/photo`
**Headers:**
```
Authorization: Bearer {token}
```
**Body:** Form-data avec fichier `photo`

## 🔄 Compatibilité Mobile

- ✅ L'API retourne `type` au lieu de `role` pour correspondre au modèle mobile
- ✅ Accepte `identifier` (email ou téléphone) pour login/register
- ✅ Format de réponse standardisé avec `success`, `message`, `data`
- ✅ Gestion des erreurs avec codes HTTP appropriés

## 🚀 Prochaines Étapes

1. **Installer Sanctum** (voir commandes ci-dessus)
2. **Tester les endpoints** avec Postman ou curl
3. **Connecter l'app mobile** aux APIs
4. **Ajouter les autres endpoints** :
   - Commandes
   - Prestataires
   - Recherche
   - Chat
   - Paiements
   - etc.

## 📝 Notes

- Les photos sont stockées dans `storage/app/public/photos/`
- Le statut de l'utilisateur doit être `active` pour se connecter
- Les tokens Sanctum expirent selon la configuration (par défaut, pas d'expiration)

