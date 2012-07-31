CREATE TABLE gt_agora_info (
  nom tinytext,
  description text,
  adresse_web text,
  langue tinytext,
  timezone tinytext,
  fond_ecran tinytext,
  logo tinytext,
  logo_url tinytext,
  mise_a_jour int unsigned,
  mise_a_jour_effective int unsigned,
  version_agora tinytext,
  skin tinytext,
  edition_popup tinyint,
  editeur_text_mode tinytext,
  footer_html text,
  messenger_desactive tinyint,
  libelle_module tinytext,
  tri_personnes ENUM('nom','prenom'),
  logs_jours_conservation smallint
);

INSERT INTO gt_agora_info VALUES ('Agora-Project', 'Espace de partage et de travail collaboratif', null, 'francais', '1.00', null, null, 'http://www.agora-project.net', null, null, null, 'blanc', null, null, null, null, 'page','prenom','15');


CREATE TABLE gt_module (
  nom tinytext,
  module_dossier_fichier tinytext
);

INSERT INTO gt_module VALUES ('tableau_bord', 'module_tableau_bord');
INSERT INTO gt_module VALUES ('utilisateurs', 'module_utilisateurs');
INSERT INTO gt_module VALUES ('agenda', 'module_agenda');
INSERT INTO gt_module VALUES ('fichier', 'module_fichier');
INSERT INTO gt_module VALUES ('forum', 'module_forum');
INSERT INTO gt_module VALUES ('tache', 'module_tache');
INSERT INTO gt_module VALUES ('lien', 'module_lien');
INSERT INTO gt_module VALUES ('contact', 'module_contact');
INSERT INTO gt_module VALUES ('mail', 'module_mail');


CREATE TABLE gt_espace (
  id_espace int unsigned auto_increment,
  nom tinytext,
  description text,
  password tinytext,
  inscription_users tinyint,
  fond_ecran tinytext,
  PRIMARY KEY (id_espace)
);


CREATE TABLE gt_jointure_espace_module (
  id_espace int unsigned,
  nom_module tinytext,
  classement tinyint,
  options text
);

INSERT INTO gt_jointure_espace_module VALUES (1, 'tableau_bord', 1, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'fichier', 2, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'agenda', 3, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'forum', 4, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'contact', 5, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'lien', 6, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'tache', 7, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'utilisateurs', 8, null);
INSERT INTO gt_jointure_espace_module VALUES (1, 'mail', 9, null);


CREATE TABLE gt_utilisateur (
  id_utilisateur int unsigned auto_increment,
  civilite tinytext,
  nom tinytext,
  prenom tinytext,
  identifiant tinytext,
  pass tinytext,
  photo tinytext,
  adresse text,
  codepostal tinytext,
  ville tinytext,
  pays tinytext,
  telephone tinytext,
  telmobile tinytext,
  fax tinytext,
  mail text,
  siteweb text,
  competences text,
  hobbies text,
  fonction text,
  societe_organisme text,
  commentaire text,
  date_crea datetime,
  derniere_connexion int unsigned,
  precedente_connexion int unsigned,
  admin_general tinytext,
  langue tinytext,
  espace_connexion tinytext,
  agenda_desactive tinyint,
  id_newpassword tinytext,
  PRIMARY KEY (id_utilisateur)
);



CREATE TABLE gt_jointure_espace_utilisateur (
  id_espace int unsigned,
  id_utilisateur int unsigned default null,
  tous_utilisateurs tinyint unsigned default null,
  invites tinyint unsigned default null,
  droit tinytext,
  envoi_invitation tinyint unsigned
);

INSERT INTO gt_jointure_espace_utilisateur VALUES (1, null, 1, null, 1, 1);



CREATE TABLE gt_utilisateur_adresse_ip (
  adresse_ip tinytext,
  id_utilisateur int unsigned
);


CREATE TABLE gt_utilisateur_livecounter (
  id_utilisateur int unsigned,
  adresse_ip tinytext,
  date_verif int
);


CREATE TABLE gt_utilisateur_messenger (
  id_utilisateur_expediteur int unsigned,
  id_utilisateur_destinataires text,
  message text,
  couleur tinytext,
  date int
);


CREATE TABLE gt_jointure_messenger_utilisateur (
  id_utilisateur_messenger int unsigned,
  tous_utilisateurs tinyint unsigned,
  id_utilisateur int unsigned
);

INSERT INTO gt_jointure_messenger_utilisateur VALUES (1, 1, null);



CREATE TABLE gt_jointure_objet (
  type_objet text,
  id_objet int unsigned,
  id_espace int unsigned,
  target tinytext,
  droit float unsigned
);

