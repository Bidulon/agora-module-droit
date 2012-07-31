<?php
////	LISTE DES UTILISATEURS AFFECTES A UN ESPACE
////
function users_espace($id_espace, $retour="id")
{
	// Tous les utilisateurs du site / Uniquement ceux affectés à l'espace
	$ts_users_site = db_valeur("SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".intval($id_espace)."' AND tous_utilisateurs=1");
	if($ts_users_site > 0)	$tab_users_tmp = db_tableau("SELECT * FROM gt_utilisateur ORDER BY ".$_SESSION["agora"]["tri_personnes"]);
	else					$tab_users_tmp = db_tableau("SELECT DISTINCT T1.*  FROM  gt_utilisateur T1, gt_jointure_espace_utilisateur T2  WHERE  T1.id_utilisateur=T2.id_utilisateur  AND  T2.id_espace='".intval($id_espace)."'  ORDER BY ".$_SESSION["agora"]["tri_personnes"]);
	// Retourne toutes les infos ou juste les identifiants des users
	if($retour!="id")	{ return $tab_users_tmp; }
	else{
		$tab_id = array();
		foreach($tab_users_tmp as $infos_user)   { $tab_id[] = $infos_user["id_utilisateur"]; }
		return $tab_id;
	}
}


////	LISTE DES UTILISATEURS QU'UN UTILISATEUR PEUT VOIR  (tous espace confondu)
////
function users_visibles($infos_user="", $sauf_user_courant=true, $avec_mail=false)
{
	// Init
	if($infos_user=="")  $infos_user = $_SESSION["user"];
	$sauf_user_courant	= ($sauf_user_courant==true)  ?  "AND id_utilisateur!='".$infos_user["id_utilisateur"]."'"  :  "";
	$avec_mail			= ($avec_mail==true)  ?  "AND mail!=''"  :  "";
	$users_selected = "";
	// Liste les autres users des espaces auquel peut accéder l'utilisateur courant
	if($infos_user["admin_general"]!=1){
		foreach(espaces_affectes_user($infos_user) as $espace_tmp)	{ $users_selected .= implode(users_espace($espace_tmp["id_espace"]),","); }
		$users_selected = "AND id_utilisateur IN (".trim("0,".$users_selected,",").")";
	}
	// Renvoi la liste des users
	return db_tableau("SELECT * FROM gt_utilisateur WHERE 1 ".$users_selected." ".$sauf_user_courant." ".$avec_mail." ORDER BY ".$_SESSION["agora"]["tri_personnes"]);
}


////	LISTE DES AUTRES USERS QU'IL PEUT VOIR SUR LE LIVECOUNTER & MESSENGER  :  CONNECTES + DECONNECTES
////
function users_livecounter($infos_user="")
{
	if($infos_user=="")  $infos_user = $_SESSION["user"];
	$users_selected = "0,";  // ID factice pour pas tout sélectionner si ya rien
	foreach(users_visibles($infos_user) as $user_tmp)	{  $users_selected .= $user_tmp["id_utilisateur"].",";  }
	return db_tableau("SELECT  T1.*  FROM  gt_utilisateur T1, gt_jointure_messenger_utilisateur T2  WHERE  T1.id_utilisateur=T2.id_utilisateur_messenger  AND  T1.id_utilisateur IN (".trim($users_selected,',').")  AND  (T2.id_utilisateur='".$infos_user["id_utilisateur"]."' OR T2.tous_utilisateurs='1')");
}


////	LISTE DES AUTRES USERS QU'IL PEUT VOIR SUR LE LIVECOUNTER & MESSENGER  : CONNECTES UNIQUEMENT
////
function users_connectes()
{
	$users_selected = "0,";  // ID factice pour pas tout sélectionner si ya rien
	foreach(users_livecounter() as $user_tmp)	{  $users_selected .= $user_tmp["id_utilisateur"].",";  }
	return db_tableau("SELECT  T1.*  FROM  gt_utilisateur T1, gt_utilisateur_livecounter T2  WHERE  T1.id_utilisateur=T2.id_utilisateur  AND  T1.id_utilisateur IN (".trim($users_selected,',').")  AND  T2.date_verif > '".(time() - duree_livecounter)."'");
}


////	LISTE DES ESPACES AFFECTES A UN UTILISATEUR
////
function espaces_affectes_user($infos_user=null, $espace_courant_premier=false)
{
	// Init
	if($infos_user==null)  $infos_user = @$_SESSION["user"];
	$espace_courant_premier = ($espace_courant_premier==true)  ?  "(T1.id_espace=".$_SESSION["espace"]["id_espace"].") DESC,"  :  "";
	// Admin général
	if(@$infos_user["admin_general"]==1)	{ return db_tableau("SELECT * FROM gt_espace T1 ORDER BY ".$espace_courant_premier." T1.nom asc"); }
	// Invité  /  Utilisateur
	else{
		$sql_selection = ($infos_user["id_utilisateur"]<1) ?  "T2.invites=1"  :  "T2.id_utilisateur=".$infos_user["id_utilisateur"]." OR T2.tous_utilisateurs=1";
		return db_tableau("SELECT DISTINCT  T1.*  FROM  gt_espace T1  LEFT JOIN gt_jointure_espace_utilisateur T2  ON  T1.id_espace=T2.id_espace WHERE ".$sql_selection." ORDER BY ".$espace_courant_premier." T1.nom asc");
	}
}


