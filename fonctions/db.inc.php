<?php
////	CONNEXION A LA BDD
$mysql_link_identifier = @mysql_connect(db_host, db_login, db_password);
$mysql_db_connect = @mysql_select_db(db_name);

////	DB PAS ACCESSIBLE : INSTALL DE L'AGORA
if($mysql_link_identifier==false || $mysql_db_connect==false){
	echo "<script type=\"text/javascript\">  alert(\"No connection to MySQL server\");  window.location.replace(\"".ROOT_PATH."install/\");  </script>";
	exit;
}

////	PARAMETRAGE DE LA CONNEXION
mysql_query("SET NAMES UTF8", $mysql_link_identifier);
mysql_query("SET SESSION group_concat_max_len = 102400;", $mysql_link_identifier);
//$_SESSION["db_nb_lecture"] = $_SESSION["db_nb_ecriture"] = "0";



////	RETOURNE UN TABLEAU A 2 DIMENSIONS (lignes & colonnes)
////
function db_tableau($requete, $cle="")
{
	//$_SESSION["db_nb_lecture"]++;
	global $mysql_link_identifier;
	$tab_resultat = array();
	$resultat = mysql_query($requete, $mysql_link_identifier);
	if($resultat==false)	{ echo "<h4>error in : ".$requete."</h4>";  return false; }
	else
	{
		// Tableau avec des clés :  numérique incrémentées  /  d'identifiant spécifique
		if($cle==""){
			while($ligne = mysql_fetch_assoc($resultat))	{ $tab_resultat[] = $ligne; }
		}else{
			while($ligne = mysql_fetch_assoc($resultat))	{ $tab_resultat[$ligne[$cle]] = $ligne; }
		}
		return $tab_resultat;
	}
}


////	RETOURNE UN TABLEAU SIMPLE. Exemple : "DUPOND", "jean", "paris", "france"
////
function db_ligne($requete)
{
	//$_SESSION["db_nb_lecture"]++;
	global $mysql_link_identifier;
	$tab_resultat=array();
	$resultat = mysql_query($requete, $mysql_link_identifier);
	if($resultat==false)	{ echo "<h4>error in : ".$requete."</h4>"; return false; }
	else{
		while($ligne = mysql_fetch_array($resultat))	{ $tab_resultat = $ligne; break; }
		return $tab_resultat;
	}
}


////	RETOURNE UN TABLEAU DE VALEURS SUR UNE SEULE CLE  (liste d'identifiants par exemple)
////
function db_colonne($requete)
{
	//$_SESSION["db_nb_lecture"]++;
	global $mysql_link_identifier;
	$tab_resultat=array();
	$resultat = mysql_query($requete, $mysql_link_identifier);
	if($resultat==false)	{ echo "<h4>error in : ".$requete."</h4>";  return false; }
	else{
		while($ligne = mysql_fetch_array($resultat))	{ $tab_resultat[] = $ligne[0]; }
		return $tab_resultat;
	}
}


////	RETOURNE UNE VALEUR
////
function db_valeur($requete)
{
	//$_SESSION["db_nb_lecture"]++;
	global $mysql_link_identifier;
	$resultat = mysql_query($requete, $mysql_link_identifier);
	if($resultat==false)					{ echo "<h4>error in : ".$requete."</h4>";  return false; }
	elseif(mysql_num_rows($resultat) > 0)	{ return mysql_result($resultat,0,0); }
}


////	EXECUTE UNE REQUETE
////
function db_query($requete, $show_error=true)
{
	//(preg_match("/UPDATE|INSERT|DELETE/i",$requete))  ?  $_SESSION["db_nb_ecriture"]++  :  $_SESSION["db_nb_lecture"]++;
	global $mysql_link_identifier;
	$resultat = mysql_query($requete, $mysql_link_identifier);
	if($resultat==false && $show_error==true)	{ echo "<h4>error in : ".$requete."</h4>";  return false; }
	else										{ return $resultat; }
}


