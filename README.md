# FSCo - Formation Suivi Conseil 🚀

> **Plateforme Éducative Moderne - Architecture 100% SQL avec Intelligence Artificielle intégrée.**

---

## 📖 Histoire de l'Application

FSCo est née de la volonté d'offrir une solution robuste de **Formation, Suivi et Conseil** pour le marché marocain. Initialement conçue avec une structure hybride (JSON pour le contenu, SQL pour les examens), l'application a subi une **migration architecturale majeure** vers un modèle relationnel complet. Cette évolution permet aujourd'hui une interconnexion totale entre les contenus (blogs, cours, ressources) et les agents d'intelligence artificielle.

---

## 🏗️ Architecture Technique

### Backend (PHP 8.x)
- **API REST** : Authentification sécurisée par JWT ou Clés API (format `fsco_...`).
- **Base de Données** : MySQL/MariaDB (Moteur InnoDB), structurée en 14 tables normalisées.
- **Audit & Sécurité** : Journalisation complète des actions via `audit_logs` et protection contre les injections SQL.

### Frontend
- **Interface Dynamique** : Page d'accueil (`index.php`) synchronisée en temps réel avec la base de données via la table `site_config`.
- **Espace Administrateur** : Gestion centralisée du contenu et du système d'évaluations.

### Service IA & WhatsApp (Node.js)
- **Agent Maître** : Propulsé par Google Gemini 2.0 Flash pour l'assistance contextuelle.
- **Intégration WhatsApp** : Service Baileys autonome gérant l'historique et les demandes automatiques après approbation.

---

## 🚀 Guide de Déploiement Rigoureux

### 1. Déploiement du Site (InfinityFree / Hostinger / etc.)

1.  **Préparation de la Base de Données** :
    - Créez une base de données MySQL dans votre panneau de contrôle.
    - Importez le fichier **`database/migrate_v4.sql`**. Ce fichier va créer toutes les tables et injecter la configuration initiale.
2.  **Configuration des Fichiers** :
    - Renommez `htdocs/.env.example` en **`.env`**.
    - Remplissez les informations de connexion : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
    - Ajoutez votre **`GEMINI_API_KEY`**.
3.  **Migration des Données Legacy** :
    - Si vous avez des données dans les anciens fichiers JSON, exécutez le script : `php database/migrate_json_to_sql.php`.
    - Une fois terminé, le dossier `htdocs/pages/admin/data/` peut être supprimé.

### 2. Déploiement du Service WhatsApp (Render / Railway)

1.  **Hébergement Node.js** :
    - Poussez le dossier `Interface/whatsapp/` sur un repository GitHub séparé ou déployez-le directement.
    - Sur Render/Railway, créez un **Web Service**.
2.  **Variables d'Environnement (Secrets)** :
    - `PHP_API_URL` : L'URL de votre API de site (ex: `https://fsco.gt.tc/Interface/api`).
    - `PHP_API_KEY` : La clé configurée dans `Interface/config.php` (clé de liaison).
    - `GEMINI_API_KEY` : Votre clé Google AI Studio.
    - `AUTHORIZED_NUMBERS` : Les numéros autorisés à commander l'agent (format international).
3.  **Premier Lancement** :
    - Scannez le QR Code généré dans les logs du service via WhatsApp ("Appareils connectés").

---

## 📚 Endpoints API pour l'Agent IA

| Action | Endpoint (POST) | Description |
| :--- | :--- | :--- |
| **Chat** | `/api/ai/chat.php` | Conversation intelligente avec contexte |
| **Modifier Config** | `/api/admin/site-config.php` | Changement de titres, couleurs, etc. |
| **Gérer Blogs** | `/api/admin/blogs.php` | CRUD complet des articles |
| **Clés API** | `/api/admin/api-keys.php` | Autonomie de gestion des accès |

---

## 🛠️ Maintenance & Audit

- **Audit Logs** : Consultez la table `audit_logs` pour voir chaque modification effectuée par l'IA ou les administrateurs.
- **WhatsApp History** : Les tables `whatsapp_chats` et `whatsapp_messages` conservent l'historique complet pour chaque utilisateur.

---

**Propriété de FSCo - Développé avec Rigueur & Passion** 🇲🇦
