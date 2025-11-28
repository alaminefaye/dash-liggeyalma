    # 🔥 Configuration Firebase pour les Notifications Push

## ⚠️ IMPORTANT : Mise à jour vers l'API V1

**Nous utilisons maintenant Firebase Cloud Messaging V1 API** (recommandé par Google) au lieu de l'API Legacy.

👉 **Voir le guide complet** : [FIREBASE_V1_SETUP.md](./FIREBASE_V1_SETUP.md)

## 📋 Prérequis

1. Projet Firebase créé dans [Firebase Console](https://console.firebase.google.com/)
2. Application iOS et Android enregistrées dans Firebase
3. Bundle ID : `com.depannema.app` (pour iOS et Android)
4. API Firebase Cloud Messaging activée dans Google Cloud Console

## 🔑 Configuration FCM V1 (Nouvelle méthode)

### Voir le guide détaillé : [FIREBASE_V1_SETUP.md](./FIREBASE_V1_SETUP.md)

**Résumé rapide :**
1. Créer un compte de service dans Google Cloud Console
2. Télécharger le fichier JSON des credentials
3. Placer le fichier dans `storage/app/firebase-credentials.json`
4. Configurer les variables dans `.env`

---

## 🔑 Méthode Legacy (Ancienne - Non recommandée)

### ⚠️ L'API Legacy est dépréciée

Cette méthode utilise l'API Legacy qui sera dépréciée. Utilisez plutôt l'API V1 (voir ci-dessus).

### Étape 1 : Accéder aux paramètres du projet

1. Allez sur [Firebase Console](https://console.firebase.google.com/)
2. Sélectionnez votre projet : **depannema-288ba**
3. Cliquez sur l'icône ⚙️ (Paramètres) en haut à gauche
4. Sélectionnez **Paramètres du projet**
5. Cliquez sur l'onglet **Cloud Messaging**

### Étape 2 : Activer l'API Legacy (si elle est désactivée)

**Si vous voyez "API Cloud Messaging (ancienne version) - Désactivée"** :

1. **Rafraîchissez la page Firebase Console** (appuyez sur F5 ou Cmd+R)
2. Si elle reste désactivée, allez dans **Google Cloud Console** :
   - Ouvrez : https://console.cloud.google.com/apis/library?project=depannema-288ba
   - Recherchez **"Firebase Cloud Messaging API (Legacy)"** ou **"FCM Legacy API"**
   - Si elle apparaît, cliquez dessus et activez-la
3. **Ou utilisez l'API V1** (recommandée, voir Option 2 ci-dessous)

### Étape 3 : Récupérer la Server Key (si l'API Legacy est activée)

Une fois l'API Legacy activée :

4. ✅ **Une fois que vous voyez "API activée"** (comme vous voyez maintenant), l'API est prête !

### Étape 3 : Aller dans Firebase Console pour obtenir la Server Key

**Maintenant, vous devez aller dans Firebase Console** (pas Google Cloud Console) :

1. **Ouvrez un nouvel onglet** ou cliquez sur ce lien : https://console.firebase.google.com/
2. Sélectionnez votre projet : **depannema-288ba**
3. Cliquez sur l'icône ⚙️ (Paramètres) en haut à gauche
4. Cliquez sur **"Paramètres du projet"**
5. Cliquez sur l'onglet **"Cloud Messaging"**
6. Dans la section **"API Cloud Messaging (ancienne version)"**, vous devriez maintenant voir la **Server Key** (clé du serveur)
7. **Copiez cette clé** - elle ressemble à : `AAAAxxxxxxx:APA91bHxxxxx...`
8. Collez-la dans votre fichier `.env` comme `FIREBASE_SERVER_KEY`

> ✅ **IMPORTANT** : Pour notre configuration, vous avez seulement besoin de la **Server Key** (un texte simple). 
> Vous n'avez **PAS besoin** de télécharger un fichier JSON. 
> Le fichier `google-services.json` (pour Android) et `GoogleService-Info.plist` (pour iOS) sont déjà en place dans l'application mobile.

## ⚙️ Configuration dans Laravel

### ✅ CE DONT VOUS AVEZ BESOIN

Pour que les notifications fonctionnent depuis votre backend Laravel, vous avez besoin de :
- ✅ **La Server Key** (un texte simple - copiez/collez dans `.env`)
- ❌ **PAS besoin** du fichier JSON du service account
- ❌ **PAS besoin** de télécharger d'autres fichiers

> 📝 **Note** : Les fichiers `google-services.json` et `GoogleService-Info.plist` sont déjà dans l'application mobile Flutter. Ce n'est pas ce dont vous avez besoin pour le backend.

### Étape 1 : Ajouter la clé dans le fichier `.env`

Ouvrez le fichier `.env` à la racine du projet `dash-liggueyalma` et ajoutez :

```env
FIREBASE_SERVER_KEY=votre_cle_serveur_ici
FIREBASE_PROJECT_ID=depannema-288ba
```

Remplacez `votre_cle_serveur_ici` par la Server Key que vous avez copiée depuis Firebase Console.

**Exemple** :
```env
FIREBASE_SERVER_KEY=AAAAxxx123:APA91bHxxx456789...
FIREBASE_PROJECT_ID=depannema-288ba
```

### Étape 2 : Vérifier la configuration

Le fichier `config/firebase.php` a déjà été créé et utilise ces variables d'environnement.

### Étape 3 : Tester la configuration

Une fois la clé ajoutée, vous pouvez tester en créant une commande depuis l'application mobile. Le prestataire devrait recevoir une notification push.

## 📱 Fichiers déjà configurés

### Application Mobile (Flutter)
- ✅ `ios/Runner/GoogleService-Info.plist`
- ✅ `android/app/google-services.json`
- ✅ Bundle ID : `com.depannema.app`
- ✅ Initialisation Firebase dans `main.dart`

### Backend Laravel
- ✅ Service FCM : `app/Services/Notifications/FCMService.php`
- ✅ Configuration : `config/firebase.php`
- ✅ Intégration dans :
  - `CommandeController` : Notification lors de création de commande
  - `CommandeController` : Notification lors de changement de statut
  - `MessageController` : Notification pour nouveaux messages

## 🧪 Tester les notifications

### Méthode 1 : Via l'application mobile

1. Connectez-vous en tant que client
2. Créez une commande pour un prestataire
3. Le prestataire devrait recevoir une notification

### Méthode 2 : Via une route de test (à créer si besoin)

Vous pouvez créer une route de test temporaire :

```php
Route::post('/test-notification', function (Request $request) {
    $fcmService = new \App\Services\Notifications\FCMService();
    
    $result = $fcmService->sendToUser(
        $request->user_id, // ID de l'utilisateur
        'Test Notification',
        'Ceci est un test de notification push',
        ['type' => 'test']
    );
    
    return response()->json($result);
})->middleware('auth:sanctum');
```

## 🔍 Dépannage

### Les notifications ne fonctionnent pas

1. **Vérifier la clé serveur** :
   - Ouvrez `.env` et vérifiez que `FIREBASE_SERVER_KEY` est correctement rempli
   - La clé doit commencer par `AAAA` et être très longue

2. **Vérifier les logs Laravel** :
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Vérifier que les tokens FCM sont enregistrés** :
   ```sql
   SELECT * FROM fcm_tokens WHERE is_active = 1;
   ```

4. **Vérifier les permissions iOS** :
   - L'utilisateur doit autoriser les notifications dans les paramètres de l'app

5. **Tester avec un token valide** :
   - Utilisez la route de test ci-dessus avec un `user_id` qui a un token FCM actif

## 🔄 Option 2 : Utiliser l'API V1 (Recommandée)

Si l'API Legacy reste désactivée, vous pouvez utiliser l'API V1 qui est déjà activée. Cela nécessite un compte de service.

### Créer un compte de service :

1. Dans Firebase Console, cliquez sur **"Gérer les comptes de service"** (dans la section "API Firebase Cloud Messaging (V1)")
2. Ou allez directement à : https://console.cloud.google.com/iam-admin/serviceaccounts?project=depannema-288ba
3. Cliquez sur **"Créer un compte de service"**
4. Donnez un nom (ex: `fcm-service`) et cliquez sur **"Créer"**
5. Sélectionnez le rôle **"Firebase Cloud Messaging API Admin"** ou **"Cloud Messaging API Admin"**
6. Cliquez sur **"Continuer"** puis **"Terminé"**
7. Cliquez sur le compte créé, allez dans l'onglet **"Clés"**
8. Cliquez sur **"Ajouter une clé"** → **"Créer une nouvelle clé"**
9. Sélectionnez **JSON** et cliquez sur **"Créer"**
10. Le fichier JSON sera téléchargé - **garde-le précieusement** !

### Configurer Laravel pour l'API V1 :

1. Placez le fichier JSON dans `dash-liggueyalma/storage/app/firebase-credentials.json`
2. Ajoutez dans `.env` :
   ```env
   FIREBASE_CREDENTIALS_PATH=storage/app/firebase-credentials.json
   FIREBASE_PROJECT_ID=depannema-288ba
   ```
3. Le code FCMService devra être mis à jour pour utiliser l'API V1 (modification nécessaire du code)

> ⚠️ **Note** : L'utilisation de l'API V1 nécessite des modifications du code `FCMService.php`. Pour l'instant, essayons d'abord de faire fonctionner l'API Legacy.

## 📚 Types de notifications disponibles

Le service FCM supporte actuellement :

- ✅ **Nouvelle commande** : Quand un client crée une commande
- ✅ **Changement de statut** : Quand le statut d'une commande change
- ✅ **Nouveau message** : Quand un utilisateur reçoit un message
- ✅ **Paiement reçu** : Quand un prestataire reçoit un paiement
- ✅ **Nouvel avis** : Quand un prestataire reçoit un avis

## 🎯 Prochaines étapes

1. Ajouter la `FIREBASE_SERVER_KEY` dans `.env`
2. Tester la création d'une commande
3. Vérifier que les notifications arrivent sur l'appareil
4. (Optionnel) Configurer les notifications programmées avec des queues Laravel

## 📞 Support

Si vous rencontrez des problèmes :
- Vérifiez les logs Laravel
- Vérifiez les logs Firebase dans la console
- Assurez-vous que l'API Cloud Messaging (Legacy) est activée dans Firebase

## ❓ FAQ

### Q: Dois-je télécharger un fichier JSON pour le backend ?
**R:** Non ! Pour notre configuration actuelle, vous avez seulement besoin de la **Server Key** (un texte simple). Aucun fichier JSON à télécharger.

### Q: Où se trouve le fichier google-services.json ?
**R:** Il est déjà dans votre projet Flutter (`android/app/google-services.json`). C'est pour l'application mobile, pas pour le backend Laravel.

### Q: Et le fichier de service account JSON ?
**R:** Pas nécessaire pour l'API Legacy. On l'utilise seulement si on migre vers l'API V1 de Firebase (plus tard).

