# 📱 Installation des APIs Mobile

## ✅ Ce qui a été créé

1. **Routes API** : `routes/api.php`
   - `/api/auth/login` - Connexion
   - `/api/auth/register` - Inscription
   - `/api/auth/logout` - Déconnexion
   - `/api/user/profile` - Profil utilisateur
   - `/api/user/profile` (PUT) - Mise à jour profil
   - `/api/user/photo` - Upload photo

2. **Contrôleur API** : `app/Http/Controllers/Api/AuthController.php`
   - Méthodes login, register, logout, profile, updateProfile, uploadPhoto

3. **Configuration** :
   - Routes API ajoutées dans `bootstrap/app.php`
   - Modèle User mis à jour avec `HasApiTokens`

## 🔧 Installation de Sanctum (Requis)

Sanctum est nécessaire pour l'authentification API. Exécutez ces commandes :

```bash
cd dash-liggueyalma
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## ⚙️ Configuration

### 1. Configurer Sanctum dans `config/sanctum.php`

Assurez-vous que la configuration est correcte (généralement par défaut).

### 2. Ajouter le middleware dans `bootstrap/app.php`

Le middleware est déjà configuré dans les routes API.

### 3. Vérifier les routes

Testez les endpoints avec Postman ou curl :

```bash
# Test Login
curl -X POST https://depannema.universaltechnologiesafrica.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "test@example.com",
    "password": "password"
  }'

# Test Register
curl -X POST https://depannema.universaltechnologiesafrica.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "identifier": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

## 📝 Format des Réponses

### Login Success
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
      "email_verified_at": "2024-01-01T00:00:00.000000Z",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

### Register Success
```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "token": "1|xxxxxxxxxxxx",
    "user": { ... }
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "identifier": ["Le champ identifier est requis."]
  }
}
```

## 🔐 Utilisation du Token

Pour les routes protégées, inclure le token dans le header :

```
Authorization: Bearer {token}
```

## ⚠️ Notes Importantes

1. **Type vs Role** : L'API retourne `type` au lieu de `role` pour la compatibilité avec le mobile
2. **Identifier** : Accepte email OU téléphone pour login/register
3. **Photo** : Stockée dans `storage/app/public/photos/`
4. **Status** : Vérifie que l'utilisateur est `active` avant de permettre la connexion

## 🚀 Prochaines Étapes

1. Installer Sanctum (voir commandes ci-dessus)
2. Tester les endpoints avec Postman
3. Connecter l'application mobile
4. Ajouter les autres endpoints (commandes, prestataires, etc.)

