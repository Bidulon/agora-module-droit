<?php
/////	INITIALISATION
/////
session_start();
require "../fonctions/divers.inc.php";
require "../fonctions/text.inc.php";
require "../fonctions/fichier.inc.php";
require "../fonctions/menu.inc.php";
////	CHARGEMENT DES TRADUCTIONS
if(!isset($_GET["lang_install"]))	$_GET["lang_install"] = "francais";
define("PATH_LANG", "../traduction/");
require PATH_LANG.$_GET["lang_install"].".php";
////	CONTROLE DU DOSSIER "stock_fichiers"
define("PATH_STOCK_FICHIERS", "../stock_fichiers/");
chmod_recursif(PATH_STOCK_FICHIERS);
if(!is_writable(PATH_STOCK_FICHIERS))	alert($trad["MSG_ALERTE_chmod_stock_fichiers"]);
////	URL DE CONNEXION (RACINE DU SITE)
$adresse_connexion = "http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
$adresse_connexion = substr($adresse_connexion,0,strpos($adresse_connexion,"install/"))."index.php";


////	VALIDE L'INSTALL
////
if(isset($_POST["installation"]))
{
	// INFOS DE CONNEXION BDD  &  SALT DU PASSWORD
	define("db_host", $_POST["db_host"]);
	define("db_login", $_POST["db_login"]);
	define("db_password", $_POST["db_password"]);
	define("db_name", $_POST["db_name"]);
	define("AGORA_SALT", mt_rand(111111111,999999999));

	// INSTALL
	$connection = @mysql_connect(db_host, db_login, db_password);
	if($connection==false)	{ alert("Error establishing a database connection"); }
	else
	{
		////	CREATION DE LA BDD SI INEXISTANTE  /  NETTOYAGE : SUPPR LES TABLES EXISTANTES DE L'AGORA
		if(mysql_select_db(db_name)==false)		{ mysql_query("CREATE DATABASE ".db_name, $connection); }
		else{
			$tables = mysql_query("SHOW TABLES FROM `".db_name."`");
			while($line = mysql_fetch_row($tables))		{  if(substr($line[0],0,3)=="gt_")  mysql_query("DROP TABLE ".$line[0]." ");  }
		}
		mysql_select_db(db_name);

		////	CHARGE LES FONCTIONS
		require "../fonctions/db.inc.php";
		require "../fonctions/utilisateur.inc.php";

		////	IMPORTATION DU FICHIER SQL
		$fichier_sql = "bdd.sql";
		$handle = fopen($fichier_sql,"r");
		$contenu = fread($handle, filesize($fichier_sql));
		$contenu = str_replace("utf8;", "utf8@@@", $contenu); //Fin de ligne de création de table
		$contenu = str_replace(");", ")@@@", $contenu); //Fin de ligne d'insertion dans la table
		$contenu = explode("@@@",$contenu);
		fclose ($handle);
		foreach($contenu as $ligne)		{ if($ligne!="")  @mysql_query($ligne); }

		////	PARAMETRAGE GENERAL DU SITE  +  PARAMETRAGE DE L'ESPACE
		db_query("UPDATE gt_agora_info SET adresse_web=".db_format($_POST["site_adresse_web"]).", timezone=".db_format(@$_POST["timezone"]).", langue=".db_format($_POST["langue"]));
		db_query("INSERT INTO gt_espace SET id_espace='1', nom=".db_format($_POST["espace_nom"]).", description=".db_format($_POST["espace_description"]));
		if($_POST["espace_public"]=="1")	db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace='1', invites='1', droit='1'");

		////	CREATION DU COMPTE ADMINISTRATEUR
		$id_utilisateur = creer_utilisateur(@$_POST["nom"], @$_POST["prenom"], $_POST["identifiant"], $_POST["pass"], "1", $_POST["mail"]);
		db_query("UPDATE gt_utilisateur SET admin_general='1' WHERE id_utilisateur='".intval($id_utilisateur)."'");

		////	MODIF DE "config.inc.php"
		$tab_valeurs_modif = array("db_host"=>db_host, "db_login"=>db_login, "db_password"=>db_password, "db_name"=>db_name, "limite_nb_users"=>"10000");
		$tab_valeurs_modif["limite_espace_disque"] = return_bytes($_POST["limite_espace_disque"].$_POST["limite_espace_disque_unite"]);
		$tab_valeurs_ajout = array("AGORA_SALT"=>AGORA_SALT);
		modif_fichier_config(PATH_STOCK_FICHIERS."config.inc.php", $tab_valeurs_modif, array(), $tab_valeurs_ajout);

		// REDIRECTION EN PAGE DE CONNEXION
		alert($trad["INSTALL_install_ok"]);
		redir("../index.php");
	}
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title> AGORA-PROJECT INSTALL </title>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
		<script type="text/javascript" src="../commun/javascript.js"></script>
		<meta http-equiv="Cache-Control" content="no-cache">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="0">

		<style type="text/css">
		body					{ font-family:arial,helvetica,sans-serif; margin:0px; }
		table					{ color:#fff; font-size:13px; }
		input[type=text], input[type=password]				{ background-color:#eee; border: #999 1px solid; width:100%; font-size:11px; font-family:Arial,Helvetica,sans-serif; color:#000; }
		input[type=text]:focus, input[type=password]:focus	{ background-color:#fff; border: #fff 1px solid; }
		input[type=submit]		{ color: #000; width:120px; font-size:11px; }
		select					{ font-size:10px; }
		textarea				{ background-color:#eee; border: #999 1px solid; width:100%; height:30px; font-size:11px; }
		.infobulle				{ position:absolute; z-index:10000; visibility:hidden; padding:7px; border:1px solid #ccc; border-radius:4px; background-color:#fff; }
		.infobulle_contenu		{ padding:7px; width:300px; font-size:13px; color:#fff; font-weight:bold; background-color:#000; }
		.titre_tr				{ font-weight:bold; padding-top:30px; padding-bottom:10px; }
		</style>

		<script type="text/javascript">
		////	On contrôle les champs principaux
		function controle_formulaire()
		{
			////	BDD
			// Controle la connexion au serveur de base de données
			bdd_tmp = "&db_host="+get_value("db_host") +"&db_login="+get_value("db_login") +"&db_password="+get_value("db_password") +"&db_name="+get_value("db_name");
			requete_ajax("GET", "controle.php?action=controle_connexion"+bdd_tmp);
			if(trouver('connexion_bdd_pas_ok',Http_Request_Result)==true)	{ if(confirm("<?php echo $trad["INSTALL_erreur_acces_bdd"]; ?>")==false)  return false; }
			// Controle s'il existe déjà une base de données
			requete_ajax("GET", "controle.php?action=controle_agora_existe"+bdd_tmp);
			if(trouver('bdd_existe',Http_Request_Result)==true)				{ if(confirm("<?php echo $trad["INSTALL_erreur_agora_existe"]; ?>")==false)  return false; }
			// Controle la version de MySQL
			requete_ajax("GET", "controle.php?action=controle_version_mysql"+bdd_tmp);
			if(trouver('mysql_version_pas_ok',Http_Request_Result)==true)	{ if(confirm("<?php echo $trad["INSTALL_confirm_version_mysql"]; ?>")==false)  return false; }

			////	DIVERS
			// Champs obligatoire
			if (get_value("db_host")=="" || get_value("db_name")=="" || get_value("db_login")=="" || get_value("nom")=="" || get_value("prenom")=="" || get_value("identifiant")=="" || get_value("pass")=="" || get_value("mail")=="")		{  alert("<?php echo $trad["remplir_tous_champs"]; ?>");  return false;  }
			// Mot de passe & mot de passe vérif identiques
			if(get_value("pass")!=get_value("pass2"))		{ alert("<?php echo $trad["password_verif_alert"]; ?>");  return false; }
			// Vérif du mail
			if(controle_mail(get_value("mail"))==false)		{ alert("<?php echo $trad["mail_pas_valide"]; ?>");  return false; }
			<?php
			////	Controle la version de php & confirme l'installation finale
			if(version_compare(PHP_VERSION,'4.3.0','>=')==false)	echo "if(confirm(\"".$trad["INSTALL_confirm_version_php"]."\")==false)  return false;";
			?>
			////	DERNIERE CONFIRMATION
			if(confirm("<?php echo $trad["INSTALL_confirm_install"]; ?>")==false)  return false;
		}
		</script>
	</head>


	<body>
		<div style="z-index:-1000;position:fixed;left:0px;top:0px;height:100%;width:100%;"><img src="../templates/fond_ecran/1.jpg" style='width:100%;height:100%;' /></div>
		<div id="infobulle" class="infobulle"></div>


		<form action="index.php" method="post" OnSubmit="return controle_formulaire();">
			<table style="margin-top:30px;width:600px;" align="center" cellpadding="2px">
				<tr>
					<td style="text-align:left"><img src="installer.png" style="vertical-align:middle" /></td>
					<td style="text-align:right;cursor:pointer;"><?php echo liste_langues($_GET["lang_install"],"install"); ?></td>
				</tr>
				<tr><td colspan="2" class="titre_tr" style="padding-top:20px;"><?php echo $trad["INSTALL_connexion_bdd"]; ?> :</td></tr>
				<tr>
					<td width="300px"><?php echo $trad["INSTALL_db_host"]; ?></td>
					<td <?php echo infobulle("exemple : ''localhost'', ''sql4.free.fr'', etc."); ?>><input type="text" name="db_host" /></td>
				</tr>
				<tr>
					<td><?php echo $trad["INSTALL_db_login"]; ?></td>
					<td><input type="text" name="db_login" /></td>
				</tr>
				<tr>
					<td><?php echo $trad["INSTALL_db_password"]; ?></td>
					<td><input type="password" name="db_password" /></td>
				</tr>
				<tr>
					<td><?php echo $trad["INSTALL_db_name"]; ?></td>
					<td<?php echo infobulle($trad["INSTALL_db_name_info"]); ?>><input type="text" name="db_name" /></td>
				</tr>
				<tr><td colspan="2" class="titre_tr"><?php echo $trad["INSTALL_config_admin"]; ?> :</td></tr>
				<tr>
					<td><?php echo $trad["nom"]; ?></td>
					<td><input type="text" name="nom" /></td>
				</tr>
				<tr>
					<td><?php echo $trad["prenom"]; ?></td>
					<td><input type="text" name="prenom" /></td>
				</tr>
				<tr <?php echo infobulle($trad["INSTALL_login_password_info"]); ?> >
					<td><?php echo $trad["login"]; ?></td>
					<td><input type="text" name="identifiant" /></td>
				</tr>
				<tr <?php echo infobulle($trad["INSTALL_login_password_info"]); ?> >
					<td><?php echo $trad["pass"]; ?></td>
					<td><input type="password" name="pass" Autocomplete="off" /></td>
				</tr>
				<tr <?php echo infobulle($trad["INSTALL_login_password_info"]); ?> >
					<td><?php echo $trad["pass2"]; ?></td>
					<td><input type="password" name="pass2" /></td>
				</tr>
				<tr>
					<td><?php echo $trad["mail"]; ?></td>
					<td><input type="text" name="mail" /></td>
				</tr>
				<tr><td colspan="2" class="titre_tr"><?php echo $trad["PARAMETRAGE_description_module"]; ?> :</td></tr>
				<tr>
					<td><?php echo $trad["PARAMETRAGE_limite_espace_disque"]; ?></td>
					<td>
						<input type="text" name="limite_espace_disque" value="10" style="width:50px" />
						<select name="limite_espace_disque_unite"><option value="g"><?php echo $trad["giga_octet"]; ?></option><option value="m"><?php echo $trad["mega_octet"]; ?></option></select>
					</td>
				<?php
				////	TIMEZONE
				if(version_compare(PHP_VERSION,'5.1.0','>='))
				{
					echo "<tr>";
						echo "<td>".$trad["PARAMETRAGE_timezone"]."</td>";
						echo "<td><select name=\"timezone\">";
							foreach($tab_timezones as $timezone_libelle => $timezone)	{ echo "<option value=\"".$timezone."\">[GMT ".($timezone>0?"+".$timezone:$timezone)."]&nbsp; ".$timezone_libelle."</option>"; }
						echo "</select><script>set_value('timezone','".server_timezone("num")."');</script></td>";
					echo "</tr>";
				}
				?>
				</tr>
				<tr>
					<td><?php echo $trad["PARAMETRAGE_adresse_web"]; ?></td>
					<td><input type="text" name="site_adresse_web" value="<?php echo $adresse_connexion; ?>" /></td>
				</tr>
				<tr><td colspan="2" class="titre_tr"><?php echo $trad["INSTALL_config_espace"]; ?> :</td></tr>
				<tr>
					<td><?php echo $trad["nom"]; ?></td>
					<td><input type="text" name="espace_nom" value="Espace principal" /></td>
				</tr>
				<tr>
					<td><?php echo $trad["description"]; ?></td>
					<td><textarea name="espace_description"></textarea></td>
				</tr>
				<tr>
					<td><?php echo $trad["ESPACES_espace_public"]; ?></td>
					<td><select name="espace_public"><option value="0"><?php echo $trad["non"]; ?></option><option value="1"><?php echo $trad["oui"]; ?></option></select></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:right;"><br /><br /><input type="hidden" name="installation" value="1"/><input type="submit" value="<?php echo $trad["valider"]; ?>" /></td>
				</tr>
			</table>
		</form>
	</body>
</html>
