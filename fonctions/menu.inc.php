<?php
////	LANGUES DISPONIBLES DANS UNE LISTE DEROULANTE
////
function liste_langues($langue_select, $type_config="")
{
	// Init
	global $trad, $infos_trad;
	// Langue passé en parametre  OU  par défaut celui du site  OU  par défaut "french"
	if($langue_select=="" && $_SESSION["agora"]["langue"]!="")		$langue_select = $_SESSION["agora"]["langue"];
	elseif($langue_select=="" && $_SESSION["agora"]["Langue"]=="")	$langue_select = "francais";
	$dossiers_langues = opendir(PATH_LANG);
	// Install : reload page
	$onchange = ($type_config=="install")  ?  "redir('".php_self()."?lang_install='+this.value);"  :  "element('icone_langue').src='".PATH_LANG."'+this.value+'.png';";
	// Affichage
	$liste_langues = "<select name='langue' onChange=\"".$onchange."\">";
	while($langue_file = readdir($dossiers_langues))
	{
		if(preg_match("/\.php/i",$langue_file))
		{
			$langue_tmp = str_replace(".php","",$langue_file);
			$libelle_tmp = $langue_tmp;
			if($type_config=="user" && $langue_tmp==$_SESSION["agora"]["langue"])   $libelle_tmp .= " (".$trad["par_defaut"].")";
			$liste_langues .=  "<option value=\"".$langue_tmp."\" ".($langue_tmp==$langue_select?"selected":"")."> ".$libelle_tmp."</option>";
		}
	}
	$liste_langues .= "</select>&nbsp; ";
	$liste_langues .= "<img src=\"".PATH_LANG.$langue_select.".png\" id='icone_langue' style='vertical-align:middle;height:20px;' />";
	return $liste_langues;
}