////	FORMATE LA VALEUR D'UN CHAMP DANS UNE REQUETE (SELECT, INSERT..)
////
function db_format($chaine, $options="")
{
	////	Chaine vide / booléen ou numerique NULL
	if($chaine=="" || $chaine=="<div>&nbsp;</div>" || (preg_match("/bool/i",$options) && $chaine!="1") || (preg_match("/numerique/i",$options) && $chaine=="0"))	return "null";
	////	Formate la chaine de caractere
	else
	{
		////	Filtre le code provenant de TinyMCE
		if(preg_match("/editeur/i",$options))
		{
			// Ajoute "wmode=transparent" pour afficher les menus contextuels au dessus des animations flash
			$chaine = str_replace(array("<EMBED","<embed"), "<embed wmode=\"transparent\" ", $chaine);
			// Enleve le javascript pour limiter les XSS  (les attribus qui commencent par "on", les balises "<script>", etc.)
			$chaine = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $chaine);
			$chaine = preg_replace('#<script[^>]*?.*?</script>#siu', '', $chaine);
		}
		////	Sinon convertit les caractères spéciaux ("insert_ext" -> convertit les simples + doubles quotes. Sinon juste les doubles quotes)  ET  enlève le code PHP et les balises HTML
		elseif(preg_match("/insert_ext/i",$options))	$chaine = htmlspecialchars(strip_tags($chaine), ENT_QUOTES);
		else											$chaine = htmlspecialchars(strip_tags($chaine));
		////	Remplace les virgules par des points si c'est une valeur flottante
		if(preg_match("/float/i",$options))		$chaine = str_replace(",", ".", $chaine);
		////	Ajoute  "http://"  dans l'url si besoin
		if(preg_match("/url/i",$options) && !preg_match("/http:/i",$chaine))	$chaine = "http://".$chaine;
		////	Ajoute des slashes si "magic_quotes_gpc" n'est pas activé ou les slashs sont demandés
		if(preg_match("/slash/i",$options) || @get_magic_quotes_gpc()!="1")		$chaine = addslashes($chaine);
		////	Retour avec les guillements simple.. ou pas (pour la recherche de carac.)
		if(preg_match("/noquotes/i",$options))	return trim($chaine);
		else									return "'".trim($chaine)."'";
	}
}


////	FORMATE LA DATE ACTUELLE (DATETIME) POUR L'ECRITURE DANS LA BDD
////
function db_insert_date()
{
	return strftime("%Y-%m-%d %H:%M:%S");
}


////	RETOURNE LE DERNIER ID RENTREE DANS LA DERNIERE REQUETTE
////
function db_last_id()
{
	global $mysql_link_identifier;
	return mysql_insert_id($mysql_link_identifier);
}


////	RECUPERE LES VALEURS D'UN CHAMP ENUM
////
function db_enum($table, $champ)
{
	$tab_enum = db_ligne("SHOW COLUMNS FROM ".$table." LIKE '".$champ."'");
	$tab_enum = explode(",", substr($tab_enum,5,-1));
	foreach($tab_enum as $key => $enum)		{ $tab_enum[$key] = trim($enum,"'"); }
	return $tab_enum;
}


////	SAUVEGARDE DE LA BDD
////
function db_sauvegarde()
{
	foreach(db_colonne("SHOW TABLES FROM `".db_name."`") as $nom_table)
	{
		// SELECTIONNE UNIQUEMENT LES TABLES DE L'AGORA
		if(preg_match("/gt_/i",$nom_table))
		{
			// STRUCTURE DE LA TABLE
			$create_table = db_ligne("SHOW CREATE TABLE ".$nom_table);
			$tab_dump[] = str_replace("\n","",$create_table[1]).";";

			// CONTENU DE LA TABLE
			global $mysql_link_identifier;
			$contenu_table = mysql_query("SELECT * FROM ".$nom_table, $mysql_link_identifier);
			while($ligne = mysql_fetch_assoc($contenu_table))
			{
				$insertion_tmp = "INSERT INTO ".$nom_table." VALUES(";
				foreach($ligne as $field){
					$field = ($field=="")  ?  "null"  :  "'".mysql_real_escape_string($field)."'";
					$insertion_tmp .= $field.",";
				}
				$tab_dump[] = trim($insertion_tmp,",").");";
			}
		}
	}
	// TRANSFORME LE TABLEAU EN TEXTE & ON ENREGISTRE LE FICHIER SQL
	$fichier_dump = PATH_STOCK_FICHIERS."Backup_Mysql_".db_name.".sql";
	$fp = fopen($fichier_dump, "w");
	fwrite($fp, implode("\n", $tab_dump));
	fclose($fp);
	// Retourne le chemin du fichier
	return $fichier_dump;
}


