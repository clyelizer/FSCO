Structure de la Base de Données
Liste de toutes les tables et leurs colonnes.

 exam_categories
8 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
nom	varchar(100)	NON		NULL	
description	text	OUI		NULL	
couleur	varchar(7)	OUI		#007bff	
ordre	int(11)	OUI	MUL	0	
created_by	int(11)	NON	MUL	NULL	
created_at	timestamp	OUI		current_timestamp()	
updated_at	timestamp	OUI		NULL	on update current_timestamp()
 exam_examen_questions
5 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
examen_id	int(11)	NON	MUL	NULL	
question_id	int(11)	NON	MUL	NULL	
ordre	int(11)	OUI	MUL	0	
points_question	decimal(5,2)	OUI		NULL	
 exam_examens
18 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
titre	varchar(200)	NON		NULL	
description	text	OUI		NULL	
duree_minutes	int(11)	OUI		60	
questions_aleatoires	tinyint(1)	OUI		0	
nombre_questions	int(11)	OUI		NULL	
melanger_questions	tinyint(1)	OUI		1	
melanger_options	tinyint(1)	OUI		1	
afficher_resultats	tinyint(1)	OUI		1	
afficher_corrections	tinyint(1)	OUI		1	
date_debut	datetime	OUI	MUL	NULL	
date_fin	datetime	OUI	MUL	NULL	
statut	enum('draft','published','closed')	OUI	MUL	draft	
is_public	tinyint(1)	OUI		0	
note_passage	int(11)	OUI		50	
created_by	int(11)	NON	MUL	NULL	
created_at	timestamp	OUI		current_timestamp()	
updated_at	timestamp	OUI		NULL	on update current_timestamp()
 exam_file_uploads
6 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
filename	varchar(255)	NON		NULL	
filepath	varchar(500)	NON		NULL	
filesize	int(11)	OUI		NULL	
uploaded_by	int(11)	NON	MUL	NULL	
uploaded_at	timestamp	OUI	MUL	current_timestamp()	
 exam_logs_activite
7 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
user_id	int(11)	OUI	MUL	NULL	
action	varchar(100)	NON	MUL	NULL	
description	text	OUI		NULL	
ip_address	varchar(45)	OUI		NULL	
user_agent	text	OUI		NULL	
created_at	timestamp	OUI	MUL	current_timestamp()	
 exam_questions
27 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
titre	varchar(200)	OUI		NULL	
enonce	text	OUI		NULL	
type_question	enum('qcm','ouverte','vrai_faux')	OUI		NULL	
options_json	text	OUI		NULL	
reponse_correcte	varchar(255)	OUI		NULL	
explication	text	OUI		NULL	
duree_secondes	int(11)	OUI		60	
categorie_id	int(11)	OUI	MUL	NULL	
type	enum('qcm','ouverte','vrai_faux')	NON	MUL	ouverte	
texte	text	NON	MUL	NULL	
reponse_ouverte	text	OUI		NULL	
option_a	varchar(255)	OUI		NULL	
option_b	varchar(255)	OUI		NULL	
option_c	varchar(255)	OUI		NULL	
option_d	varchar(255)	OUI		NULL	
reponse_qcm	set('A','B','C','D')	OUI		NULL	
reponse_vrai_faux	enum('vrai','faux')	OUI		NULL	
image_path	varchar(255)	OUI		NULL	
audio_path	varchar(255)	OUI		NULL	
points	decimal(5,2)	OUI		1.00	
duree_estimee	int(11)	OUI		60	
niveau_difficulte	enum('facile','moyen','difficile')	OUI	MUL	moyen	
published	tinyint(1)	OUI	MUL	0	
created_by	int(11)	NON	MUL	NULL	
created_at	timestamp	OUI		current_timestamp()	
updated_at	timestamp	OUI		NULL	on update current_timestamp()
 exam_sessions
