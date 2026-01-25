# Stratégie de Migration V2 : Vers une Architecture Unifiée

Ce document détaille l'état actuel de l'infrastructure technique de FSCO, les raisons de nos choix architecturaux pour la V1, et la feuille de route précise pour la transition vers la version 2.

## 1. État des Lieux : L'Architecture Hybride (V1)

Pour permettre un déploiement rapide et efficace, nous avons opté pour une approche pragmatique qui sépare la gestion du contenu statique de la gestion des données dynamiques.

### Le Contenu "Froid" (JSON)
Tout ce qui concerne la vitrine du site — à savoir les formations, les articles de blog, les ressources téléchargeables et la configuration générale — est actuellement stocké dans des fichiers JSON plats. Cette méthode offre une excellente performance de lecture, car elle évite des requêtes base de données coûteuses pour des informations qui changent peu. Elle simplifie également considérablement les sauvegardes : une simple copie des fichiers suffit.

### Les Données "Chaudes" (MySQL)
En revanche, la partie critique de l'application, c'est-à-dire le système d'évaluation et d'examens, repose sur une base de données MySQL robuste. C'est indispensable pour gérer les relations complexes entre les utilisateurs, les questions, les examens et les résultats, tout en garantissant l'intégrité des données transactionnelles.

### La Gestion des Accès
Cette dualité se reflète dans l'authentification. L'administrateur principal, responsable du contenu éditorial, est défini directement dans la configuration du serveur pour une sécurité maximale hors base de données. Parallèlement, les utilisateurs (étudiants, professeurs et administrateurs d'examens) sont gérés dynamiquement dans la base de données pour permettre les inscriptions et la gestion des rôles.

---

## 2. Les Défis de la V2 : Pourquoi et Comment Migrer

Si l'architecture actuelle est parfaite pour le lancement, elle présente des limites structurelles que la V2 devra adresser pour permettre la croissance de la plateforme.

### L'Unification des Données
Le défi majeur sera de fusionner ces deux mondes. Actuellement, il est impossible de lier techniquement un auteur (table utilisateurs) à un article de blog (fichier JSON). La V2 devra migrer tout le contenu JSON vers des tables relationnelles MySQL (`articles`, `formations`, `ressources`). Cela permettra des fonctionnalités avancées comme "Voir tous les articles de cet auteur" ou "Lister les formations suivies par cet étudiant".

### La Centralisation de l'Authentification
Nous devrons abandonner l'administrateur "système" défini dans le code au profit d'une gestion 100% base de données. Cela signifie que tous les utilisateurs, du super-admin à l'étudiant, seront dans la même table `users`, différenciés uniquement par leurs rôles et permissions. Cela offrira une granularité de sécurité bien plus fine et permettra de changer les mots de passe admin sans toucher au code source.

### La Refonte du Backend
C'est le chantier le plus technique. Il faudra réécrire la couche d'accès aux données de l'administration. Tous les scripts qui lisent actuellement des fichiers JSON (`json_decode`) devront être refondus pour exécuter des requêtes SQL (`SELECT * FROM`). Cela implique une réécriture significative des fichiers comme `formations.php`, `blogs.php` et `ressources.php`.

### La Gestion de la Performance
Passer du JSON au tout-SQL aura un impact sur la charge serveur. Pour la V2, il faudra sans doute implémenter un système de cache (comme Redis ou un cache fichier simple) pour éviter de solliciter la base de données à chaque affichage de la page d'accueil, conservant ainsi la rapidité de la V1 tout en gagnant la puissance du relationnel.

---

## 3. Conclusion

Cette transition vers la V2 n'est pas une correction de bug, mais une évolution naturelle de la maturité du projet. La V1 est optimisée pour le "Time-to-Market" et la simplicité opérationnelle. La V2 sera optimisée pour la scalabilité, la cohérence des données et la maintenabilité à long terme.
