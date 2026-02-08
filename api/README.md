# FSCO API

API REST complète pour le site FSCO, permettant aux agents IA et intégrations externes d'interagir avec toutes les fonctionnalités du site.

## 📁 Structure

```
api/
├── .htaccess                          # Configuration Apache
├── README.md                          # Ce fichier
├── includes/
│   ├── api_common.php                 # Fonctions communes de l'API
│   ├── api_key_helper.php             # Gestion des clés API
│   └── jwt_helper.php                 # Gestion des tokens JWT
├── admin/
│   ├── api-keys.php                   # Gestion des clés API
│   ├── blogs.php                      # Gestion des blogs
│   ├── formations.php                 # Gestion des formations
│   ├── ressources.php                 # Gestion des ressources
│   ├── settings.php                   # Gestion des paramètres du site
│   ├── evaluations.php                # Gestion des tests/évaluations
│   └── content.php                    # Gestion des témoignages et FAQ
├── user/
│   ├── profile.php                    # Gestion du profil utilisateur
│   └── library.php                    # Gestion de la bibliothèque utilisateur
├── evaluations/
│   └── student.php                    # API pour les étudiants (tests)
├── auth/
│   ├── login.php                      # Connexion
│   ├── register.php                   # Inscription
│   └── check-email.php                # Vérification email
├── content/
│   ├── blogs.php                      # Blogs publics
│   ├── formations.php                 # Formations publiques
│   └── ressources.php                 # Ressources publiques
└── tracking/
    └── log.php                        # Tracking des actions
```

## 🔐 Authentification

L'API supporte deux méthodes d'authentification :

### 1. JWT (JSON Web Token)
Pour les utilisateurs connectés via le formulaire de connexion.

```
Authorization: Bearer <token_jwt>
```

### 2. Clé API
Pour les agents IA et intégrations externes.

```
Authorization: Bearer fsco_<clé_api>
```

ou

```
X-API-Key: fsco_<clé_api>
```

## 📚 Documentation

- **[Documentation complète de l'API](../API_DOCUMENTATION.md)** - Référence complète de tous les endpoints
- **[Guide de l'Agent IA](../AI_AGENT_GUIDE.md)** - Guide d'utilisation pour les agents IA

## 🚀 Démarrage Rapide

### 1. Créer une clé API

```bash
curl -X POST https://fsco.gt.tc/api/admin/api-keys \
  -H "Authorization: Bearer <votre_token_jwt_admin>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Agent IA Principal",
    "permissions": ["read", "write", "delete"],
    "expires_in": 2592000
  }'
```

### 2. Utiliser l'API

```bash
curl -X GET https://fsco.gt.tc/api/admin/blogs \
  -H "Authorization: Bearer fsco_<votre_clé_api>"
```

## 🔑 Permissions

Les clés API peuvent avoir les permissions suivantes :

| Permission | Description |
|------------|-------------|
| `read` | Lecture seule |
| `write` | Création et modification |
| `delete` | Suppression |
| `admin` | Tous les accès |

## 📊 Endpoints Principaux

### Admin
- `GET/POST/PUT/DELETE /api/admin/api-keys` - Gestion des clés API
- `GET/POST/PUT/DELETE /api/admin/blogs` - Gestion des blogs
- `GET/POST/PUT/DELETE /api/admin/formations` - Gestion des formations
- `GET/POST/PUT/DELETE /api/admin/ressources` - Gestion des ressources
- `GET/PUT /api/admin/settings` - Gestion des paramètres
- `GET/POST/PUT/DELETE /api/admin/evaluations` - Gestion des tests
- `GET/POST/PUT/DELETE /api/admin/content` - Gestion des témoignages/FAQ

### User
- `GET/PUT /api/user/profile` - Profil utilisateur
- `GET/POST/DELETE /api/user/library` - Bibliothèque utilisateur

### Évaluations (Étudiant)
- `GET /api/evaluations/student?type=available_tests` - Tests disponibles
- `GET /api/evaluations/student?type=test_questions&test_id=X` - Questions du test
- `POST /api/evaluations/student` - Démarrer/soumettre un test
- `GET /api/evaluations/student?type=results` - Résultats

### Content Public
- `GET /api/content/blogs` - Blogs publics
- `GET /api/content/formations` - Formations publiques
- `GET /api/content/ressources` - Ressources publiques

## 🛡️ Sécurité

- Les clés API sont stockées dans `api_keys.json` (protégé par .htaccess)
- Toutes les requêtes sont loggées dans `logs/api_YYYY-MM-DD.log`
- Rate limiting recommandé pour les endpoints publics
- Validation des entrées et sanitization des données

## 📝 Format des Réponses

Toutes les réponses suivent ce format :

```json
{
  "status": "success|error",
  "data": {},
  "message": "Message descriptif",
  "timestamp": 1705319400
}
```

## 🐛 Codes d'Erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide |
| 401 | Non autorisé |
| 403 | Interdit |
| 404 | Non trouvé |
| 405 | Méthode non autorisée |
| 409 | Conflit |
| 500 | Erreur serveur |

## 🔧 Configuration

### Variables d'environnement recommandées

```bash
# Dans votre fichier .env ou config.php
API_KEY_SECRET="votre_secret_pour_jwt"
API_RATE_LIMIT=100
API_RATE_LIMIT_PERIOD=60
```

### Fichiers de données

Les données sont stockées dans les fichiers JSON suivants :

- `api_keys.json` - Clés API
- `htdocs/pages/admin/data/blogs.json` - Blogs
- `htdocs/pages/admin/data/formations.json` - Formations
- `htdocs/pages/admin/data/ressources.json` - Ressources
- `htdocs/pages/admin/data/site_config.json` - Configuration du site
- `htdocs/pages/admin/data/testimonials.json` - Témoignages
- `htdocs/pages/admin/data/faq*.json` - FAQ
- `htdocs/pages/admin/evaluations/database/*.json` - Tests et questions

## 📞 Support

Pour toute question ou problème, contactez : contact@fsco.gt.tc

## 📄 Licence

Propriétaire : FSCO