////	DROIT D'ACCÈS A L'ESPACE (1=utilisation et 2=administration)
////
function droit_acces_espace($id_espace, $infos_user)
{
	// Si c'est l'admin général : tous les droits
	if($infos_user["admin_general"]==1)		{ return 2; }
	else
	{
		$acces_statut_select = ($infos_user["id_utilisateur"] > 0)  ?  "(id_utilisateur=".$infos_user["id_utilisateur"]." OR tous_utilisateurs=1 OR invites=1)"  :  "invites=1";
		return db_valeur("SELECT MAX(droit) FROM gt_jointure_espace_utilisateur WHERE id_espace='".intval($id_espace)."' AND ".$acces_statut_select);
	}
}


////	CONTROLE L'AFFICHAGE D'UN UTILISATEUR POUR L'UTILISATEUR COURANT
////
function controle_affichage_utilisateur($id_utilisateur)
{
	if($_SESSION["user"]["admin_general"]!=1 && $_SESSION["user"]["id_utilisateur"]!=$id_utilisateur)
	{
		$controle = false;
		foreach(users_visibles() as $infos_user)	{  if($id_utilisateur==$infos_user["id_utilisateur"])  $controle = true;  }
		if($controle==false)	exit();
	}
}


////	CONTROLE D'ACCES AU MODULES D'ADMINISTRATION (ESPACE / PARAMETRAGE / ETC)
////
function controle_acces_admin($type)
{
	if(($type=="admin_general" && $_SESSION["user"]["admin_general"]!=1) || ($type=="admin_espace" && $_SESSION["espace"]["droit_acces"]<2))
		exit();
}


////	INFOS SUR UN UTILISATEUR
////
function user_infos($id_utilisateur, $champ="*")
{
	if($champ=="*")		{ return db_ligne("SELECT * FROM gt_utilisateur WHERE id_utilisateur='".intval($id_utilisateur)."'"); }
	else				{ return db_valeur("SELECT ".$champ." FROM gt_utilisateur WHERE id_utilisateur='".intval($id_utilisateur)."'"); }
}


////	PHOTO UTILISATEUR
////
function photo_user($infos_user, $width_maxi="", $height_maxi="", $dimensions_fixes=false)
{
	$chemin_img = (@$infos_user["photo"]=="")  ?  PATH_TPL."divers/inconnu.png" :  PATH_MOD_USER.$infos_user["photo"];
	if($width_maxi=="")		$width_maxi = $height_maxi;
	if($height_maxi=="")	$height_maxi = $width_maxi;
	$prefixe_max = ($dimensions_fixes==false)  ?  "max-"  :  "";
	return "<img src=\"".$chemin_img."\" style=\"".$prefixe_max."width:".$width_maxi."px;".$prefixe_max."height:".$height_maxi."px;\" ".popup_user(@$infos_user["id_utilisateur"])." />";
}


////	LIEN VERS LA FICHE D'UN UTILISATEUR
////
function popup_user($id_utilisateur)
{
	if($id_utilisateur>0)	return "class='lien' OnClick=\"popup('".ROOT_PATH."module_utilisateurs/utilisateur.php?id_utilisateur=".$id_utilisateur."','user".$id_utilisateur."');\" onMouseMove='pas_propager_click(this);'";
}


////	CONTROLE D'AJOUT D'UTILISATEUR
////
function nb_users_depasse($alerte=true, $close_windows=true)
{
	global $trad;
	// SI ON DEPASSE..
	if(defined("limite_nb_users") && limite_nb_users > 0 && db_valeur("SELECT count(*) FROM gt_utilisateur") >= limite_nb_users)
	{
		if($alerte==true)	alert($trad["MSG_ALERTE_nb_users"].limite_nb_users);
		if($close_windows==true)	reload_close();
		else						return true;
	}
}


////	CREER USER
////
function creer_utilisateur($nom, $prenom, $identifiant, $pass, $id_espace="", $mail="")
{
	global $trad;
/**/////	Vérifie si le nombre d'utilisateurs n'est pas atteind + message d'alerte..
/**/if(nb_users_depasse(true,false)==true){
/**/	return false;
/**/}
/**//////	Vérifie si l'identifiant existe déjà
/**/elseif(db_valeur("SELECT count(*) FROM gt_utilisateur WHERE identifiant!='' AND identifiant='".$identifiant."'")>0){
/**/	alert($trad["MSG_ALERTE_user_existdeja"]." (".$identifiant.")");
/**/	return false;
/**/}
	////	Créé l'utilisateur avec les infos de base
	elseif(@$identifiant!="" && @$pass!="")
	{
		db_query("INSERT INTO gt_utilisateur SET nom=".db_format(@$nom).", prenom=".db_format(@$prenom).", identifiant=".db_format(@$identifiant).", pass=".db_format(sha1_pass($pass)).", mail=".db_format(@$mail).", date_crea='".db_insert_date()."', espace_connexion=".db_format(@$id_espace));
		$id_utilisateur = db_last_id();
		// On ajoute ensuite l'utilisateur au module agenda, au messenger et à un espace si besoin
		db_query("INSERT INTO gt_agenda SET id_utilisateur='".$id_utilisateur."', type='utilisateur'");
		db_query("INSERT INTO gt_jointure_messenger_utilisateur SET id_utilisateur_messenger='".$id_utilisateur."', tous_utilisateurs='1'");
		if($id_espace > 0)	db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace='".$id_espace."', id_utilisateur='".$id_utilisateur."', droit='1'");
		return $id_utilisateur;
	}
}


