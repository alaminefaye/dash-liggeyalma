# 🔧 Comment activer l'API Legacy FCM

## Problème
L'API Cloud Messaging (ancienne version) apparaît comme "Désactivée" dans Firebase Console, même après avoir activé l'API FCM dans Google Cloud Console.

## Solution : Activer l'API Legacy spécifiquement

### Étape 1 : Activer l'API Legacy dans Google Cloud Console

1. Allez sur : https://console.cloud.google.com/apis/library?project=depannema-288ba
2. Dans la barre de recherche, tapez : **"Firebase Cloud Messaging"** ou **"FCM"**
3. Cherchez **"Firebase Cloud Messaging API"** (pas juste "Firebase Cloud Messaging API")
4. Il devrait y avoir deux APIs :
   - ✅ Firebase Cloud Messaging API (déjà activée)
   - ❓ Firebase Cloud Messaging API (Legacy) - à activer

### Étape 2 : Si l'API Legacy n'apparaît pas

Parfois, l'API Legacy est intégrée à l'API principale. Dans ce cas :

1. Allez sur : https://console.cloud.google.com/apis/credentials?project=depannema-288ba
2. Cliquez sur **"+ CRÉER DES IDENTIFIANTS"** (en haut)
3. Sélectionnez **"Clé API"**
4. Une clé API sera créée - **ce n'est pas la Server Key**
5. Annulez cette création

### Étape 3 : Vérifier dans Firebase Console

1. Retournez dans Firebase Console : https://console.firebase.google.com/project/depannema-288ba/settings/cloudmessaging
2. **Rafraîchissez la page** (F5)
3. Vérifiez si la Server Key apparaît maintenant

### Étape 4 : Solution Alternative - Utiliser l'ID de l'expéditeur

Si la Server Key n'apparaît toujours pas, vous pouvez essayer d'utiliser l'**ID de l'expéditeur** qui est visible : `704564606130`

Mais cela nécessiterait de modifier le code pour utiliser une autre méthode d'authentification.

## ⚠️ Note importante

L'API Legacy sera dépréciée en juin 2024. Il est recommandé de migrer vers l'API V1 avec un compte de service.

