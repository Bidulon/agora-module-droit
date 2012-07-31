<?php
////	INIT
require "commun.inc.php";
header("Content-type: text/html; charset=UTF-8"); // Mode AJAX Oblige...


////	INFOS + DROIT ACCES + LOGS
////
$fichier_tmp = objet_infos($objet["fichier"],$_REQUEST["id_fichier"]);
$fichier_version_tmp = infos_version_fichier($_REQUEST["id_fichier"]);
droit_acces_controler($objet["fichier"], $fichier_tmp, 1);
add_logs("consult", $objet["fichier"], $_GET["id_fichier"]);
if(!isset($_REQUEST["rotation"]))	$_REQUEST["rotation"] = 0;


////	CHEMIN DU FICHIER
////
$chemin_fichier = PATH_MOD_FICHIER.chemin($objet["fichier_dossier"],$fichier_tmp["id_dossier"],"url").$fichier_version_tmp["nom_reel"];
$chemin_fichier_src = ($_REQUEST["rotation"]>0)  ?  "image_rotation.php?rotation=".$_REQUEST["rotation"]."&chemin_fichier=".urlencode($chemin_fichier)  :  $chemin_fichier;


////	DIMENSION DE L'IMAGE PRECEDANTE  &  ROTATION
////
if(preg_match("/90|270/", $_REQUEST["rotation"]))	{ list($img_hauteur, $img_largeur) = getimagesize($chemin_fichier); }
else												{ list($img_largeur, $img_hauteur) = getimagesize($chemin_fichier); }
$rotation_gauche = ($_REQUEST["rotation"]=="270")  ?  "0"  :  ($_REQUEST["rotation"]+90);
$rotation_droite = ($_REQUEST["rotation"]==0)  ?  "270"  :  ($_REQUEST["rotation"]-90);


////	IMAGE PRECEDANTE / SUIVANTE
////
$cpt_derniere_img = count($_SESSION["cfg"]["espace"]["scroller_images"]) - 1;
foreach($_SESSION["cfg"]["espace"]["scroller_images"] as $cpt_img => $infos_image)
{
	if($infos_image["id_fichier"]==$_REQUEST["id_fichier"]) {
		// Image precedante (dernière de la liste ou n-1)
		if($cpt_img==0)		{ $id_img_pre = $_SESSION["cfg"]["espace"]["scroller_images"][$cpt_derniere_img]["id_fichier"]; }
		else				{ $id_img_pre = $_SESSION["cfg"]["espace"]["scroller_images"][$cpt_img-1]["id_fichier"]; }
		// Image suivante (première de la liste ou n+1)
		if($cpt_img==$cpt_derniere_img)		{ $id_img_suiv = $_SESSION["cfg"]["espace"]["scroller_images"][0]["id_fichier"]; }
		else								{ $id_img_suiv = $_SESSION["cfg"]["espace"]["scroller_images"][$cpt_img+1]["id_fichier"]; }
	}
}


////	MENU PRINCIPAL DE L'IMAGE
////
echo "<div class='noprint' style='position:fixed;top:82px;left:0px;z-index:1000;width:100%;text-align:center;padding:5px;'>";
	////	INFOS (parametrage HTTPS+IE) + TELECHARGER + IMPRIMER
	if(defined("HOST_DOMAINE")==true && @$_SESSION["cfg"]["navigateur"]=="ie")	echo "<img src=\"".PATH_TPL."divers/info.png\" class=\"lien icone\" ".infobulle($trad["FICHIER_info_https_flash"])." /> &nbsp; ";
	echo "<a href=\"telecharger.php?id_fichier=".$fichier_tmp["id_fichier"]."\"><img src=\"".PATH_TPL."divers/telecharger.png\" class=\"icone\" ".infobulle($trad["telecharger"])." /></a> &nbsp; ";
	echo "<img src=\"".PATH_TPL."divers/imprimer.png\" onClick=\"window.print();\" class=\"lien icone\" ".infobulle($trad["imprimer"])." /> &nbsp; ";
	////	ROTATION  +  IMAGE PRECEDENTE  +  IMAGE SUIVANTE
	if(function_exists("imagerotate")){
		echo "<img src=\"".PATH_TPL."module_fichier/rotation_gauche.png\" onClick=\"affiche_img('".$fichier_tmp["id_fichier"]."', 0, '".$rotation_gauche."');\" class=\"lien icone\" ".infobulle($trad["FICHIER_rotation_gauche"])." /> &nbsp; ";
		echo "<img src=\"".PATH_TPL."module_fichier/rotation_droite.png\" onClick=\"affiche_img('".$fichier_tmp["id_fichier"]."', 0, '".$rotation_droite."');\" class=\"lien icone\" ".infobulle($trad["FICHIER_rotation_droite"])." /> &nbsp; ";
	}
	echo "<img src=\"".PATH_TPL."module_fichier/precedent.png\" onClick=\"affiche_img('".$id_img_pre."');\" class=\"lien icone\" ".infobulle($trad["FICHIER_img_precedante"])." /> &nbsp; ";
	echo "<img src=\"".PATH_TPL."module_fichier/suivant.png\" onClick=\"affiche_img('".$id_img_suiv."');\" class=\"lien icone\" ".infobulle($trad["FICHIER_img_suivante"])." /> &nbsp; ";
	////	DIAPORAMA : LANCER / PAUSE
	echo "<img src=\"".PATH_TPL."module_fichier/diaporama_lecture.png\" onClick=\"lance_diaporama(true);\" id=\"icone_lect_diapo\" class=\"lien icone\"  ".(@$_REQUEST["diaporama"]=="1"?"style='display:none;'":"")."  ".infobulle($trad["FICHIER_defiler_images"])." /> &nbsp; ";
	echo "<img src=\"".PATH_TPL."module_fichier/pause.png\" onClick=\"lance_diaporama(false);\" id=\"icone_stop_diapo\" class=\"lien icone\"  ".(@$_REQUEST["diaporama"]=="1"?"":"style='display:none;'")." /> &nbsp; ";
echo "</div>";
?>


<img src="<?php echo $chemin_fichier_src ; ?>" id="image" style="width:<?php echo $img_largeur; ?>px;height:<?php echo $img_hauteur; ?>px;" onClick="redimentionne_img('zoom');" onMouseOver="txt_zoom('<?php echo addslashes($trad["FICHIER_zoom"]); ?>');" onMouseOut="bullefin();" />


<input type="hidden" id="id_fichier" value="<?php echo $fichier_tmp["id_fichier"]; ?>" />
<input type="hidden" id="image_width_reference" value="<?php echo $img_largeur; ?>" />
<input type="hidden" id="image_height_reference" value="<?php echo $img_hauteur; ?>" />
<input type="hidden" id="id_img_pre" value="<?php echo $id_img_pre; ?>" />
<input type="hidden" id="id_img_suiv" value="<?php echo $id_img_suiv; ?>" />
<input type="hidden" id="rotation_gauche" value="<?php echo $rotation_gauche; ?>" />
<input type="hidden" id="rotation_droite" value="<?php echo $rotation_droite; ?>" />