////	FONDS D'ECRAN DISPONIBLES DANS UNE LISTE DEROULANTE
////
function menu_fonds_ecran($fond_ecran_select, $parametrage_site=false)
{
	// Init
	global $trad;

	////	JAVASCRIPT DE MODIF
	$menu_tmp = "<script type='text/javascript'>  function gestion_fond_ecran() {";
	// Init
	$menu_tmp .= "img_select = element('fond_ecran_select').value; ";
	$menu_tmp .= "img_nom	 = img_select.replace('".BG_DEFAULT."',''); ";
	// Change l'image du fond d'écran
	$menu_tmp .= "if(trouver('".BG_DEFAULT."',img_select))	{ element('fond_ecran_img').src = \"".PATH_DIVERS."vignette_img.php?chemin_img=".PATH_WALLPAPER."\" + img_nom; } ";
	$menu_tmp .= "else if(img_select!='ajouter')			{ element('fond_ecran_img').src = \"".PATH_DIVERS."vignette_img.php?chemin_img=".PATH_WALLPAPER_USER."\" + img_nom; } ";
	//	Ajoute un fond d'écran ?  Affiche bouton suppr (fond perso)
	if($parametrage_site==true){
		$menu_tmp .= "(img_select=='ajouter')  ?  afficher('fichier_fond_ecran',true)  :  afficher('fichier_fond_ecran',false);";
		$menu_tmp .= "suppr_fond_ecran = \"&nbsp; <span class='lien' onClick=\\\"if(confirm('".addslashes($trad["confirmer"])."')) redir('index.php?suppr_fond_ecran=\"+ img_nom +\"');\\\"><img src='".PATH_TPL."divers/supprimer.png' /> ".$trad["supprimer"]."</span>\"; ";
		$menu_tmp .= "element('bouton_suppr_fond_ecran').innerHTML =  (trouver('".BG_DEFAULT."',img_select)==false && img_select!='ajouter')  ?  suppr_fond_ecran  :  ''; ";
	}
	$menu_tmp .= "}  </script>";

	////	CHEMIN DU FOND D'ECRAN
	$img_src = (preg_match("/".BG_DEFAULT."/i",$fond_ecran_select))  ?  PATH_WALLPAPER.str_replace(BG_DEFAULT,"",$fond_ecran_select)  :  PATH_WALLPAPER_USER.$fond_ecran_select;
	if(is_file($img_src)==false)	$img_src = PATH_WALLPAPER."1.jpg";

	////	LISTE DES FONDS D'ECRAN  (PAR DEFAUT + PERSO)
	$fonds_ecran_tmp = array();
	$dir = opendir(PATH_WALLPAPER);
	while($elem = readdir($dir))	{ $fonds_ecran_tmp[] = array("chemin"=>PATH_WALLPAPER.$elem, "valeur"=>BG_DEFAULT.$elem, "nom"=>$elem, "nom_sans_ext"=>str_replace(extension($elem),"",$elem)); }
	$fonds_ecran_tmp = array_multi_sort($fonds_ecran_tmp, "nom_sans_ext");
	$dir = opendir(PATH_WALLPAPER_USER);
	while($elem = readdir($dir))	{ $fonds_ecran_tmp[] = array("chemin"=>PATH_WALLPAPER_USER.$elem, "valeur"=>$elem, "nom"=>$elem, "nom_sans_ext"=>str_replace(extension($elem),"",$elem)); }

	////	IMAGE  &  MENU DE SELECTION
	$menu_tmp .= "<img src=\"\" id='fond_ecran_img' style='max-width:150px;max-height:150px;vertical-align:middle;' /> &nbsp; ";
	$menu_tmp .= "<select name='fond_ecran' ".($parametrage_site==true?"size='6'":"")." style='vertical-align:top;min-width:120px;' id='fond_ecran_select' OnChange=\"gestion_fond_ecran();\">";
		// "Par défaut" / "Ajouter"
		if($parametrage_site==false)	$menu_tmp .= "<option value=''>".$trad["par_defaut"]."</option>";
		else							$menu_tmp .= "<option value='ajouter' style='color:#b00;font-weight:bold;'>".$trad["ajouter"]."</option>";
		// Liste des fonds d'écran dispo
		foreach($fonds_ecran_tmp as $fond_ecran_tmp){
			if(is_file($fond_ecran_tmp["chemin"]))	$menu_tmp .= "<option value=\"".$fond_ecran_tmp["valeur"]."\" ".($fond_ecran_select==$fond_ecran_tmp["valeur"]?"selected":"").">".$fond_ecran_tmp["nom"]."</option>";
		}
	$menu_tmp .= "</select> &nbsp; ";

	////	BOUTON SUPPR & INPUT AJOUT & JAVASCRIPT D'AFFICHAGE
	if($parametrage_site==true){
		$menu_tmp .="<span id='bouton_suppr_fond_ecran' ".infobulle($trad["PARAMETRAGE_suppr_fond_ecran"])."></span>";
		$menu_tmp .="<input type='file' name='fichier_fond_ecran' id='fichier_fond_ecran' class='cacher' />";
	}
	$menu_tmp .= "<script type='text/javascript'> gestion_fond_ecran(); </script>";

	return $menu_tmp;
}


////	ICONE DE MODIF
////
function icone_modif($adresse_popup, $confirm=1)
{
	if($confirm==1) {
		global $trad;
		return "<img src=\"".PATH_TPL."divers/crayon.png\" title=\"".$trad["modifier"]."\" class='lien'  onclick=\"popup('".$adresse_popup."','modif');\" />";
	}
}


////	ICONE DE SUPPRESSION
////
function icone_suppr($adresse_suppr, $confirm=1, $phrase_confirm="defaut")
{
	if($confirm==1)
	{
		global $trad;
		if($phrase_confirm=="defaut")	$phrase_confirm = $trad["confirmer_suppr"];
		return "<img src=\"".PATH_TPL."divers/supprimer.png\" title=\"".$trad["supprimer"]."\" class='lien'  onclick=\"confirmer('".addslashes($phrase_confirm)."','".$adresse_suppr."');\" />";
	}
}