////	FERMER LA BDD
////
function db_close()
{
	//alert("nb lectures : ".$_SESSION["db_nb_lecture"]." - nb écritures : ".$_SESSION["db_nb_ecriture"]);
	global $mysql_link_identifier;
	mysql_close($mysql_link_identifier);
}


////	TEST UNITAIRE DES MISES A JOUR D'AGORA
////
function db_maj_test_version($version_maj_unitaire)
{
	// Si la version n'est pas précisé en BDD  =>  version de "config.inc.php" (avant la V.2.13.1)  OU  version 2.11.0
	if(empty($_SESSION["agora"]["version_agora"]))	$_SESSION["agora"]["version_agora"] = (defined("version_agora"))  ?  version_agora  :  "2.11.0";
	//  pas de controle de version   OU   version actuelle (cf. config.inc.php) PLUS ANCIENNE que la version spécifiée ($version_maj_unitaire)  =>  on met à jour !!
	if($version_maj_unitaire=="no_control" || version_compare(@$_SESSION["agora"]["version_agora"],$version_maj_unitaire,"<="))		return true;
	else																															return false;
}


////	EFFECTUE UNE MISE A JOUR SIMPLE : "INSERT", "CHANGE", etc
////
function db_maj_query($version_maj_unitaire, $requete, $show_error=true)
{
	if(db_maj_test_version($version_maj_unitaire))	db_query($requete,$show_error);
}


////	TESTE L'EXISTANCE D'UNE TABLE, ET LA CREE SI BESOIN
////
function db_maj_table_ajoute($version_maj_unitaire, $table, $requete_creation="")
{
	global $mysql_link_identifier;
	$existe = @mysql_query("SHOW COLUMNS FROM ".$table, $mysql_link_identifier);
	if($existe==false && $requete_creation!="" && db_maj_test_version($version_maj_unitaire))	@mysql_query($requete_creation, $mysql_link_identifier);
	return $existe;
}


////	TESTE L'EXISTANCE D'UN CHAMP DANS UNE TABLE, ET LE CREE SI BESOIN  (exemple : "ALTER TABLE `gt_utilisateur` ADD `plage_horaire` TINYTEXT NULL")
////
function db_maj_champ_ajoute($version_maj_unitaire, $table, $champ, $requete_creation="")
{
	global $mysql_link_identifier;
	$existe = @mysql_query("SELECT ".$champ." FROM ".$table, $mysql_link_identifier);
	if($existe==false && $requete_creation!="" && db_maj_test_version($version_maj_unitaire)) 	@mysql_query($requete_creation, $mysql_link_identifier);
	return $existe;
}


////	TEST L'EXISTANCE D'UN CHAMP ET CHANGE SON NOM SI BESOIN
////
function db_maj_champ_rename($version_maj_unitaire, $table, $champ_old, $requete_renommage)
{
	global $mysql_link_identifier;
	$existe = @mysql_query("SELECT ".$champ_old." FROM ".$table, $mysql_link_identifier);
	if($existe!=false && $requete_renommage!="" && db_maj_test_version($version_maj_unitaire))	@mysql_query($requete_renommage, $mysql_link_identifier);
	return $existe;
}
?>