<?php
////	INIT
define("IS_MAIN_PAGE",true);
require "commun.inc.php";
require PATH_INC."header_menu.inc.php";
//init_id_dossier();

controle_acces_admin('admin_espace');

<?php require PATH_INC."footer.inc.php"; ?>