////	PAGER => MENU DE NAVIGATION SUR PLUSIEURS PAGES
////
function menu_pager($nb_elems, $url_destination="index.php")
{
	$nbpages = ceil($nb_elems / nb_elements_page());
	if($nbpages>1)
	{
		////	INIT
		global $trad;
		$page_courante = $_SESSION["cfg"]["espace"]["num_page"];
		$url_tmp = $url_destination.variables_get("num_page");
		$page_pre  = ($page_courante==1)  ?  $url_tmp."&num_page=1"  :  $url_tmp."&num_page=".($page_courante-1);
		$page_suiv = ($page_courante==$nbpages)  ?  $url_tmp."&num_page=".$nbpages  :  $url_tmp."&num_page=".($page_courante+1);
		////	AFFICHAGE
		$pager = "<hr style='clear:both;visibility:hidden;' />";
		$pager .= "<div class='div_menu_horizontal'>";
			$pager .= "<img src=\"".PATH_TPL."divers/precedent.png\" onClick=\"redir('".$page_pre."');\" class='lien' style='margin-bottom:3px;' />&nbsp; ";
			for($i=1; $i<=$nbpages; $i++)	{ $pager .= "<a href=\"".$url_tmp."&num_page=".$i."\" style='font-size:14px;padding:7px;".($i==$page_courante?STYLE_SELECT_RED:"")."' title=\"".$trad["aller_page"]." ".$i."\">".$i."</a>"; }
			$pager .= " &nbsp;<img src=\"".PATH_TPL."divers/suivant.png\" onClick=\"redir('".$page_suiv."');\" class='lien' style='margin-bottom:3px;' />";
		$pager .= "</div>";
		return $pager;
	}
}


////	MENU ALPHABET
////
function menu_alphabet($table_select, $champ_select, $clause_select, $url_destination)
{
	////	INIT
	global $trad;
	$url_tmp = $url_destination.variables_get("alphabet");
	if(!isset($_REQUEST["alphabet"]))	{ $style_tout = "lien_select";	$text_filtre = $trad["tout"]; }
	else								{ $style_tout = "lien";			$text_filtre = $_REQUEST["alphabet"]; }
	$lettres_alphabet = db_colonne("SELECT DISTINCT left(".$champ_select.",1) FROM ".$table_select." WHERE 1 ".$clause_select." ORDER BY ".$champ_select." asc ");
	////	AFFICHAGE
	$alphabet = "<div class='menu_gauche_line lien' onMouseOver=\"afficher('menu_alphabet',true);\" onMouseOut=\"afficher('menu_alphabet',false);\">";
		$alphabet .= "<div class='menu_gauche_img'><img src=\"".PATH_TPL."divers/alphabet.png\" /></div>";
		$alphabet .= "<div class='menu_gauche_txt'>";
			$alphabet .= "<div class='menu_context' id='menu_alphabet'>";
				$alphabet .= "<a href=\"".$url_tmp."\" class='".$style_tout."'>".$trad["tout"]."</a> &nbsp;";
				foreach($lettres_alphabet as $lettre_tmp)  { $alphabet .= "<a href=\"".$url_tmp."&alphabet=".$lettre_tmp."\" class='".(@$_REQUEST["alphabet"]==$lettre_tmp?"lien_select":"lien")."'>".majuscule($lettre_tmp)."</a> &nbsp;"; }
			$alphabet .= "</div>";
			$alphabet .= $trad["alphabet_filtre"]." : ".$text_filtre;
		$alphabet .= "</div>";
	$alphabet .= "</div>";
	return $alphabet;
}


