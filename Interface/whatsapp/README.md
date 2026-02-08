# Service WhatsApp FSCO - Baileys

Service Node.js pour gérer WhatsApp avec Baileys et l'intégration IA pour le site FSCO.

## 🚀 Déploiement sur Render (Gratuit)

### Étape 1 : Créer un compte Render
1. Allez sur [render.com](https://render.com)
2. Créez un compte gratuit
3. Créez un nouveau **Web Service**

### Étape 2 : Configuration du service
1. **Nom** : `fsco-whatsapp`
2. **Environment** : Node.js
3. **Build Command** : `npm install`
4. **Start Command** : `node index.js`
5. **Instance Type** : Free (ou Starter)

### Étape 3 : Variables d'environnement
Ajoutez ces variables d'environnement dans Render :

```bash
# URL de l'interface PHP (InfinityFree)
PHP_API_URL=https://fsco.gt.tc/Interface/api

# Clé API pour communiquer avec l'interface PHP
PHP_API_KEY=fsco_change_this_to_secure_key

# Configuration de l'IA
AI_TYPE=openai
OPENAI_API_KEY=sk-your-openai-api-key
OPENAI_MODEL=gpt-4
OPENAI_TEMPERATURE=0.7
OPENAI_MAX_TOKENS=2000

# Port
PORT=3000

# Numéro WhatsApp autorisé (optionnel)
AUTHORIZED_NUMBERS=+212XXXXXXXXXX

# Mode debug
DEBUG=false
```

### Étape 4 : Déploiement
1. Connectez votre repository GitHub
2. Sélectionnez le repository
3. Cliquez sur **Deploy**
4. Attendez que le service soit en ligne

### Étape 5 : Récupérer l'URL
Une fois déployé, Render vous donnera une URL comme :
```
https://fsco-whatsapp.onrender.com
```

## 🚀 Déploiement sur Railway (Gratuit)

### Étape 1 : Créer un compte Railway
1. Allez sur [railway.app](https://railway.app)
2. Créez un compte gratuit
3. Cliquez sur **New Project**

### Étape 2 : Ajouter un service
1. Cliquez sur **+ New Service**
2. Sélectionnez **Deploy from GitHub repo**
3. Connectez votre repository

### Étape 3 : Configuration
1. **Name** : `fsco-whatsapp`
2. **Environment** : Node.js
3. **Variables** : Ajoutez les mêmes variables que Render (voir ci-dessus)

### Étape 4 : Déploiement
1. Cliquez sur **Deploy**
2. Attendez que le service soit en ligne

### Étape 5 : Récupérer l'URL
Railway vous donnera une URL comme :
```
https://fsco-whatsapp.up.railway.app
```

## 🔧 Configuration Locale

### Installation
```bash
cd Interface/whatsapp
npm install
```

### Configuration
1. Copiez `.env.example` vers `.env`
2. Modifiez les variables selon vos besoins

### Démarrage
```bash
npm start
```

### Mode développement
```bash
npm run dev
```

## 📱 Utilisation

### 1. Premier démarrage
Au premier démarrage, un QR code sera affiché dans le terminal. Scannez-le avec WhatsApp sur votre téléphone.

### 2. Connexion WhatsApp
Une fois connecté, le service :
- Recevra les messages WhatsApp
- Enverra à l'IA
- Appliquera les changements après confirmation

### 3. Communication avec l'interface PHP
Le service communique avec l'interface PHP via :
- Webhook : `POST /webhook`
- API REST : `GET /api/status`, `POST /api/message`

## 🔐 Sécurité

### Clé API PHP
Modifiez `PHP_API_KEY` dans `.env` avec une clé sécurisée.

### Numéros autorisés
Configurez `AUTHORIZED_NUMBERS` pour limiter qui peut utiliser le service.

### HTTPS
Assurez-vous que l'URL PHP_API_URL utilise HTTPS.

## 📊 Monitoring

### Logs
Les logs sont sauvegardés dans `logs/whatsapp.log`.

### API Status
Vérifiez le statut du service :
```bash
curl https://fsco-whatsapp.onrender.com/api/status
```

## 🐛 Dépannage

### QR code ne s'affiche pas
- Vérifiez que le port n'est pas bloqué
- Vérifiez les logs dans `logs/whatsapp.log`

### Erreur de connexion PHP
- Vérifiez que `PHP_API_URL` est correcte
- Vérifiez que `PHP_API_KEY` correspond à celle configurée dans l'interface PHP

### Erreur IA
- Vérifiez que les clés API sont correctes
- Vérifiez que le modèle est disponible

## 📞 Support

Pour toute question, contactez : contact@fsco.gt.tc
