<?php
////	INIT
require_once "../includes/global.inc.php";


////	ON AJOUTE LE MESSAGE
////
$_POST["tab_users_messenger"][] = $_SESSION["user"]["id_utilisateur"];
db_query("INSERT INTO  gt_utilisateur_messenger SET id_utilisateur_expediteur='".$_SESSION["user"]["id_utilisateur"]."', id_utilisateur_destinataires=".db_format(tab2text($_POST["tab_users_messenger"])).", message=".db_format(rawurldecode($_POST["texte_messenger"])).", couleur=".db_format($_POST["couleur_messenger"]).", date='".time()."'");

// Couleur et liste des utilisateurs sélectionnés
$_SESSION["couleur_messenger"] = $_POST["couleur_messenger"];
$_SESSION["users_consult_messenger"] = $_POST["tab_users_messenger"];

// Déconnexion à la bdd
db_close();
?>
