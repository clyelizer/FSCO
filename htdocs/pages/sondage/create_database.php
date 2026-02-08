<?php
// Script pour créer la base de données MySQL et la table des sondages
require_once dirname(__DIR__, 2) . '/config.php';

try {
    // Obtenir la connexion MySQL
    $pdo = getDBConnection();

    // Créer la table des réponses au sondage
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS survey_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date_soumission DATETIME NOT NULL,

        -- PARTIE 1 : Profil
        prenom VARCHAR(255) NOT NULL,
        pays VARCHAR(100) NOT NULL,
        adresse TEXT,
        sexe VARCHAR(50) NOT NULL,
        domaine VARCHAR(255) NOT NULL,
        annee VARCHAR(50) NOT NULL,
        etablissement VARCHAR(255) NOT NULL,
        experience VARCHAR(10) NOT NULL,

        -- PARTIE 2 : IA et Data
        cours_ia VARCHAR(255) NOT NULL,
        explication_ia TEXT,
        usage_ia VARCHAR(255) NOT NULL,
        usages_ia TEXT,

        -- PARTIE 3 : Défis
        problemes TEXT,
        probleme_principal TEXT,
        processus_repetitifs TEXT,
        processus_automatise TEXT,
        heures_economisees INT,
        quantite_donnees VARCHAR(255) NOT NULL,

        -- PARTIE 4 : Formation
        competences TEXT,
        duree_formation VARCHAR(100),
        format_formation VARCHAR(100),
        prix_formation VARCHAR(100),
        prix_converti VARCHAR(100),

        -- PARTIE 5 : Obstacles
        obstacles TEXT,
        equipements VARCHAR(255),
        raisons_pas_solutions TEXT,
        obstacle_principal TEXT,

        -- PARTIE 6 : Cybersécurité
        formation_securite VARCHAR(255),
        niveau_securite VARCHAR(50),
        pratiques_securite VARCHAR(50),
        risques_cyber TEXT,

        -- PARTIE 7 : Avenir Professionnel
        importance_ia_carriere VARCHAR(255),
        secteur_souhaite VARCHAR(255),
        demande_emploi VARCHAR(50),
        competences_importance VARCHAR(255),
        preparation_emploi VARCHAR(50),
        manque_preparation TEXT,
        entreprises_innovantes TEXT,

        -- PARTIE 8 : IA dans l'enseignement
        ia_cours VARCHAR(50),
        ia_ameliore_enseignement VARCHAR(50),
        risques_ia_enseignement VARCHAR(50),
        risques_details TEXT,

        -- PARTIE 9 : Réflexion finale
        recommandation_education TEXT,
        vision_tech TEXT,
        interets_communaute TEXT,
        email VARCHAR(255),

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($create_table_sql);

    // Créer un index sur la date pour de meilleures performances
    // $pdo->exec("CREATE INDEX idx_date_soumission ON survey_responses(date_soumission)");

    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;'>";
    echo "<h2 style='color: #0ea5e9; margin-bottom: 20px;'>✅ Base de données MySQL créée avec succès !</h2>";
    echo "<p style='margin-bottom: 15px;'><strong>Base de données :</strong> " . DB_NAME . "</p>";
    echo "<p style='margin-bottom: 15px;'><strong>Table créée :</strong> survey_responses</p>";
    echo "<p style='margin-bottom: 20px;'><strong>Statut :</strong> Prêt pour les tests</p>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<h3 style='margin-top: 0; color: #059669;'>Prochaines étapes :</h3>";
    echo "<ol style='margin-bottom: 0;'>";
    echo "<li><a href='sondage.php' style='color: #2563eb;'>Tester le sondage</a></li>";
    echo "<li>Vérifier les données sauvegardées</li>";
    echo "<li>Consulter les statistiques</li>";
    echo "</ol>";
    echo "</div>";
    echo "<p style='color: #6b7280; font-size: 0.9em;'>La base de données MySQL est maintenant opérationnelle sur InfinityFree.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #fef2f2; border: 1px solid #dc2626; border-radius: 8px;'>";
    echo "<h2 style='color: #dc2626; margin-bottom: 20px;'>❌ Erreur lors de la création de la base de données</h2>";
    echo "<p><strong>Erreur :</strong> " . $e->getMessage() . "</p>";
    echo "<div style='background: #f3f4f6; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='margin-top: 0; color: #374151;'>🔧 Dépannage :</h3>";
    echo "<ul style='margin-bottom: 0; color: #374151;'>";
    echo "<li>Vérifiez que le fichier <code>.env</code> contient les bonnes credentials InfinityFree</li>";
    echo "<li>Assurez-vous que la base de données existe dans votre panneau InfinityFree</li>";
    echo "<li>Vérifiez que l'utilisateur MySQL a les privilèges nécessaires</li>";
    echo "<li>Le hostname doit être au format <code>sqlXXX.epizy.com</code></li>";
    echo "</ul>";
    echo "</div>";
    echo "<p style='color: #6b7280; font-size: 0.9em;'>Si le problème persiste, consultez les logs d'erreur PHP dans votre panneau de contrôle InfinityFree.</p>";
    echo "</div>";
}
?>