////	LIVECOUNTER ET MESSENGER ACTIF?
////
function livecounter_messenger_actif()
{
	////	utilisateur avec messenger activé ?
	if($_SESSION["user"]["id_utilisateur"]>0 && $_SESSION["agora"]["messenger_desactive"]!="1")		return true;
	else																							return false;
}


////	AFFICHAGE D'UNE CARTE GOOGLEMAP POUR UNE GEOLOCALISATION
////
function carte_localisation($personne_tmp)
{
	global $trad;
	if($personne_tmp["adresse"]!="" || $personne_tmp["codepostal"]!="" || $personne_tmp["ville"]!="" || $personne_tmp["pays"]!="")
		return "<a href=\"javascript:popup('http://maps.google.fr/maps?f=q&hl=fr&q=".addslashes($personne_tmp["adresse"].", ".$personne_tmp["codepostal"]." ".$personne_tmp["ville"]." ".$personne_tmp["pays"])."',null,950,600);\" onMouseMove='pas_propager_click(this);' ".infobulle($trad["localiser_carte"])."><img src=\"".PATH_TPL."divers/carte.png\" /></a>";
}


////	GROUPES D'UTILISATEURS & DROITS D'ACCES D'UN ESPACE (ET EVENTUELLEMENT UN UTILISATEUR)
////
function groupes_users($id_espace="", $id_utilisateur="")
{
	////	INIT
	$groupes = array();
	$sql_groupe = "";
	if($id_espace>0){
		$sql_groupe = "WHERE id_espaces is null OR id_espaces LIKE '%@@".$id_espace."@@%'";
		$users_espace = users_espace($id_espace);
	}
	////	LISTE DES GROUPES
	foreach(db_tableau("SELECT * FROM gt_utilisateur_groupe  ".$sql_groupe."  ORDER BY titre") as $groupe_tmp)
	{
		// Tous les groupes sélectionnés / Groupes sélectionnés, affectés à l'utilisateur
		if($id_utilisateur=="" || ($id_utilisateur>0 && preg_match("/@".$id_utilisateur."@/",$groupe_tmp["id_utilisateurs"])))
		{
			// Droit : lecture OU écriture si auteur ou admin gé.
			$groupe_tmp["droit"] = (is_auteur($groupe_tmp["id_utilisateur"]) || $_SESSION["user"]["admin_general"]==1)  ?  2  :  1;
			// Utilisateurs du groupe (& dans l'espace sélectionné?)
			$groupe_tmp["users_title"] = "";
			$groupe_tmp["users_tab"] = array();
			foreach(text2tab($groupe_tmp["id_utilisateurs"]) as $id_user)
			{
				if($id_espace=="" || in_array($id_user,$users_espace)){
					$groupe_tmp["users_title"] .= auteur($id_user).", ";
					$groupe_tmp["users_tab"][]  = $id_user;
				}
			}
			$groupe_tmp["users_title"] = substr($groupe_tmp["users_title"],0,-2);
			// Ajoute le groupe au tableau de sortie
			$groupes[$groupe_tmp["id_groupe"]] = $groupe_tmp;
		}
	}
	return $groupes;
}


////	EDITION D'UTILISATEUR / CONTACT : AFFICHE UN CHAMP TEXTE
////
function aff_champ($user_tmp, $cle_champ, $options="")
{
	// Init
	global $trad;
	$infobulle = $value = $autocomplete = "";
	// Champ obligatoire?
	if(preg_match("/obligatoire/i",$options))		{ $style_txt = "lien_select";	$infobulle = infobulle($trad["champs_obligatoire"]); }
	else											{ $style_txt = "form_libelle"; }
	// Champ password / autre
	if($cle_champ=="pass" || $cle_champ=="pass2")	{ $type_input = "password";	 $autocomplete = "Autocomplete='off'"; }
	else											{ $type_input = "text";		 $value = @$user_tmp[$cle_champ]; }
	// Affiche
	echo "<tr ".$infobulle.">";
		echo "<td class='".$style_txt."' style='width:40%;'>".$trad[$cle_champ]."</td>";
		echo "<td><input type='".$type_input."' name='".$cle_champ."' value=\"".$value."\" ".$autocomplete." style='width:100%;' /></td>";
	echo "</tr>";
}
?>