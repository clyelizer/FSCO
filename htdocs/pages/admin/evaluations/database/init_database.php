<?php
/**
 * Script d'initialisation de la base de données
 * À exécuter une seule fois après la création de la base
 */

// Inclure la configuration principale
require_once '../config.php';

try {
    // D'abord essayer de se connecter directement à la base existante
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        $databaseExists = true;
    } catch (PDOException $e) {
        // Si la base n'existe pas, essayer de la créer
        if (strpos($e->getMessage(), 'Unknown database') !== false || strpos($e->getMessage(), '1049') !== false) {
            $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);

            // Créer la base de données
            $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");
            $databaseExists = false;
        } else {
            throw $e; // Autre erreur de connexion
        }
    }

    // Vérifier si les tables existent déjà
    $existingTables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $existingTables[] = $row[0];
    }

    $requiredTables = ['users', 'categories', 'questions', 'examens', 'examen_questions', 'examen_sessions', 'logs_activite'];
    $tablesToCreate = array_diff($requiredTables, $existingTables);

    if (empty($tablesToCreate)) {
        // Toutes les tables existent déjà
        echo "<div style='color: blue; font-family: Arial, sans-serif; padding: 20px;'>";
        echo "<h2>ℹ️ Base de données déjà initialisée</h2>";
        echo "<p>Toutes les tables nécessaires existent déjà dans la base de données.</p>";
        echo "<p>Tables trouvées : " . implode(', ', $existingTables) . "</p>";
        echo "<p style='color: green;'><strong>✅ Vous pouvez utiliser l'application normalement !</strong></p>";
        echo "</div>";
        exit;
    }

    // Lire et exécuter le schéma
    $schema = file_get_contents(__DIR__ . '/schema.sql');

    // Diviser le schéma en requêtes individuelles
    $queries = array_filter(array_map('trim', explode(';', $schema)));

    $createdTables = [];
    foreach ($queries as $query) {
        if (!empty($query) && !preg_match('/^(SET|START|COMMIT|--)/i', $query)) {
            // Extraire le nom de la table de la requête CREATE TABLE
            if (preg_match('/CREATE TABLE `?(\w+)`?/i', $query, $matches)) {
                $tableName = $matches[1];
                if (in_array($tableName, $tablesToCreate)) {
                    $pdo->exec($query);
                    $createdTables[] = $tableName;
                }
            } elseif (preg_match('/INSERT INTO `?(\w+)`?/i', $query, $matches)) {
                // Les INSERT sont exécutés seulement si la table correspondante a été créée
                $tableName = $matches[1];
                if (in_array($tableName, $createdTables)) {
                    $pdo->exec($query);
                }
            } else {
                // Autres requêtes (clés étrangères, etc.)
                $pdo->exec($query);
            }
        }
    }

    if ($databaseExists) {
        echo "<div style='color: green; font-family: Arial, sans-serif; padding: 20px;'>";
        echo "<h2>✅ Tables créées dans la base existante !</h2>";
        echo "<p>Base de données trouvée : <strong>" . DB_NAME . "</strong></p>";
    } else {
        echo "<div style='color: green; font-family: Arial, sans-serif; padding: 20px;'>";
        echo "<h2>✅ Base de données créée et initialisée !</h2>";
        echo "<p>Base de données créée : <strong>" . DB_NAME . "</strong></p>";
    }

    if (!empty($createdTables)) {
        echo "<p>Tables créées :</p>";
        echo "<ul>";
        foreach ($createdTables as $table) {
            $descriptions = [
                'users' => 'Utilisateurs (professeurs/étudiants)',
                'categories' => 'Catégories de questions',
                'questions' => 'Questions d\'examen',
                'examens' => 'Configurations d\'examens',
                'examen_questions' => 'Liaison examens-questions',
                'examen_sessions' => 'Sessions d\'examen',
                'logs_activite' => 'Logs d\'activité'
            ];
            echo "<li><strong>{$table}</strong> - " . ($descriptions[$table] ?? 'Table système') . "</li>";
        }
        echo "</ul>";

        echo "<p><strong>Comptes de test créés :</strong></p>";
        echo "<ul>";
        echo "<li><strong>Professeur :</strong> prof@example.com / password</li>";
        echo "<li><strong>Étudiant :</strong> student@example.com / password</li>";
        echo "</ul>";
    }

    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📊 État de la base de données</h3>";
    echo "<p><strong>Tables présentes :</strong> " . implode(', ', $existingTables) . "</p>";
    if (!empty($createdTables)) {
        echo "<p><strong>Tables ajoutées :</strong> " . implode(', ', $createdTables) . "</p>";
    }
    echo "</div>";

    echo "<p style='color: red;'><strong>⚠️ Important :</strong> Supprimez ce fichier après l'initialisation pour des raisons de sécurité !</p>";
    echo "</div>";

} catch (PDOException $e) {
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();

    echo "<div style='color: red; font-family: Arial, sans-serif; padding: 20px;'>";
    echo "<h2>❌ Erreur lors de l'initialisation</h2>";
    echo "<p><strong>Erreur :</strong> " . $errorMessage . "</p>";

    // Messages d'aide spécifiques
    if ($errorCode == 1045) {
        echo "<p><strong>💡 Solution :</strong> Vérifiez votre nom d'utilisateur et mot de passe MySQL.</p>";
    } elseif ($errorCode == 2002) {
        echo "<p><strong>💡 Solution :</strong> Vérifiez l'adresse du serveur MySQL.</p>";
    } elseif (strpos($errorMessage, 'Access denied') !== false) {
        echo "<p><strong>💡 Solution :</strong> Vérifiez les permissions de votre utilisateur MySQL.</p>";
    } elseif (strpos($errorMessage, 'already exists') !== false) {
        echo "<p><strong>💡 Note :</strong> La table existe déjà. Si vous voulez la recréer, supprimez-la d'abord.</p>";
    }

    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>🔧 Paramètres de connexion actuels :</h4>";
    echo "<ul>";
    echo "<li><strong>Serveur :</strong> " . DB_HOST . "</li>";
    echo "<li><strong>Base :</strong> " . DB_NAME . "</li>";
    echo "<li><strong>Utilisateur :</strong> " . DB_USER . "</li>";
    echo "</ul>";
    echo "<p><em>Modifiez ces valeurs dans <code>config.php</code> si nécessaire.</em></p>";
    echo "</div>";

    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: red; font-family: Arial, sans-serif; padding: 20px;'>";
    echo "<h2>❌ Erreur générale</h2>";
    echo "<p><strong>Erreur :</strong> " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez que le fichier <code>schema.sql</code> existe et est accessible.</p>";
    echo "</div>";
}
?>