<?php
// Crée un tableau de tous les pays via ICU/Intl
$countryNames = ResourceBundle::create('fr', 'ICUDATA-region')->get('Countries');

// Fonction pour générer l'emoji drapeau à partir du code pays
function getFlag($countryCode) {
    $code = strtoupper($countryCode);
    $flag = '';
    for ($i = 0; $i < 2; $i++) {
        $flag .= mb_chr(ord($code[$i]) + 127397, 'UTF-8');
    }
    return $flag;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sondage - FSCo</title>
    <link rel="stylesheet" href="sondage.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">FSCo</div>
            <nav class="navbar">
                <ul>
                    <li><a href="../../index.php#services">Services</a></li>
                    <li><a href="../../index.php#formation">Formation</a></li>
                    <li><a href="../../index.php#methodologie">Méthodologie</a></li>
                    <li><a href="../../index.php#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Sondage Section -->
    <section class="survey-section">
        <div class="container">
            <div class="survey-header">
                <h1 class="section-title">📊 Enquête Complète</h1>
                <h2>Étudiants, Technologies & Avenir Professionnel</h2>
                <p class="survey-intro">Votre avis est précieux ! Cette enquête nous aide à mieux comprendre vos besoins et à adapter nos services pour répondre aux défis réels des étudiants et professionnels.</p>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <span class="progress-text">Progression: <span id="progressPercent">0%</span></span>
                </div>
            </div>

            <form id="surveyForm" action="process_survey.php" method="post" class="survey-form">
                <!-- PARTIE 1 : À propos de vous (Profil) -->
                <div class="survey-part" data-part="1">
                    <h3 class="part-title">👤 PARTIE 1 : À propos de vous (Profil)</h3>

                    <div class="form-group">
                        <label for="prenom">Votre prénom complet :</label>
                        <input type="text" id="prenom" name="prenom" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="pays">Votre pays :</label>
                        <select id="pays" name="pays" class="form-select" data-placeholder="Sélectionnez votre pays">
                            <option value="">Sélectionnez votre pays</option>
                            <?php foreach ($countryNames as $code => $name): ?>
                                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo getFlag($code) . ' ' . htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                            <option value="Autre">🌍 Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="adresse">Votre adresse (facultatif) :</label>
                        <input type="text" id="adresse" name="adresse" class="form-input">
                    </div>

                    <div class="form-group">
                        <label>Quel est votre sexe ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="sexe" value="Masculin"> Masculin</label>
                            <label class="radio-label"><input type="radio" name="sexe" value="Féminin"> Féminin</label>
                            <label class="radio-label"><input type="radio" name="sexe" value="Autre"> Autre / Ne souhaite pas répondre</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="domaine">Votre domaine d'études principal :</label>
                        <select id="domaine" name="domaine" class="form-select">
                            <option value="">Sélectionnez votre domaine</option>
                            <option value="Informatique/Ingénierie">Informatique/Ingénierie</option>
                            <option value="Gestion/Commerce/Management/Économie">Gestion/Commerce/Management/Économie</option>
                            <option value="Mathématiques/Statistiques">Mathématiques/Statistiques</option>
                            <option value="Droit/Administration Publique">Droit/Administration Publique</option>
                            <option value="Santé/Sciences">Santé/Sciences</option>
                            <option value="Électrotechnique/Télécommunications">Électrotechnique/Télécommunications</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>En quelle année êtes-vous ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="annee" value="Licence 1"> Licence 1</label>
                            <label class="radio-label"><input type="radio" name="annee" value="Licence 2"> Licence 2</label>
                            <label class="radio-label"><input type="radio" name="annee" value="Licence 3"> Licence 3</label>
                            <label class="radio-label"><input type="radio" name="annee" value="Master 1"> Master 1</label>
                            <label class="radio-label"><input type="radio" name="annee" value="Master 2"> Master 2</label>
                            <label class="radio-label"><input type="radio" name="annee" value="Autre"> Autre</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Où étudiez-vous ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="etablissement" value="Université publique"> Université publique</label>
                            <label class="radio-label"><input type="radio" name="etablissement" value="Établissement privé"> Établissement privé</label>
                            <label class="radio-label"><input type="radio" name="etablissement" value="Établissement privé"> Établissement privé & publique</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Avez-vous une expérience professionnelle ou de stage ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="experience" value="Oui"> Oui</label>
                            <label class="radio-label"><input type="radio" name="experience" value="Non"> Non</label>
                        </div>
                    </div>
                </div>

                <!-- PARTIE 2 : Perception et Usage de l'IA et de la Data -->
                <div class="survey-part" data-part="2">
                    <h3 class="part-title">🤖 PARTIE 2 : Perception et Usage de l'IA et de la Data</h3>

                    <div class="form-group">
                        <label>Avez-vous suivi des cours sur l'IA ou la data science dans votre cursus ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="cours_ia" value="Oui, un cours complet ou module dédié"> Oui, un cours complet ou module dédié</label>
                            <label class="radio-label"><input type="radio" name="cours_ia" value="Oui, des notions intégrées dans d'autres cours"> Oui, des notions intégrées dans d'autres cours</label>
                            <label class="radio-label"><input type="radio" name="cours_ia" value="Non, jamais abordé"> Non, jamais abordé</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="explication_ia">Comment expliqueriez-vous l'IA à quelqu'un qui n'en a jamais entendu parler ?</label>
                        <textarea id="explication_ia" name="explication_ia" rows="4" class="form-textarea" placeholder="Votre explication..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Avez-vous utilisé des outils d'IA dans votre quotidien ? (Ex: ChatGPT, Copilot, etc.)</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="usage_ia" value="Oui, régulièrement"> Oui, régulièrement</label>
                            <label class="radio-label"><input type="radio" name="usage_ia" value="Oui, de temps en temps"> Oui, de temps en temps</label>
                            <label class="radio-label"><input type="radio" name="usage_ia" value="Non, jamais"> Non, jamais</label>
                            <label class="radio-label"><input type="radio" name="usage_ia" value="Je ne sais pas si j'en ai utilisé"> Je ne sais pas si j'en ai utilisé</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pour quels usages avez-vous utilisé l'IA ?</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="usages_ia[]" value="Études"> Études</label>
                            <label class="checkbox-label"><input type="checkbox" name="usages_ia[]" value="Programmation"> Programmation</label>
                            <label class="checkbox-label"><input type="checkbox" name="usages_ia[]" value="Divertissement/loisirs"> Divertissement/loisirs</label>
                            <label class="checkbox-label"><input type="checkbox" name="usages_ia[]" value="Créativité"> Créativité</label>
                            <label class="checkbox-label"><input type="checkbox" name="usages_ia[]" value="Travail professionnel/stage"> Travail professionnel/stage</label>
                        </div>
                    </div>
                </div>

                <!-- PARTIE 3 : Vos Défis et Problématiques -->
                <div class="survey-part" data-part="3">
                    <h3 class="part-title">🎯 PARTIE 3 : Vos Défis et Problématiques</h3>

                    <div class="form-group">
                        <label>Quels problèmes vous posent problème MAINTENANT dans vos études ou travail ?</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="problemes[]" value="Temps perdu sur tâches répétitives"> Temps perdu sur tâches répétitives</label>
                            <label class="checkbox-label"><input type="checkbox" name="problemes[]" value="Données désorganisées"> Données désorganisées</label>
                            <label class="checkbox-label"><input type="checkbox" name="problemes[]" value="Difficultés à prendre des bonnes décisions"> Difficultés à prendre des bonnes décisions</label>
                            <label class="checkbox-label"><input type="checkbox" name="problemes[]" value="Manque de compétences tech/data"> Manque de compétences tech/data</label>
                            <label class="checkbox-label"><input type="checkbox" name="problemes[]" value="Peur de ne pas trouver emploi"> Peur de ne pas trouver emploi</label>
                            <label class="checkbox-label"><input type="checkbox" name="problemes[]" value="Aucun blocage"> Aucun blocage</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="probleme_principal">Décrivez le problème qui vous pose LE PLUS DE PROBLÈME :</label>
                        <textarea id="probleme_principal" name="probleme_principal" rows="4" class="form-textarea" placeholder="Décrivez votre problème principal..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Quels processus RÉPÉTITIFS mangent votre temps ?</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="processus_repetitifs[]" value="Générer des rapports"> Générer des rapports</label>
                            <label class="checkbox-label"><input type="checkbox" name="processus_repetitifs[]" value="Saisie/copie de données"> Saisie/copie de données</label>
                            <label class="checkbox-label"><input type="checkbox" name="processus_repetitifs[]" value="Envoyer des emails/notifications"> Envoyer des emails/notifications</label>
                            <label class="checkbox-label"><input type="checkbox" name="processus_repetitifs[]" value="Classer/archiver des fichiers"> Classer/archiver des fichiers</label>
                            <label class="checkbox-label"><input type="checkbox" name="processus_repetitifs[]" value="Vérifier/valider des informations"> Vérifier/valider des informations</label>
                            <label class="checkbox-label"><input type="checkbox" name="processus_repetitifs[]" value="Aucun processus automatisable"> Aucun processus automatisable</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="processus_automatise">Quel processus automatisé vous changerait LE PLUS la vie ?</label>
                        <textarea id="processus_automatise" name="processus_automatise" rows="3" class="form-textarea" placeholder="Décrivez le processus..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="heures_economisees">Combien d'heures par semaine pensez-vous que cela vous économiserait ?</label>
                        <input type="number" id="heures_economisees" name="heures_economisees" min="0" max="168" class="form-input" placeholder="Nombre d'heures">
                    </div>

                    <div class="form-group">
                        <label>Dans votre contexte (études, stage), travaillez-vous avec combien de données ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="quantite_donnees" value="Beaucoup de données"> Beaucoup de données</label>
                            <label class="radio-label"><input type="radio" name="quantite_donnees" value="Quantité moyenne"> Quantité moyenne</label>
                            <label class="radio-label"><input type="radio" name="quantite_donnees" value="Peu de données"> Peu de données</label>
                            <label class="radio-label"><input type="radio" name="quantite_donnees" value="Je ne sais pas / pas de contexte data"> Je ne sais pas / pas de contexte data</label>
                        </div>
                    </div>
                </div>

                <!-- PARTIE 4 : Formation et Besoins en Compétences -->
                <div class="survey-part" data-part="4">
                    <h3 class="part-title">🎓 PARTIE 4 : Formation et Besoins en Compétences</h3>

                    <div class="form-group">
                        <label>Quelles compétences voulez-vous acquérir EN PRIORITÉ ?</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Outils Microsoft"> Outils Microsoft(Excel,World,Powerpoint,...)</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Developpement web"> Developpement web</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="SQL"> SQL</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Python/automatisation"> Python/automatisation</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Analytics/BI"> Analytics/BI</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Bases de l'IA"> Bases de l'IA</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="IA avancee"> IA avancée</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="cybersecurite"> cybersecurite</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Consulting"> Consulting</label>
                            <label class="checkbox-label"><input type="checkbox" name="competences[]" value="Aucune"> Aucune</label>
                            
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Format de formation : vous préférez quoi ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="format_formation" value="Intensif"> Intensif</label>
                            <label class="radio-label"><input type="radio" name="format_formation" value="Soir/weekend"> Soir/weekend</label>
                            <label class="radio-label"><input type="radio" name="format_formation" value="En ligne"> En ligne</label>
                            <label class="radio-label"><input type="radio" name="format_formation" value="Mixte"> Mixte (ligne&presentiel)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Si vous deviez suivre une formation payant, sur quelle duree la voudriez-vous ?</label>
                        <input type="text" name="" id="" placeholder='2 mois'>
    
                    </div>   
                
               
                    <div class="form-group">
                        <label>Combien seriez-vous prêt à payer pour une formation en ligne?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="prix_formation" value="1 000 - 2 000"> 1 000 - 2 000<span id="currency-display-1">[Unité monétaire locale]</span></label>
                            <label class="radio-label"><input type="radio" name="prix_formation" value="3 000 - 5 000"> 3 000 - 5 000 <span id="currency-display-1">[Unité monétaire locale]</span></label>
                            <label class="radio-label"><input type="radio" name="prix_formation" value="5 000 - 10 000"> 15 000 - 50 000 <span id="currency-display-2">[Unité monétaire locale]</span></label>
                            <label class="radio-label"><input type="radio" name="prix_formation" value="50 000 - 60 000"> 50 000 - 100 000 <span id="currency-display-3">[Unité monétaire locale]</span></label>
                            <label class="radio-label"><input type="radio" name="prix_formation" value="100 000+"> 100 000+ <span id="currency-display-4">[Unité monétaire locale]</span></label>
                            <label class="radio-label"><input type="radio" name="prix_formation" value="Gratuit seulement"> Gratuit seulement</label>

                        </div>
                    </div>
                </div>

                <!-- PARTIE 5 : Obstacles (Apprentissage et Marché) -->
                <div class="survey-part" data-part="5">
                    <h3 class="part-title">🚧 PARTIE 5 : Obstacles (Apprentissage et Marché)</h3>

                    <div class="form-group">
                        <label>Quels sont les principaux obstacles que vous rencontrez pour apprendre (IA, data, prog) ?</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Coûts trop élevés"> Coûts trop élevés</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Accès à internet insuffisant"> Accès à internet insuffisant</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Équipements informatiques inadéquats"> Équipements informatiques inadéquats</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Enseignants pas suffisamment formés"> Enseignants pas suffisamment formés</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Programmes d'études obsolètes"> Programmes d'études obsolètes</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Ressources en anglais"> Ressources en anglais</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Pas assez de temps"> Pas assez de temps</label>
                            <label class="checkbox-label"><input type="checkbox" name="obstacles[]" value="Manque de motivation"> Manque de motivation</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Avez-vous accès à des équipements informatiques adéquats pour vos études ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="equipements" value="Oui, j'ai tout"> Oui, j'ai tout</label>
                            <label class="radio-label"><input type="radio" name="equipements" value="Plutôt oui"> Plutôt oui</label>
                            <label class="radio-label"><input type="radio" name="equipements" value="Partiellement"> Partiellement</label>
                            <label class="radio-label"><input type="radio" name="equipements" value="Non, accès très limité"> Non, accès très limité</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pourquoi n'existe-t-il pas (ou très peu) de solutions data/IA dans votre région/pays ? (Votre avis)</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Trop cher"> Trop cher</label>
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Manque de talent/expertise"> Manque de talent/expertise</label>
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Infrastructure internet instable"> Infrastructure internet instable</label>
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Mentalité"> Mentalité</label>
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Problèmes de régulation"> Problèmes de régulation</label>
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Mauvaise gouvernance des données"> Mauvaise gouvernance des données</label>
                            <label class="checkbox-label"><input type="checkbox" name="raisons_pas_solutions[]" value="Pas de vraie DEMANDE"> Pas de vraie DEMANDE</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="obstacle_principal">Quel est LE PRINCIPAL obstacle (sur le marché local) à votre avis ?</label>
                        <textarea id="obstacle_principal" name="obstacle_principal" rows="3" class="form-textarea" placeholder="Décrivez l'obstacle principal..."></textarea>
                    </div>
                </div>

                <!-- PARTIE 6 : Cybersécurité -->
                <div class="survey-part" data-part="6">
                    <h3 class="part-title">🔒 PARTIE 6 : Cybersécurité</h3>

                    <div class="form-group">
                        <label>Avez-vous reçu une formation en sécurité informatique ou cybersécurité ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="formation_securite" value="Oui, spécialisée"> Oui, spécialisée</label>
                            <label class="radio-label"><input type="radio" name="formation_securite" value="Oui, notions basiques"> Oui, notions basiques</label>
                            <label class="radio-label"><input type="radio" name="formation_securite" value="Non, jamais rien appris"> Non, jamais rien appris</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Comment évaluez-vous votre propre niveau de connaissance en sécurité informatique ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="niveau_securite" value="Excellent"> Excellent</label>
                            <label class="radio-label"><input type="radio" name="niveau_securite" value="Bon"> Bon</label>
                            <label class="radio-label"><input type="radio" name="niveau_securite" value="Moyen"> Moyen</label>
                            <label class="radio-label"><input type="radio" name="niveau_securite" value="Faible"> Faible</label>
                            <label class="radio-label"><input type="radio" name="niveau_securite" value="Très faible"> Très faible</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Utilisez-vous des pratiques de sécurité informatique au quotidien ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="pratiques_securite" value="Oui, systématiquement"> Oui, systématiquement</label>
                            <label class="radio-label"><input type="radio" name="pratiques_securite" value="Souvent"> Souvent</label>
                            <label class="radio-label"><input type="radio" name="pratiques_securite" value="Parfois"> Parfois</label>
                            <label class="radio-label"><input type="radio" name="pratiques_securite" value="Rarement"> Rarement</label>
                            <label class="radio-label"><input type="radio" name="pratiques_securite" value="Jamais"> Jamais</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="risques_cyber">Quels sont les principaux risques de cybersécurité que vous identifiez dans votre pays/région ?</label>
                        <textarea id="risques_cyber" name="risques_cyber" rows="4" class="form-textarea" placeholder="Décrivez les risques..."></textarea>
                    </div>
                </div>

                <!-- PARTIE 7 : Avenir Professionnel et Marché de l'Emploi -->
                <div class="survey-part" data-part="7">
                    <h3 class="part-title">💼 PARTIE 7 : Avenir Professionnel et Marché de l'Emploi</h3>

                    <div class="form-group">
                        <label>Pensez-vous que l'IA et la data science seront importantes pour votre carrière future ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="importance_ia_carriere" value="Oui, absolument essentiel"  > Oui, absolument essentiel</label>
                            <label class="radio-label"><input type="radio" name="importance_ia_carriere" value="Probablement utile"> Probablement utile</label>
                            <label class="radio-label"><input type="radio" name="importance_ia_carriere" value="Peut-être"> Peut-être</label>
                            <label class="radio-label"><input type="radio" name="importance_ia_carriere" value="Probablement pas"> Probablement pas</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="secteur_souhaite">Secteur où vous voulez travailler :</label>
                        <select id="secteur_souhaite" name="secteur_souhaite" class="form-select">
                            <option value="">Sélectionnez un secteur</option>
                            <option value="Tech/Digital/Startups">Tech/Digital/Startups</option>
                            <option value="Finance/Banque/Assurance">Finance/Banque/Assurance</option>
                            <option value="Secteur public/Administration">Secteur public/Administration</option>
                            <option value="Santé/Éducation">Santé/Éducation</option>
                            <option value="Agriculture/Commerce/Industrie">Agriculture/Commerce/Industrie</option>
                            <option value="Entrepreneuriat/Créer ma propre entreprise">Entrepreneuriat/Créer ma propre entreprise</option>
                            <option value="Je ne sais pas encore">Je ne sais pas encore</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pensez-vous qu'il y a une vraie demande d'emploi dans votre région/pays pour ces profils (Data/IA) ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="demande_emploi" value="Oui, beaucoup"> Oui, beaucoup</label>
                            <label class="radio-label"><input type="radio" name="demande_emploi" value="Moyen"> Moyen</label>
                            <label class="radio-label"><input type="radio" name="demande_emploi" value="Peu"> Peu</label>
                            <label class="radio-label"><input type="radio" name="demande_emploi" value="Je ne sais pas"> Je ne sais pas</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pour être compétitif dans 5 ans, classez ces compétences par importance :</label>
                        <div class="ranking-group">
                            <div class="ranking-item">
                                <select name="competences_importance[1]"   class="form-select">
                                    <option value="">1ère priorité</option>
                                    <option value="Compétences techniques">Compétences techniques</option>
                                    <option value="Business sense">Business sense</option>
                                    <option value="Soft skills">Soft skills</option>
                                    <option value="Understanding AI">Understanding AI</option>
                                </select>
                            </div>
                            <div class="ranking-item">
                                <select name="competences_importance[2]"   class="form-select">
                                    <option value="">2ème priorité</option>
                                    <option value="Compétences techniques">Compétences techniques</option>
                                    <option value="Business sense">Business sense</option>
                                    <option value="Soft skills">Soft skills</option>
                                    <option value="Understanding AI">Understanding AI</option>
                                </select>
                            </div>
                            <div class="ranking-item">
                                <select name="competences_importance[3]"   class="form-select">
                                    <option value="">3ème priorité</option>
                                    <option value="Compétences techniques">Compétences techniques</option>
                                    <option value="Business sense">Business sense</option>
                                    <option value="Soft skills">Soft skills</option>
                                    <option value="Understanding AI">Understanding AI</option>
                                </select>
                            </div>
                            <div class="ranking-item">
                                <select name="competences_importance[4]"   class="form-select">
                                    <option value="">4ème priorité</option>
                                    <option value="Compétences techniques">Compétences techniques</option>
                                    <option value="Business sense">Business sense</option>
                                    <option value="Soft skills">Soft skills</option>
                                    <option value="Understanding AI">Understanding AI</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pensez-vous que les établissements d'enseignement préparent bien les étudiants à trouver un emploi ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="preparation_emploi" value="Oui, très bien"  > Oui, très bien</label>
                            <label class="radio-label"><input type="radio" name="preparation_emploi" value="Plutôt bien"> Plutôt bien</label>
                            <label class="radio-label"><input type="radio" name="preparation_emploi" value="Moyennement"> Moyennement</label>
                            <label class="radio-label"><input type="radio" name="preparation_emploi" value="Mal"> Mal</label>
                            <label class="radio-label"><input type="radio" name="preparation_emploi" value="Très mal"> Très mal</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="manque_preparation">Qu'est-ce qui manque pour mieux préparer les étudiants à l'emploi ?</label>
                        <textarea id="manque_preparation" name="manque_preparation" rows="4" class="form-textarea" placeholder="Vos suggestions..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="entreprises_innovantes">Nommez 3 entreprises / organisations (locales ou régionales) que vous trouvez innovantes :</label>
                        <textarea id="entreprises_innovantes" name="entreprises_innovantes" rows="3" class="form-textarea" placeholder="1. Nom entreprise&#10;2. Nom entreprise&#10;3. Nom entreprise"></textarea>
                    </div>
                </div>

                <!-- PARTIE 8 : IA dans l'Enseignement -->
                <div class="survey-part" data-part="8">
                    <h3 class="part-title">🏫 PARTIE 8 : IA dans l'Enseignement</h3>

                    <div class="form-group">
                        <label>Vos enseignants utilisent-ils des outils d'IA en cours ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="ia_cours" value="Oui, régulièrement"  > Oui, régulièrement</label>
                            <label class="radio-label"><input type="radio" name="ia_cours" value="Oui, occasionnellement"> Oui, occasionnellement</label>
                            <label class="radio-label"><input type="radio" name="ia_cours" value="Non, jamais vu"> Non, jamais vu</label>
                            <label class="radio-label"><input type="radio" name="ia_cours" value="Je ne sais pas"> Je ne sais pas</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pensez-vous que l'IA pourrait améliorer la qualité de l'enseignement ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="ia_ameliore_enseignement" value="Oui, beaucoup"  > Oui, beaucoup</label>
                            <label class="radio-label"><input type="radio" name="ia_ameliore_enseignement" value="Oui, un peu"> Oui, un peu</label>
                            <label class="radio-label"><input type="radio" name="ia_ameliore_enseignement" value="Neutre"> Neutre</label>
                            <label class="radio-label"><input type="radio" name="ia_ameliore_enseignement" value="Non, ça risque de faire du mal"> Non, ça risque de faire du mal</label>
                            <label class="radio-label"><input type="radio" name="ia_ameliore_enseignement" value="Je ne sais pas"> Je ne sais pas</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Voyez-vous des risques ou des problèmes à l'utilisation de l'IA en enseignement ?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="risques_ia_enseignement" value="Oui, plusieurs risques sérieux"  > Oui, plusieurs risques sérieux</label>
                            <label class="radio-label"><input type="radio" name="risques_ia_enseignement" value="Oui, quelques-uns"> Oui, quelques-uns</label>
                            <label class="radio-label"><input type="radio" name="risques_ia_enseignement" value="Peu de risques"> Peu de risques</label>
                            <label class="radio-label"><input type="radio" name="risques_ia_enseignement" value="Non, je ne vois pas de problème"> Non, je ne vois pas de problème</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="risques_details">Quels seraient ces risques ? (Ex: fraude, déshumanisation...)</label>
                        <textarea id="risques_details" name="risques_details" rows="4" class="form-textarea" placeholder="Décrivez les risques..."></textarea>
                    </div>
                </div>

                <!-- PARTIE 9 : Réflexion Finale et Contact -->
                <div class="survey-part" data-part="9">
                    <h3 class="part-title">🤔 PARTIE 9 : Réflexion Finale et Contact</h3>

                    <div class="form-group">
                        <label for="recommandation_education">Si vous aviez une seule recommandation pour les responsables de l'éducation de votre pays, ce serait quoi ?</label>
                        <textarea id="recommandation_education" name="recommandation_education" rows="4" class="form-textarea" placeholder="Votre recommandation..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="vision_tech">Comment voyez-vous le futur de la tech dans votre région/pays ? (Vision, optimisme, défis...)</label>
                        <textarea id="vision_tech" name="vision_tech" rows="4" class="form-textarea" placeholder="Votre vision..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Seriez-vous intéressé par (pour créer une communauté) :</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label"><input type="checkbox" name="interets_communaute[]" value="Être formé gratuitement/bêta-testeur"> Être formé gratuitement/bêta-testeur</label>
                            <label class="checkbox-label"><input type="checkbox" name="interets_communaute[]" value="Devenir ambassadeur"> Devenir ambassadeur</label>
                            <label class="checkbox-label"><input type="checkbox" name="interets_communaute[]" value="Avoir un diagnostic gratuit"> Avoir un diagnostic gratuit</label>
                            <label class="checkbox-label"><input type="checkbox" name="interets_communaute[]" value="Peut-être cofonder"> Peut-être cofonder</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Votre email (pour qu'on vous recontacte) :</label>
                        <input type="email" id="email" name="email"   class="form-input" placeholder="votre.email@exemple.com">
                    </div>
                </div>

                <!-- Navigation buttons -->
                <div class="form-navigation">
                    <button type="button" id="prevBtn" class="btn btn-secondary" style="display: none;">Précédent</button>
                    <button type="button" id="nextBtn" class="btn btn-primary">Suivant</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-huge" style="display: none;">Envoyer l'enquête</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('surveyForm');
            const parts = document.querySelectorAll('.survey-part');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            const progressFill = document.getElementById('progressFill');
            const progressPercent = document.getElementById('progressPercent');
            const paysSelect = document.getElementById('pays');

            let currentPart = 0;
            const totalParts = parts.length;

            // Currency conversion rates (MAD as base)
            const currencyRates = {
                'Maroc': { symbol: 'MAD', rate: 1 },
                'Algérie': { symbol: 'DZD', rate: 13.5 },
                'Tunisie': { symbol: 'TND', rate: 0.32 },
                'France': { symbol: 'EUR', rate: 0.088 },
                'Canada': { symbol: 'CAD', rate: 0.12 },
                'Belgique': { symbol: 'EUR', rate: 0.088 },
                'Suisse': { symbol: 'CHF', rate: 0.094 },
                'Espagne': { symbol: 'EUR', rate: 0.088 },
                'Italie': { symbol: 'EUR', rate: 0.088 },
                'Portugal': { symbol: 'EUR', rate: 0.088 },
                'États-Unis': { symbol: 'USD', rate: 0.099 },
                'Royaume-Uni': { symbol: 'GBP', rate: 0.077 },
                'Allemagne': { symbol: 'EUR', rate: 0.088 }
            };

            function updateCurrencyDisplays(country) {
                if (currencyRates[country]) {
                    const rate = currencyRates[country].rate;
                    const symbol = currencyRates[country].symbol;

                    // Update all currency displays
                    for (let i = 1; i <= 4; i++) {
                        const display = document.getElementById(`currency-display-${i}`);
                        if (display) {
                            const range = display.previousSibling.textContent.trim();
                            if (range.includes('-')) {
                                const [min, max] = range.split('-').map(s => s.replace(/\s+/g, ''));
                                const minConverted = Math.round(parseInt(min.replace(/\D/g, '')) * rate);
                                const maxConverted = Math.round(parseInt(max.replace(/\D/g, '')) * rate);
                                display.textContent = `[${minConverted} - ${maxConverted} ${symbol}]`;
                            } else if (range.includes('+')) {
                                const amount = parseInt(range.replace(/\D/g, ''));
                                const converted = Math.round(amount * rate);
                                display.textContent = `[${converted}+ ${symbol}]`;
                            }
                        }
                    }
                }
            }

            // Update currency when country changes
            paysSelect.addEventListener('change', function() {
                updateCurrencyDisplays(this.value);
            });

            function showPart(index) {
                parts.forEach((part, i) => {
                    part.style.display = i === index ? 'block' : 'none';
                });

                prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
                nextBtn.style.display = index === totalParts - 1 ? 'none' : 'inline-block';
                submitBtn.style.display = index === totalParts - 1 ? 'inline-block' : 'none';

                const progress = ((index + 1) / totalParts) * 100;
                progressFill.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';

                // Scroll to top of form
                document.querySelector('.survey-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            nextBtn.addEventListener('click', function() {
                if (currentPart < totalParts - 1) {
                    currentPart++;
                    showPart(currentPart);
                }
            });

            prevBtn.addEventListener('click', function() {
                if (currentPart > 0) {
                    currentPart--;
                    showPart(currentPart);
                }
            });

            // Initialize first part
            showPart(0);

            // Form validation
            form.addEventListener('submit', function(e) {
                // Only validate visible/current part fields
                const currentPart = parts[currentPart];
                const  Fields = currentPart.querySelectorAll('[ ]');
                let isValid = true;

                 Fields.forEach(field => {
                    if (field.type === 'radio') {
                        // Check if any radio button in the group is checked
                        const radioGroup = form.querySelectorAll(`input[name="${field.name}"]`);
                        const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                        if (!isChecked) {
                            isValid = false;
                            // Highlight the first radio button in the group
                            field.parentElement.style.border = '2px solid #dc2626';
                            field.parentElement.style.borderRadius = '8px';
                            field.parentElement.style.padding = '8px';
                        } else {
                            field.parentElement.style.border = 'none';
                            field.parentElement.style.padding = '0';
                        }
                    } else if (field.type === 'checkbox') {
                        // For checkbox groups, at least one should be checked
                        const checkboxGroup = form.querySelectorAll(`input[name="${field.name}[]"]`);
                        const isChecked = Array.from(checkboxGroup).some(checkbox => checkbox.checked);
                        if (!isChecked) {
                            isValid = false;
                            field.parentElement.style.border = '2px solid #dc2626';
                            field.parentElement.style.borderRadius = '8px';
                            field.parentElement.style.padding = '8px';
                        } else {
                            field.parentElement.style.border = 'none';
                            field.parentElement.style.padding = '0';
                        }
                    } else if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#dc2626';
                        field.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                    } else {
                        field.style.borderColor = '#d1d5db';
                        field.style.boxShadow = 'none';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires de cette partie.');
                    // Scroll to first invalid field
                    const firstInvalid = currentPart.querySelector('[ ]:invalid, .radio-group[style*="solid"], .checkbox-group[style*="solid"]');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Real-time validation feedback
            form.addEventListener('input', function(e) {
                if (e.target.hasAttribute(' ') && e.target.value.trim()) {
                    e.target.style.borderColor = '#d1d5db';
                    e.target.style.boxShadow = 'none';
                }
            });

            // Handle radio button changes
            form.addEventListener('change', function(e) {
                if (e.target.type === 'radio') {
                    // Remove highlighting from radio group
                    const radioGroup = form.querySelectorAll(`input[name="${e.target.name}"]`);
                    radioGroup.forEach(radio => {
                        radio.parentElement.style.border = 'none';
                        radio.parentElement.style.padding = '0';
                    });
                }
            });

            // Handle checkbox changes
            form.addEventListener('change', function(e) {
                if (e.target.type === 'checkbox') {
                    const checkboxGroup = form.querySelectorAll(`input[name="${e.target.name}"]`);
                    const isAnyChecked = Array.from(checkboxGroup).some(cb => cb.checked);
                    if (isAnyChecked) {
                        checkboxGroup.forEach(cb => {
                            cb.parentElement.style.border = 'none';
                            cb.parentElement.style.padding = '0';
                        });
                    }
                }
            });
        });
    </script>

    <!-- Choices.js for searchable select -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js for country select
            const paysSelect = document.getElementById('pays');
            const choices = new Choices(paysSelect, {
                searchEnabled: true,
                searchPlaceholderValue: 'Tapez pour rechercher un pays...',
                itemSelectText: 'Sélectionner',
                noResultsText: 'Aucun pays trouvé',
                noChoicesText: 'Aucun pays disponible',
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Sélectionnez votre pays',
                searchResultLimit: 5,
                renderChoiceLimit: -1
            });

            // Custom styling for Choices.js
            const choicesContainer = paysSelect.parentElement;
            const choicesElement = choicesContainer.querySelector('.choices');
            if (choicesElement) {
                choicesElement.style.width = '100%';
                choicesElement.style.maxWidth = '100%';
            }
        });
    </script>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">FSCo</div>
                    <p>Formation Suivi Conseil - Votre partenaire digital</p>
                </div>
                <div class="footer-links">
                    <h4>Liens utiles</h4>
                    <ul>
                        <li><a href="../../index.php#services">Services</a></li>
                        <li><a href="../../index.php#formation">Formation</a></li>
                        <li><a href="../../index.php#methodologie">Méthodologie</a></li>
                        <li><a href="sondage.php">Sondage</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p>Email: contact@fsc.com</p>
                    <p>Téléphone: +212 6 XX XX XX XX</p>
                    <p>Adresse: Casablanca, Maroc</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>