INSERT INTO gt_jointure_objet VALUES ('actualite', 1, 1, 'tous', 1);
INSERT INTO gt_jointure_objet VALUES ('lien_dossier', 1, 1, 'tous', 1);
INSERT INTO gt_jointure_objet VALUES ('fichier_dossier', 1, 1, 'tous', 1);
INSERT INTO gt_jointure_objet VALUES ('contact_dossier', 1, 1, 'tous', 1);
INSERT INTO gt_jointure_objet VALUES ('tache_dossier', 1, 1, 'tous', 1);


CREATE TABLE gt_tache_dossier (
  id_dossier int unsigned auto_increment,
  id_dossier_parent int unsigned,
  nom tinytext,
  description text,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_dossier, id_dossier_parent)
);

INSERT INTO gt_tache_dossier VALUES (1, 0, null, null, null, null, null, null, null, null);


CREATE TABLE gt_tache (
  id_tache int unsigned auto_increment,
  id_dossier int unsigned,
  titre text,
  description text,
  priorite tinytext,
  avancement tinyint,
  charge_jour_homme float,
  budget_disponible int unsigned,
  budget_engage int unsigned,
  devise tinytext,
  date_debut datetime,
  date_fin datetime,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_tache, id_dossier)
);


CREATE TABLE gt_tache_responsable (
  id_tache int unsigned,
  id_utilisateur int unsigned,
  PRIMARY KEY (id_tache, id_utilisateur)
);


CREATE TABLE gt_lien_dossier (
  id_dossier int unsigned auto_increment,
  id_dossier_parent int unsigned,
  nom tinytext,
  description text,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_dossier, id_dossier_parent)
);

INSERT INTO gt_lien_dossier VALUES (1, 0, null, null, null, null, null, null, null, null);



CREATE TABLE gt_lien (
  id_lien int unsigned auto_increment,
  id_dossier int unsigned,
  adresse text,
  description text,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_lien, id_dossier)
);


CREATE TABLE gt_contact_dossier (
  id_dossier int unsigned auto_increment,
  id_dossier_parent int unsigned,
  nom tinytext,
  description text,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_dossier, id_dossier_parent)
);

INSERT INTO gt_contact_dossier VALUES (1, 0, null, null, null, null, null, null, null, null);



CREATE TABLE gt_contact (
  id_contact int unsigned auto_increment,
  id_dossier int unsigned,
  civilite tinytext,
  nom tinytext,
  prenom tinytext,
  photo tinytext,
  societe_organisme text,
  fonction text,
  adresse text,
  codepostal tinytext,
  ville tinytext,
  pays tinytext,
  telephone tinytext,
  telmobile tinytext,
  fax tinytext,
  mail text,
  siteweb text,
  competences text,
  hobbies text,
  commentaire text,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_contact, id_dossier)
);


CREATE TABLE gt_fichier_dossier (
  id_dossier int unsigned auto_increment,
  id_dossier_parent int unsigned,
  nom tinytext,
  nom_reel tinytext,
  description text,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_dossier, id_dossier_parent)
);

INSERT INTO gt_fichier_dossier VALUES (1, 0, null, null, null, null, null, null, null, null, null);


CREATE TABLE gt_fichier (
  id_fichier int unsigned auto_increment,
  id_dossier int unsigned,
  nom tinytext,
  extension tinytext,
  description text,
  taille_octet int,
  vignette tinytext,
  nb_downloads int unsigned not null default '0',
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_fichier, id_dossier)
);


CREATE TABLE gt_fichier_version (
  id_fichier int unsigned,
  nom tinytext,
  nom_reel text,
  taille_octet int unsigned,
  description text,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext
);


CREATE TABLE gt_forum_sujet (
  id_sujet int unsigned auto_increment,
  titre tinytext,
  description text,
  id_theme int,
  date_dernier_message datetime,
  auteur_dernier_message tinytext,
  users_consult_dernier_message text,
  users_notifier_dernier_message tinytext,
  raccourci tinyint,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_sujet)
);


CREATE TABLE gt_forum_message (
  id_message int unsigned auto_increment,
  id_message_parent int unsigned,
  id_sujet int unsigned,
  titre tinytext,
  description text,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_message)
);


CREATE TABLE gt_forum_theme (
  id_theme int unsigned auto_increment,
  id_utilisateur int unsigned,
  id_espaces text,
  titre tinytext,
  description text,
  couleur tinytext,
  PRIMARY KEY (id_theme)
);


