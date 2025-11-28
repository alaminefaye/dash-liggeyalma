# 🔔 Configuration Firebase Cloud Messaging V1 API

Ce guide explique comment configurer Firebase Cloud Messaging (FCM) V1 API pour envoyer des notifications push depuis votre backend Laravel.

## 📋 Prérequis

- Projet Firebase créé : `depannema-288ba`
- Accès à Google Cloud Console
- API Firebase Cloud Messaging activée

## 🔑 Étape 1 : Créer un compte de service

1. **Allez sur Google Cloud Console - Comptes de service** :
   - URL directe : https://console.cloud.google.com/iam-admin/serviceaccounts?project=depannema-288ba
   - Ou : Google Cloud Console → IAM & Admin → Service Accounts

2. **Créer un nouveau compte de service** :
   - Cliquez sur **"+ CRÉER UN COMPTE DE SERVICE"** (ou "+ CREATE SERVICE ACCOUNT")
   
3. **Remplir les informations** :
   - **Nom du compte de service** : `fcm-notification-service`
   - **Description** : `Service account pour les notifications FCM`
   - Cliquez sur **"CRÉER ET CONTINUER"**

4. **Attribuer un rôle** :
   - Cliquez sur **"Sélectionner un rôle"**
   - Recherchez : `Firebase Cloud Messaging API Admin`
   - Ou : `Cloud Messaging API Admin`
   - Sélectionnez le rôle
   - Cliquez sur **"CONTINUER"**

5. **Finaliser** :
   - Cliquez sur **"TERMINÉ"** (vous pouvez ignorer l'étape "Accorder l'accès aux utilisateurs")

## 📥 Étape 2 : Télécharger le fichier JSON des credentials

1. **Dans la liste des comptes de service**, trouvez le compte que vous venez de créer : `fcm-notification-service@depannema-288ba.iam.gserviceaccount.com`

2. **Cliquez sur le compte** pour ouvrir ses détails

3. **Onglet "Clés"** :
   - Cliquez sur l'onglet **"Clés"** (Keys)
   - Cliquez sur **"+ AJOUTER UNE CLÉ"** → **"Créer une nouvelle clé"**

4. **Télécharger la clé JSON** :
   - Sélectionnez le type : **JSON**
   - Cliquez sur **"CRÉER"**
   - Un fichier JSON sera téléchargé automatiquement (nom du fichier : `depannema-288ba-xxxxx.json`)

## 📁 Étape 3 : Placer le fichier JSON dans votre projet

1. **Renommer le fichier** (optionnel mais recommandé) :
   - Renommez le fichier téléchargé en : `firebase-credentials.json`

2. **Placer le fichier dans le projet Laravel** :
   - Copiez le fichier `firebase-credentials.json` dans :
   - `/Users/mouhamadoulaminefaye/Desktop/PROJETS DEV/projet liggeyalma/dash-liggueyalma/storage/app/`
   
   ⚠️ **IMPORTANT** : Ne placez JAMAIS ce fichier dans le dossier `public/` car il contient des informations sensibles !

3. **Vérifier les permissions** (sur Linux/Mac) :
   ```bash
   chmod 600 storage/app/firebase-credentials.json
   ```

## ⚙️ Étape 4 : Configurer le fichier `.env`

Ajoutez ou modifiez ces lignes dans votre fichier `.env` :

```env
FIREBASE_PROJECT_ID=depannema-288ba
FIREBASE_CREDENTIALS_PATH=/chemin/absolu/vers/storage/app/firebase-credentials.json
```

**Exemple pour votre projet** :
```env
FIREBASE_PROJECT_ID=depannema-288ba
FIREBASE_CREDENTIALS_PATH=/Users/mouhamadoulaminefaye/Desktop/PROJETS DEV/projet liggeyalma/dash-liggueyalma/storage/app/firebase-credentials.json
```

> 💡 **Note** : Le chemin peut être absolu (recommandé) ou relatif au dossier racine de Laravel.

## ✅ Étape 5 : Vérifier la configuration

1. **Vérifier que le fichier existe** :
   ```bash
   cd dash-liggueyalma
   ls -la storage/app/firebase-credentials.json
   ```

2. **Vérifier le contenu du fichier** (devrait contenir) :
   ```json
   {
     "type": "service_account",
     "project_id": "depannema-288ba",
     "private_key_id": "...",
     "private_key": "-----BEGIN PRIVATE KEY-----\n...",
     "client_email": "fcm-notification-service@depannema-288ba.iam.gserviceaccount.com",
     "client_id": "...",
     "auth_uri": "https://accounts.google.com/o/oauth2/auth",
     "token_uri": "https://oauth2.googleapis.com/token",
     "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
     "client_x509_cert_url": "..."
   }
   ```

3. **Tester la configuration** (optionnel) :
   ```bash
   php artisan tinker
   ```
   Puis dans tinker :
   ```php
   $fcm = new App\Services\Notifications\FCMService();
   // Si aucune erreur, c'est bon !
   ```

## 🔒 Sécurité

- ⚠️ **JAMAIS** commiter le fichier `firebase-credentials.json` dans Git
- ✅ Assurez-vous que le fichier `.gitignore` contient :
  ```
  storage/app/firebase-credentials.json
  storage/app/*.json
  ```
- ✅ Utilisez des variables d'environnement pour les chemins sensibles
- ✅ Limitez les permissions du fichier (chmod 600)

## 🐛 Dépannage

### Erreur : "FCM credentials file not found"
- Vérifiez que le chemin dans `.env` est correct (chemin absolu recommandé)
- Vérifiez que le fichier existe : `ls -la storage/app/firebase-credentials.json`
- Vérifiez les permissions : `chmod 600 storage/app/firebase-credentials.json`

### Erreur : "Invalid FCM credentials file format"
- Vérifiez que le fichier JSON est valide : `cat storage/app/firebase-credentials.json | python -m json.tool`
- Vérifiez qu'il contient `client_email` et `private_key`

### Erreur : "Failed to get FCM access token"
- Vérifiez que l'API Firebase Cloud Messaging est activée dans Google Cloud Console
- Vérifiez que le compte de service a le rôle `Firebase Cloud Messaging API Admin`
- Vérifiez les logs Laravel : `storage/logs/laravel.log`

### Erreur : "Permission denied"
- Sur Linux/Mac : `chmod 600 storage/app/firebase-credentials.json`
- Sur Windows : Vérifiez que le fichier n'est pas en lecture seule

## 📚 Documentation

- [Firebase Cloud Messaging V1 API](https://firebase.google.com/docs/cloud-messaging/migrate-v1)
- [Google Cloud Service Accounts](https://cloud.google.com/iam/docs/service-accounts)
- [Laravel HTTP Client](https://laravel.com/docs/http-client)

## ✅ Checklist finale

- [ ] Compte de service créé dans Google Cloud Console
- [ ] Rôle `Firebase Cloud Messaging API Admin` attribué
- [ ] Fichier JSON téléchargé et placé dans `storage/app/`
- [ ] Variables d'environnement configurées dans `.env`
- [ ] Fichier `.gitignore` mis à jour
- [ ] Permissions du fichier configurées (chmod 600)
- [ ] Test effectué (optionnel)

Une fois toutes ces étapes terminées, votre backend Laravel sera prêt à envoyer des notifications push via Firebase Cloud Messaging V1 ! 🎉