////	AFFICHAGE BLOCK / LISTE / ARBORESCENCE
////
function menu_type_affichage($types="liste,bloc")
{
	////	STYLE + URL
	global $trad;
	$style_liste = $style_bloc = $style_arbo = "lien";
	if($_REQUEST["type_affichage"]=="liste")	$style_liste = "lien_select";
	if($_REQUEST["type_affichage"]=="bloc")		$style_bloc  = "lien_select";
	if($_REQUEST["type_affichage"]=="arbo")		$style_arbo  = "lien_select";
	$url_tmp = php_self().variables_get("type_affichage")."&type_affichage=";
	////	AFFICHAGE
	$affichage = "<div class='menu_gauche_line lien' onMouseOver=\"afficher('menu_affichage',true);\" onMouseOut=\"afficher('menu_affichage',false);\">";
		$affichage .= "<div class='menu_gauche_img'><img src=\"".PATH_TPL."divers/affichage_".$_REQUEST["type_affichage"].".png\" /></div>";
		$affichage .= "<div class='menu_gauche_txt'>";
			$affichage .= "<div class='menu_context' id='menu_affichage'>";
				$affichage .= "<a href=\"".$url_tmp."liste\" class='".$style_liste."'><img src=\"".PATH_TPL."divers/affichage_liste.png\" style='margin-bottom:5px;' /> &nbsp; ".$trad["type_affichage_liste"]."</a><br />";
				if(preg_match("/bloc/i",$types))	$affichage .= "<a href=\"".$url_tmp."bloc\" class='".$style_bloc."'><img src=\"".PATH_TPL."divers/affichage_bloc.png\" /> &nbsp; ".$trad["type_affichage_bloc"]."</a>";
				if(preg_match("/arbo/i",$types))	$affichage .= "<a href=\"".$url_tmp."arbo\" class='".$style_arbo."'><img src=\"".PATH_TPL."divers/affichage_arbo.png\" /> &nbsp; ".$trad["type_affichage_arbo"]."</a>";
			$affichage .= "</div>";
			$affichage .= $trad["type_affichage"];
		$affichage .= "</div>";
	$affichage .= "</div>";
	return $affichage;
}


////	MENU DE TRI
////
function menu_tri($options, $id_pref_user="", $url_destination="")
{
	////	INIT
	global $trad;
	////	TRI PREFERENCE USER  /  TRI PAR DEFAUT
	if($id_pref_user=="")	$id_pref_user = "tri_dossier_".MODULE_NOM."_".@$_REQUEST["id_dossier"];
	pref_user($id_pref_user, "tri");
	if(in_array(@$_REQUEST["tri"],$options)==false)		$_REQUEST["tri"] = $options[0];
	////	URL
	if($url_destination=="")	$url_destination = php_self();
	$url_tmp = $url_destination.variables_get("tri")."&tri=";
	////	AFFICHAGE (DANS UNE LISTE!)
	$affichage = "<div class='menu_gauche_line lien' onMouseOver=\"afficher('menu_tri',true);\" onMouseOut=\"afficher('menu_tri',false);\">";
		$affichage .= "<div class='menu_gauche_img'><img src=\"".PATH_TPL."divers/tri.png\" /></div>";
		$affichage .= "<div class='menu_gauche_txt'>";
			$affichage .= "<div class='menu_context' id='menu_tri'>";
			foreach($options as $tri_tmp){
				$tri_tab = text2tab($tri_tmp);
				$title = ($tri_tab[1]=="asc")  ?  $trad["tri_ascendant"]  :  $trad["tri_descendant"];
				$class = ($_REQUEST["tri"]==$tri_tmp)  ?  "lien_select"  :  "lien";
				$affichage .= "<a href=\"".$url_tmp.$tri_tmp."\" class='".$class."' title=\"".$title."\">".$trad["tri"][$tri_tab[0]]." &nbsp;<img src=\"".PATH_TPL."divers/tri_".$tri_tab[1].".png\" /></a><br />";
			}
			$affichage .= "</div>";
			$tri_tab = text2tab($_REQUEST["tri"]);
			$affichage .= $trad["trie_par"]." &nbsp;".$trad["tri"][$tri_tab[0]]." &nbsp; <img src=\"".PATH_TPL."divers/tri_".$tri_tab[1].".png\" />";
		$affichage .= "</div>";
	$affichage .= "</div>";
	return $affichage;
}