CREATE TABLE gt_agenda (
  id_agenda int unsigned auto_increment,
  id_utilisateur int unsigned,
  type tinytext,
  titre tinytext,
  description text,
  evt_affichage_couleur ENUM('background','border'),
  plage_horaire tinytext,
  PRIMARY KEY (id_agenda)
);


CREATE TABLE gt_agenda_jointure_evenement (
  id_evenement int unsigned,
  id_agenda int unsigned,
  confirme tinyint unsigned,
  PRIMARY KEY (id_evenement, id_agenda)
);


CREATE TABLE gt_agenda_evenement (
  id_evenement int unsigned auto_increment,
  titre tinytext,
  description text,
  date_debut datetime,
  date_fin datetime,
  id_categorie int unsigned,
  important tinyint,
  visibilite_contenu tinytext,
  periodicite_type tinytext,
  periodicite_valeurs tinytext,
  period_date_fin date,
  period_date_exception text,
  date_crea datetime,
  id_utilisateur int unsigned,
  invite tinytext,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_evenement)
);


CREATE TABLE gt_agenda_categorie (
  id_categorie int unsigned auto_increment,
  id_utilisateur int unsigned,
  id_espaces text,
  titre tinytext,
  description text,
  couleur tinytext,
  PRIMARY KEY (id_categorie)
);

INSERT INTO gt_agenda_categorie VALUES (1, 1, null, 'rendez-vous', null, '#770000');
INSERT INTO gt_agenda_categorie VALUES (2, 1, null, 'réunion', null, '#000077');
INSERT INTO gt_agenda_categorie VALUES (3, 1, null, 'congés', null, '#dd7700');
INSERT INTO gt_agenda_categorie VALUES (4, 1, null, 'personnel', null, '#007700');


CREATE TABLE gt_historique_mails (
  id_mail int unsigned auto_increment,
  destinataires text,
  titre text,
  description text,
  date_crea datetime,
  id_utilisateur int unsigned,
  PRIMARY KEY (id_mail)
);


CREATE TABLE gt_actualite (
  id_actualite int unsigned auto_increment,
  description text,
  une tinyint,
  offline tinyint,
  date_online datetime,
  date_offline datetime,
  date_crea datetime,
  id_utilisateur int unsigned,
  date_modif datetime,
  id_utilisateur_modif int unsigned,
  PRIMARY KEY (id_actualite)
);

INSERT INTO gt_actualite VALUES (1, '<br /><h2 align=center>Bienvenue sur Agora-project !</h2><br /><h3 onclick="popup(\'../module_utilisateurs/invitation.php\',\'nouvel_utilisateur\')" class="lien">Cliquez ici pour inviter des personnes à rejoindre l\'espace</h3><br /><div style=\'text-align:left;color:#888888\'>Survolez sur le \'\'+\'\' en haut à gauche pour modifier cette actualité</div>', null, null, null, null, null, null, null, null);



CREATE TABLE gt_invitation (
  id_invitation tinytext,
  id_espace int,
  nom tinytext,
  prenom tinytext,
  mail tinytext,
  pass tinytext,
  date_crea datetime,
  id_utilisateur int unsigned
);


CREATE TABLE gt_utilisateur_preferences (
  id_utilisateur int unsigned,
  cle tinytext,
  valeur tinytext
);


CREATE TABLE gt_utilisateur_inscription (
  id_inscription int unsigned auto_increment,
  id_espace int unsigned,
  nom tinytext,
  prenom tinytext,
  mail tinytext,
  identifiant tinytext,
  pass tinytext,
  message text,
  date datetime,
  PRIMARY KEY (id_inscription)
);


CREATE TABLE gt_jointure_objet_fichier (
  id_fichier int unsigned auto_increment,
  nom_fichier text,
  type_objet tinytext,
  id_objet int unsigned,
  nb_downloads int unsigned not null default '0',
  PRIMARY KEY (id_fichier)
);


CREATE TABLE gt_utilisateur_groupe (
  id_groupe int unsigned auto_increment,
  id_utilisateur int unsigned,
  titre tinytext,
  id_utilisateurs text,
  id_espaces text,
  PRIMARY KEY (id_groupe)
);


CREATE TABLE gt_logs (
  action varchar(50),
  module varchar(50),
  type_objet varchar(50),
  id_objet int unsigned,
  date datetime,
  id_utilisateur int unsigned,
  id_espace int unsigned,
  ip varchar(100),
  commentaire  varchar(300),
  KEY action (action),
  KEY module (module),
  KEY type_objet (type_objet),
  KEY id_objet (id_objet),
  KEY date (date)
);
