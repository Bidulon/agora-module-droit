<?php
// CONNEXION À LA BDD
define("db_host", "localhost");
define("db_login", "root");
define("db_password", "");
define("db_name", "agora");


// EN MAINTENANCE ?  CONTROLE L'ADRESSE IP ?
define("agora_maintenance", false);
define("controle_ip", true);

// ESPACE DISQUE / NB USERS / SALT
define("limite_espace_disque", "10737418240");
define("limite_nb_users", "10000");

// DUREE DU LIVECOUNTER / MESSENGER / AUTRES..
define("duree_livecounter", "45");
define("duree_livecounter_recharge", "12");
define("duree_messages_messenger", 7200);
define("AGORA_SALT", "388735249");
?>