////	MENU CHEMIN (ARBORESCENCE DOSSIER)
////
function menu_chemin($obj_tmp, $id_objet, $url_destination="index.php")
{
	////	INIT
	global $trad;
	$affichage = "<div class='div_menu_horizontal pas_selection'>";
	foreach(chemin($obj_tmp,$id_objet,"tableau") as $dossier_tmp){
		$affichage .= "<a href=\"".$url_destination."?id_dossier=".$dossier_tmp["id_dossier"]."\" class='lien'><img src=\"".PATH_TPL."divers/dossier_arborescence.png\" />&nbsp;".text_reduit($dossier_tmp["nom"],30)."</a> &nbsp; ";
	}
	$affichage .= "</div>";
	$affichage .= "<hr style='clear:both;visibility:hidden;' />";
	return $affichage;
}


////	MENU PHOTO USER / CONTACT
////
function menu_photo($photo)
{
	////	INIT
	global $trad;
	$affichage  = "<span class='form_libelle'>".$trad["photo"]." : </span>";
	$affichage .= "<select name='image' OnChange=\"if(this.value=='changer')  {afficher('div_fichier',true);}  else  {afficher('div_fichier',false);}\">";
		$affichage .= "<option>".($photo!=""?$trad["garder"]:"")."</option>";
		if($photo!="")	$affichage .= "<option value='supprimer'>".$trad["supprimer"]."</option>";
		$affichage .= "<option value='changer'>".($photo!=""?$trad["image_changer"]:$trad["ajouter"])."</option>";
	$affichage .= "</select>";
	$affichage .= "<div id='div_fichier' class='cacher'><br /><br /><input type='file' name='fichier_image' /></div>";
	return $affichage;
}


////	AFFECTATION AUX ESPACES  (Thème de forum (exple : T1) / Categories d'evenement (exple : C1) / Groupe d'utilisateur (exple : G1))
////
function menu_affect_espaces($id_objet, $type_objet, $id_espaces_select, $selection_js="")
{
	// Init
	global $trad;
	$espaces_selects = text2tab($id_espaces_select);
	$type_id_objet = $type_objet.$id_objet;
	$liste_espaces = array_merge(array(array("id_espace"=>"tous", "nom"=>"<i>".$trad["visible_ts_espaces"]."</i>")), espaces_affectes_user());
	// Menu masqué pour les utilisateurs lambda
	$menu = "<div style='margin-top:15px;margin-bottom:15px;overflow:auto;max-height:100px;".($_SESSION["user"]["admin_general"]!=1?"display:none;":"")."'>";
		$menu .= "<hr /><div style='margin-bottom:5px;'>".$trad["visible_espaces"]." :</div>";
		foreach($liste_espaces as $espace_tmp)
		{
			// Check :   objet créé + "tous" sélect   /   espace sélect  /  nouvel objet + espace courant
			$checked = (($id_objet>0 && $espace_tmp["id_espace"]=="tous" && count($espaces_selects)==0)  ||  in_array($espace_tmp["id_espace"],$espaces_selects)  ||  ($id_objet==0 && $espace_tmp["id_espace"]==$_SESSION["espace"]["id_espace"]))  ?  "checked"  :  "";
			// Déselection de "tous", si un espace est sélectionné
			$deselection_js = ($espace_tmp["id_espace"]!="tous")  ?  "if(is_checked(this.id)) set_check('Etous_".$type_id_objet."',false);"  :  "for(id_espace_tmp in users_ensembles){ if(id_espace_tmp!='tous') set_check('E'+id_espace_tmp+'_".$type_id_objet."',false); }";
			// Si on Check un espace : désélection de l'espace "0" (tous les espaces) + lance la fonction spécifique du module
			$menu .= "<input type='checkbox' name='id_espaces[]' value='".$espace_tmp["id_espace"]."' id=\"E".$espace_tmp["id_espace"]."_".$type_id_objet."\" OnClick=\"".$deselection_js.$selection_js."\" ".$checked." /> ".$espace_tmp["nom"]."<br />";
		}
	$menu .= "</div>";
	// Initialise la liste des espaces et utilisateurs associés (utilise le tableau "users_ensembles")
	$menu .= "<script>";
	$menu .= "users_ensembles['tous'] = Array(".db_valeur("SELECT GROUP_CONCAT(id_utilisateur) FROM gt_utilisateur").");";
	foreach(espaces_affectes_user() as $espace_tmp)		{ $menu .= "users_ensembles['".$espace_tmp["id_espace"]."'] = Array(".implode(",",users_espace($espace_tmp["id_espace"])).");"; }
	$menu .= "</script>";
	// Retourne le résultat
	return $menu;
}