18 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
examen_id	int(11)	NON	MUL	NULL	
user_id	int(11)	NON	MUL	NULL	
token_session	varchar(64)	NON	MUL	NULL	
questions_order	text	OUI		NULL	
reponses	longtext	OUI		NULL	
note_finale	decimal(5,2)	OUI		NULL	
score	decimal(5,2)	OUI		NULL	
resultat_details	longtext	OUI		NULL	
ai_corrections	longtext	OUI		NULL	
pourcentage	decimal(5,2)	OUI		NULL	
duree_reelle	int(11)	OUI		NULL	
statut	enum('en_cours','termine','abandonne','expire')	OUI	MUL	en_cours	
current_question_start_time	datetime	OUI		NULL	
current_question_index	int(11)	OUI		0	
date_debut	timestamp	OUI	MUL	current_timestamp()	
date_fin	timestamp	OUI		NULL	
dernier_progress	timestamp	OUI		current_timestamp()	on update current_timestamp()
 survey_responses
48 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
date_soumission	timestamp	OUI	MUL	current_timestamp()	
prenom	varchar(100)	OUI		NULL	
pays	varchar(100)	OUI	MUL	NULL	
adresse	text	OUI		NULL	
sexe	enum('homme','femme','autre')	OUI		NULL	
domaine	varchar(200)	OUI	MUL	NULL	
annee	varchar(50)	OUI		NULL	
etablissement	varchar(200)	OUI		NULL	
experience	varchar(100)	OUI		NULL	
cours_ia	varchar(50)	OUI		NULL	
explication_ia	text	OUI		NULL	
usage_ia	varchar(50)	OUI		NULL	
usages_ia	text	OUI		NULL	
problemes	text	OUI		NULL	
probleme_principal	text	OUI		NULL	
processus_repetitifs	text	OUI		NULL	
processus_automatise	text	OUI		NULL	
heures_economisees	varchar(50)	OUI		NULL	
quantite_donnees	varchar(50)	OUI		NULL	
competences	text	OUI		NULL	
duree_formation	varchar(100)	OUI		NULL	
format_formation	varchar(100)	OUI		NULL	
prix_formation	varchar(50)	OUI		NULL	
prix_converti	varchar(100)	OUI		NULL	
obstacles	text	OUI		NULL	
equipements	varchar(50)	OUI		NULL	
raisons_pas_solutions	text	OUI		NULL	
obstacle_principal	text	OUI		NULL	
formation_securite	varchar(50)	OUI		NULL	
niveau_securite	varchar(50)	OUI		NULL	
pratiques_securite	text	OUI		NULL	
risques_cyber	text	OUI		NULL	
importance_ia_carriere	varchar(50)	OUI		NULL	
secteur_souhaite	varchar(200)	OUI		NULL	
demande_emploi	varchar(50)	OUI		NULL	
competences_importance	text	OUI		NULL	
preparation_emploi	varchar(50)	OUI		NULL	
manque_preparation	text	OUI		NULL	
entreprises_innovantes	text	OUI		NULL	
ia_cours	varchar(50)	OUI		NULL	
ia_ameliore_enseignement	varchar(50)	OUI		NULL	
risques_ia_enseignement	varchar(50)	OUI		NULL	
risques_details	text	OUI		NULL	
recommandation_education	text	OUI		NULL	
vision_tech	text	OUI		NULL	
interets_communaute	text	OUI		NULL	
email	varchar(255)	OUI	MUL	NULL	
 user_library
7 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
user_id	int(11)	NON	MUL	NULL	
resource_id	varchar(100)	NON	MUL	NULL	
type	enum('ressource','blog')	NON	MUL	ressource	
status	enum('en_cours','termine')	OUI	MUL	en_cours	
is_favorite	tinyint(1)	OUI		0	
created_at	timestamp	OUI		current_timestamp()	
 users
12 colonnes
Champ	Type	Null	Clé	Défaut	Extra
id	int(11)	NON	PRI	NULL	auto_increment
nom	varchar(100)	NON		NULL	
email	varchar(100)	NON	UNI	NULL	
motdepasse	varchar(255)	NON		NULL	
role	enum('admin','prof','student')	NON	MUL	student	
statut	enum('active','inactive','suspended')	NON	MUL	active	
plan	enum('free','premium')	NON		free	
derniere_connexion	timestamp	OUI		NULL	
reset_token	varchar(255)	OUI		NULL	
reset_expires	datetime	OUI		NULL	
created_at	timestamp	OUI		current_timestamp()	
updated_at	timestamp	OUI		NULL	on update current_timestamp()