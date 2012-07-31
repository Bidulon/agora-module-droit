<?php
////	Ouvre une connexion à la bdd
$connexion_server	= @mysql_connect($_REQUEST["db_host"], $_REQUEST["db_login"], $_REQUEST["db_password"]);
$connexion_db		= @mysql_select_db($_REQUEST["db_name"]);

////	Controle la connexion au serveur de base de données
if($_REQUEST["action"]=="controle_connexion" && $connexion_server==false)	echo "connexion_bdd_pas_ok";

////	Controle s'il existe déjà des tables de l'Agora dans la base de données
if($_REQUEST["action"]=="controle_agora_existe" && @mysql_query("SELECT * FROM gt_agora_info")!=false)		echo "bdd_existe";

////	Controle la version de la base de données
if($_REQUEST["action"]=="controle_version_mysql" && version_compare(@mysql_get_server_info(),"4.2.0",">=")==false)		echo "mysql_version_pas_ok : ".@mysql_get_server_info();
?>