////	AFFICHE UN CAPTCHA
////
function menu_captcha()
{
	// Init
	global $trad;
	$captcha_path = PATH_DIVERS."captcha.php";
	// Controle javacript : captcha non spécifié / erronné ?
	$captcha = "<script type='text/javascript'>
	function controle_captcha()
	{
		if(get_value('captcha')=='')  { alert(\"".$trad["captcha_alert_specifier"]."\");  return false; }
		requete_ajax(\"GET\", \"".PATH_DIVERS."captcha_verif.php?captcha=\"+get_value('captcha').toUpperCase());
		if(Http_Request_Result!='true')  { alert(\"".$trad["captcha_alert_erronee"]."\");  return false; }
	}
	</script>";
	// Affichage du captcha
	$captcha .= "<span ".infobulle($trad["captcha_info"]).">".
					$trad["captcha"]."&nbsp;<img src=\"".PATH_TPL."divers/reload.png\" style='cursor:pointer;width:15px;' title='reload !' onClick=\"element('captcha_img').src='".$captcha_path."?tmp='+Math.random();\" />&nbsp;&nbsp;&nbsp;
					<img src=\"".$captcha_path."\" id='captcha_img' />&nbsp;&nbsp;&nbsp;<img src=\"".PATH_TPL."divers/fleche_droite.png\" />&nbsp;&nbsp;<input type='text' name='captcha' id='captcha' style='width:120px;text-transform:uppercase;' />
				</span>";
	// retour
	return $captcha;
}


////	MENU TINYMCE
////
function init_editeur_tinymce($textarea_name="description")
{
	global $trad;
	echo   "<script type='text/javascript' src=\"".PATH_DIVERS."tiny_mce/tiny_mce.js\"></script>
			<script type='text/javascript'>
				tinyMCE.init({
					language : '".$trad["EDITOR"]."',
					mode : 'textareas',
					theme : 'advanced',
					entity_encoding : 'raw',
					extended_valid_elements : 'iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]',
					elements : '".$textarea_name."',
					theme_advanced_toolbar_location : 'top',
					theme_advanced_toolbar_align : 'left',
					theme_advanced_buttons1 : 'fontselect,fontsizeselect,forecolor,backcolor,charmap,bold,italic,underline,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,outdent,indent,undo,redo,link,unlink,emotions,image,removeformat".(($_SESSION["user"]["id_utilisateur"]<1)?"":",code")."',
					// MODE COMPLET (Tableaux, medias, etc.)
					".((@$_SESSION["agora"]["editeur_text_mode"]=="complet")  ?  "theme_advanced_buttons2 : 'tablecontrols,|,media,pasteword,visualaid',\n"  :  "theme_advanced_path : false,\n")."
					plugins : 'emotions,visualchars,paste,table,media,autoresize'
				});
			</script>";
	$_GET["textarea_name"] = $textarea_name;
}
?>