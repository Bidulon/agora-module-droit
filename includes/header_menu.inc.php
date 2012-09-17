<?php
////	INITIALISATION
////
require_once PATH_INC."header.inc.php";
if(@$_SESSION["agora"]["skin"]=="blanc")	{ $bg_header_gauche = "header_gauche.png";		 $bg_header_centre = "header_centre.png";		$bg_header_droite = "header_droite.png"; }
else										{ $bg_header_gauche = "header_gauche_noir.png";	 $bg_header_centre = "header_centre_noir.png";	$bg_header_droite = "header_droite_noir.png"; }
?>


<style>
.icone_module 			{ <?php if(@$_SESSION["cfg"]["navigateur"]!="ie") echo "opacity:0.9;"; ?> cursor:pointer; margin:3px; }
.icone_module:hover 	{ <?php if(@$_SESSION["cfg"]["navigateur"]!="ie") echo "opacity:1;"; ?> }
.icone_module_select	{ cursor:pointer; margin:3px; margin-top:15px; }
.icone_module_mask		{ cursor:pointer; margin:3px; opacity:0.5; filter:alpha(opacity=50); }
.icone_module_mask:hover{ opacity:1; filter:alpha(opacity=100); }
</style>


<script type="text/javascript">
////		AFFICHAGE DES LIVECOUNTERS
////
function maj_textes_livecounters()
{
	////	livecounter du menu principal
	if(existe("livecounter_principal")){
		requete_ajax("GET", "<?php echo PATH_COMMUN; ?>livecounter.php?type=principal");
		element("livecounter_principal").innerHTML = Http_Request_Result;
	}
	////	livecounter du messenger + Affiche l'icone du messenger ?
	if(existe("livecounter_messenger")){
		requete_ajax("GET", "<?php echo PATH_COMMUN; ?>livecounter.php?type=messenger");
		element("livecounter_messenger").innerHTML = Http_Request_Result;
		if(element("livecounter_principal").innerHTML!="")		element("icone_messenger").style.display="inline";
		else													element("icone_messenger").style.display="none";
	}
}


////		VERIFICATION REGULIERE DU LIVECOUNTER ET DU MESSENGER (changement des personnes présentes? nouveaux messages?)
////
function livecounter_messenger_verif()
{
	requete_ajax("GET", "<?php echo PATH_COMMUN; ?>livecounter_messenger_verif.php");
	eval(Http_Request_Result);  // execute les fonctions javascript
	window.setTimeout("livecounter_messenger_verif();", <?php echo duree_livecounter_recharge*1000; ?>);
}


////		AFFICHAGE DES LIVECOUNTERS & DU MESSENGER EN FIN DE CHARGEMENT DE PAGE
////
function start_livecounters_messenger()
{
	if(existe("livecounter_principal"))		{ maj_textes_livecounters();  livecounter_messenger_verif(); }
}
<?php if(livecounter_messenger_actif()==true)	echo "$(window).load(function(){  start_livecounters_messenger();  });"; ?>


////		AFFICHAGE / MASQUAGE DU MESSENGER
////
function affichage_messenger()
{
	afficher_dynamic("calque_messenger","fade");
	if(element("calque_messenger").style.display!="none")
	{
		maj_messenger_messages();
		// Annule l'animation si elle est en cour
		$("#icone_messenger").stop(true,true);
		$("#icone_messenger").css("opacity",100);
		element("icone_messenger").src = "<?php echo PATH_TPL; ?>divers/messenger.png";
		// Reposition le calque du messenger dans la page si besoin (ascenseur)
		hauteur_messenger = parseInt(element("calque_messenger").offsetHeight);
		position_top_messenger = parseInt(element("calque_messenger").style.top.replace("px",""));
		position_top_page = document.documentElement.scrollTop;
		hauteur_page_visible = document.documentElement.clientHeight;
		// Messenger au dessus / en dessous de la page visible : on le replace pour le rendre accessible
		if((hauteur_messenger + position_top_messenger) < position_top_page)		element("calque_messenger").style.top = (position_top_page+position_top_messenger)+"px";
		if((hauteur_page_visible + position_top_page) < position_top_messenger)		element("calque_messenger").style.top = (position_top_page+120)+"px";
	}
}


////		AFFICHAGE DES MESSAGES DU MESSENGER
////
function maj_messenger_messages()
{
	requete_ajax("GET", "<?php echo PATH_COMMUN; ?>messenger_messages.php");
	element("messenger_liste_messages").innerHTML = Http_Request_Result;
	element("messenger_liste_messages").scrollTop = element("messenger_liste_messages").scrollHeight;	// Les derniers messages sont en bas de div
}


////		NOUVEAU MESSAGE SUR LE MESSENGER  :
////
function messenger_nouveau_message()
{
	// MAJ des messages OU Clignotement de l'icone
	if(element("calque_messenger").style.display!="none")	{ maj_messenger_messages(); }
	else {
		element("icone_messenger").style.display = "inline";
		element("icone_messenger").src = "<?php echo PATH_TPL; ?>divers/messenger_big.png";
		$("#icone_messenger").effect("pulsate",{times:100},500);
	}
	// Son d'alerte
	element("div_son_alert_messerger").innerHTML = "<object type='application/x-shockwave-flash' data='<?php echo PATH_COMMUN; ?>dewplayer-mini.swf' width='0' height='0'><param name='movie' value='<?php echo PATH_COMMUN; ?>dewplayer-mini.swf' /><param name='flashvars' value='mp3=<?php echo PATH_COMMUN; ?>messenger_alerte.mp3&amp;autostart=1' /></object>";
}


////		CONTROLE & POST DU MESSAGE DU MESSENGER
////
function post_message_messenger()
{
	// Vérif du message
	if(get_value("texte_messenger")=="" || get_value("texte_messenger")=="<?php echo $trad["HEADER_MENU_ajouter_message"]; ?>")	{ alert("<?php echo $trad["HEADER_MENU_specifier_message"]; ?>"); return false; }

	// Vérif des utilisateurs cochés
	var nb_users_messenger = 0;
	var url_tab_users_messenger = "";
	tab_users_messenger = document.getElementsByName("tab_users_messenger[]");
	for(i=0; i<tab_users_messenger.length; i++)
	{
		if(tab_users_messenger[i].checked==true) {
			nb_users_messenger++;
			url_tab_users_messenger += "&tab_users_messenger[]="+tab_users_messenger[i].value;
		}
	}
	if(nb_users_messenger==0)	{ alert("<?php echo $trad["selectionner_user"]; ?>");  return false; }

	// On poste le message, relance l'affichage des messages, on réinitialise le champs "message"
	requete_ajax("POST", "<?php echo PATH_COMMUN; ?>messenger_post.php", "texte_messenger="+urlencode(get_value("texte_messenger"))+"&couleur_messenger="+get_value("couleur_messenger")+url_tab_users_messenger);
	maj_messenger_messages();
	set_value("texte_messenger", "");
	element("texte_messenger").focus();
}


////		ANIMATION DES ICONES DE CHAQUE MODULE (pb sous IE8 avec les transparences des png & les animations...)  +  REDIRECTION VERS LE MODULE
////
function module_animeico_redir(icone, adresse_redir)
{
	if(navigateur()!="ie" || version_ie()>8)	$(icone).effect("pulsate",{times:1},130);
	setTimeout(function(){
		redir(adresse_redir);
	}, 300);
}


////	ANIMATION DU LOGO PRINCIPAL AU CHARGEMENT DE LA PAGE
////
$(window).load(function(){
	if((navigateur()!="ie" || version_ie()>8)  &&  existe("logo_header_fond")==true)
		$("#logo_header_fond").effect("pulsate",{times:2},500);
});
</script>



<table style="position:fixed;top:0px;left:0px;z-index:1000;width:100%;min-width:1000px;font-weight:bold;" cellpading="0" cellspacing="0" class="noprint"><tr>
	<td style="width:70px;background-image:url('<?php echo PATH_TPL."divers/".$bg_header_gauche; ?>');background-repeat:repeat-x;" class="lien_select" onMouseOver="afficher('menu_header_principal',true);" onMouseOut="afficher('menu_header_principal',false);">
		<div class="menu_context" id="menu_header_principal">
			<?php
			////	MENU PRINCIPAL (LOGO AGORA)
			////
			$espaces_affectes_user = espaces_affectes_user();
			////	SORTIE AGORA
			echo "<div class='menu_context_ligne lien' onClick=\"redir('".ROOT_PATH."index.php?deconnexion=oui');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/sortir.png\" /></div><div class='menu_context_txt'>".$trad["HEADER_MENU_sortie_agora"]."</div></div>";
			echo "<hr class='menu_context_hr' />";
			////	ESPACE : RECHERCHE  +  ENVOI INVITATION  +  PARAMETRAGE
			echo "<div class='menu_context_ligne lien' class='lien' onClick=\"popup('".PATH_COMMUN."rechercher.php');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/recherche.png\" /></div><div class='menu_context_txt'>".$trad["HEADER_MENU_recherche_elem"]."</div></div>";
			if(@$_SESSION["user"]["envoi_invitation"]=="1")		echo "<div class='menu_context_ligne lien' onClick=\"popup('".ROOT_PATH."module_utilisateurs/invitation.php','invit_utilisateur');\"  title=\"".$trad["UTILISATEURS_envoi_invitation_info"]."\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/acces_utilisateur_plus.png\" /></div><div class='menu_context_txt'>".$trad["UTILISATEURS_envoi_invitation"]."</div></div>";
			if($_SESSION["espace"]["droit_acces"]==2)			echo "<div class='menu_context_ligne lien' onclick=\"edit_iframe_popup('".ROOT_PATH."module_espaces/espace_edit.php?id_espace=".$_SESSION["espace"]["id_espace"]."');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/parametrage.png\" /></div><div class='menu_context_txt'>".$trad["ESPACES_parametrage"]."</div></div>";
			////	MENU ADMINISTRATEUR GENERAL
			if($_SESSION["user"]["admin_general"]==1)
			{
				echo "<hr class='menu_context_hr' />";
				////	GERER UTILISATEURS  /  GERER ESPACES  /  PARAMETRAGE GENERAL
				echo "<div class='menu_context_ligne lien' onclick=\"redir('".ROOT_PATH."module_utilisateurs/index.php?affichage_users=site');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/utilisateurs.png\" /></div><div class='menu_context_txt'>".$trad["UTILISATEURS_gerer_utilisateurs_site"]."</div></div>";
				if(count($espaces_affectes_user)==1)	$trad["ESPACES_gerer_espaces"] = "<span title=\"".$trad["ESPACES_description_module_infos"]."\">".$trad["ESPACES_gerer_espaces"]."</span>";
				$header_menu_module_espace = "<div class='menu_context_ligne lien' onclick=\"redir('".ROOT_PATH."module_espaces/');\" ><div class='menu_context_img'><img src=\"".PATH_TPL."divers/espaces.png\" /></div><div class='menu_context_txt'>".$trad["ESPACES_gerer_espaces"]."</div></div>";
				echo $header_menu_module_espace;
				echo "<div class='menu_context_ligne lien' onclick=\"redir('".ROOT_PATH."module_parametrage/');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/parametrage_general.png\" /></div><div class='menu_context_txt'>".$trad["PARAMETRAGE_description_module"]."</div></div>";
				echo "<hr class='menu_context_hr' />";
				echo "<div class='menu_context_ligne lien' onclick=\"redir('".ROOT_PATH."module_logs/');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/logs.png\" /></div><div class='menu_context_txt'>".$trad["LOGS_description_module"]."</div></div>";
				////	ESPACE DISQUE
				$espace_disque_utilise = dossier_taille(PATH_STOCK_FICHIERS);
				$taux_remplissage = ceil(($espace_disque_utilise/limite_espace_disque)*100);
				if($taux_remplissage > 65)		{ $image_espace_disque = "espace_disque_rouge"; }
				elseif($taux_remplissage > 33)	{ $image_espace_disque = "espace_disque_jaune"; }
				else							{ $image_espace_disque = "espace_disque_vert"; }
				echo "<div class='menu_context_ligne lien' title=\"".$taux_remplissage." % ".$trad["de"]." ".afficher_taille(limite_espace_disque)."\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/".$image_espace_disque.".png\" /></div><div class='menu_context_txt'>".$trad["espace_disque_utilise"]." : ".afficher_taille($espace_disque_utilise)."</div></div>";
			}
			?>
			<div style="margin-top:15px;text-align:right" id="menu_principal_logo_agora"></div>
		</div>
		<img src="<?php echo PATH_TPL; ?>divers/logo_header_fond.png" style="position:fixed;z-index:1001;top:-5px;left:-5px;" id="logo_header_fond" />
		<img src="<?php echo PATH_TPL; ?>divers/logo_header.png" style="position:fixed;z-index:1002;top:-5px;left:-5px;" />
	</td>
	<td style="vertical-align:top;background-image:url('<?php echo PATH_TPL."divers/".$bg_header_gauche; ?>');background-repeat:repeat-x;">
		<table cellpadding="0px" cellspacing="0px" style="width:100%;">
			<tr>
				<td style="height:25px;vertical-align:middle;">
					<?php
					////	INVITE
					if($_SESSION["user"]["id_utilisateur"]<1)
					{
						////	NOM ESPACE + CONNEXION
						echo "<span class='lien'>".$_SESSION["espace"]["nom"]."</span><img src=\"".PATH_TPL."divers/separateur.gif\" />
								<form action='index.php' style='display:inline' method='post' OnSubmit=\"return controle_connexion('".addslashes($trad["specifier_login_password"])."');\">
									<input type='text' name='login' value=\"".$trad["login"]."\" onClick=\"this.value=''\" style='width:100px;font-size:10px;' />
									<input type='password' name='password' value=\"".$trad["pass"]."\" onClick=\"this.value=''\" style='width:80px;font-size:10px;' />
									<input type='submit' value=\"".$trad["connexion"]."\"  class='button' style='width:80px;font-size:9px;' />
								</form>";
					}
					////	UTILISATEUR
					else
					{
						////	INFOS UTILISATEUR  +  MENU
						////
						echo "<span class='lien' onMouseOver=\"afficher('aff_objets',true);\" onMouseOut=\"afficher('aff_objets',false);\">";
							echo "<div class='menu_context' id='aff_objets'>";
								////	MODIF DU PROFIL  +  GESTION DU MESSENGER
								echo "<div class='menu_context_ligne lien' onClick=\"edit_iframe_popup('".ROOT_PATH."module_utilisateurs/utilisateur_edit.php?id_utilisateur=".$_SESSION["user"]["id_utilisateur"]."');\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/modifier_profil.png\" /></div><div class='menu_context_txt'>".$trad["UTILISATEURS_modifier_mon_profil"]."</div></div>";
								if(livecounter_messenger_actif()==true)		echo "<div class='menu_context_ligne lien' onClick=\"edit_iframe_popup('".ROOT_PATH."module_utilisateurs/utilisateur_messenger.php?id_utilisateur=".$_SESSION["user"]["id_utilisateur"]."');\" title=\"".$trad["UTILISATEURS_visibilite_messenger_livecounter"]."\"><div class='menu_context_img'><img src=\"".PATH_TPL."divers/visibilite_livecounter.png\" /></div><div class='menu_context_txt'>".$trad["UTILISATEURS_gestion_messenger_livecounter"]."</div></div>";
								echo "<hr class='menu_context_hr' />";
								////	AFFICHAGE NORMAL / AUTEUR / ADMIN
								$affichages = array("normal","auteur");
								if($_SESSION["espace"]["droit_acces"]==2)	$affichages[] = "tout";
								echo "<div>".$trad["HEADER_MENU_affichage_elem"]." :</div>";
								foreach($affichages as $aff_tmp)	{ echo "<div class='menu_context_ligne'><div class='menu_context_img'>&nbsp;</div><div class='menu_context_txt'><img src=\"".PATH_TPL."divers/fleche_droite.png\" /> <a href=\"".php_self()."?affichage_objet=".$aff_tmp."\" class='".($_SESSION["cfg"]["espace"]["affichage_objet"]==$aff_tmp?"lien_select":"lien")."' title=\"".$trad["HEADER_MENU_affichage_".$aff_tmp."_infos"]."\">".$trad["HEADER_MENU_affichage_".$aff_tmp]."</a></div></div>"; }
							echo "</div>";
							echo $_SESSION["user"]["prenom"]." ".$_SESSION["user"]["nom"]." <img src=\"".PATH_TPL."divers/derouler.png\" />";
						echo "</span>";
						echo "<img src=\"".PATH_TPL."divers/separateur.gif\" />";

						////	ESPACES AFFICHE  +  MENU
						////
						echo "<span class='lien' onMouseOver=\"afficher('liste_espaces',true);\" onMouseOut=\"afficher('liste_espaces',false);\">";
							echo "<div class='menu_context' id='liste_espaces'>";
								echo "<div>".$trad["HEADER_MENU_espaces_dispo"]." :</div>";
								////	LISTE DES ESPACES
								foreach($espaces_affectes_user as $espace_tmp)
								{
									// Espace courant ?
									$style_text    = ($espace_tmp["id_espace"]==$_SESSION["espace"]["id_espace"])  ?  "lien_select"  :  "lien";
									$editer_espace = ($espace_tmp["id_espace"]==$_SESSION["espace"]["id_espace"])  ?  "&nbsp; <img src=\"".PATH_TPL."divers/parametrage.png\" style='height:18px;cursor:pointer;' onclick=\"edit_iframe_popup('".ROOT_PATH."module_espaces/espace_edit.php?id_espace=".$_SESSION["espace"]["id_espace"]."');\" title=\"".$trad["ESPACES_parametrage_infos"]."\" />"  :  "";
									// Pas d'infobulle() pour pas interférer avec "overflow-y" !
									echo "<div class='menu_context_ligne'><div class='menu_context_img'>&nbsp;</div><div class='menu_context_txt' ".$style_text."'><img src=\"".PATH_TPL."divers/fleche_droite.png\" /> <a href=\"".php_self()."?id_espace_acces=".$espace_tmp["id_espace"]."\" title='".$espace_tmp["description"]."'>".$espace_tmp["nom"]."</a>".$editer_espace."</div></div>";
								}
								////	PARAMETRAGE DES ESPACES
								if($_SESSION["user"]["admin_general"]==1)	echo "<hr class='menu_context_hr' />".$header_menu_module_espace;
							echo "</div>";
							echo $_SESSION["espace"]["nom"]." <img src=\"".PATH_TPL."divers/derouler.png\" />";
						echo "</span>";

						////	RACCOURCIS
						////
						////	RECUPERATION DES ELEMENTS VIA plugin.inc.php
						$cfg_plugin = array("resultats"=>array(), "mode"=>"raccourcis");
						if(isset($_SESSION["espace"]["modules"]) and count($_SESSION["espace"]["modules"])>0)
						{
							foreach($_SESSION["espace"]["modules"] as $infos_module)
							{
								$cfg_plugin["module_dossier"] = $infos_module["module_dossier_fichier"];
								// Récupère le "commun.inc.php" si c'est pas le module courant
								if(defined("MODULE_DOSSIER") && MODULE_DOSSIER!=$cfg_plugin["module_dossier"])	include_once ROOT_PATH.$cfg_plugin["module_dossier"]."/commun.inc.php";
								// Fichier "plugin.inc.php" pour le module en question ?
								if(is_file(ROOT_PATH.$cfg_plugin["module_dossier"]."/plugin.inc.php"))			include ROOT_PATH.$cfg_plugin["module_dossier"]."/plugin.inc.php";
							}
						}
						////	AFFICHAGE DES ELEMENTS
						if(count($cfg_plugin["resultats"])>0)
						{
							echo "<span class='lien' onMouseOver=\"afficher('liste_raccourcis',true);\" onMouseOut=\"afficher('liste_raccourcis',false);\">";
								echo "<div class='menu_context' id='liste_raccourcis'>";
								foreach($cfg_plugin["resultats"] as $elem){
									echo "<div class='menu_context_ligne lien'><div class='menu_context_img' onClick=\"".$elem["lien_js_icone"]."\"><img src='".PATH_TPL.$elem["module_dossier"]."/plugin.png' /></div><div class='menu_context_txt' onClick=\"".$elem["lien_js_libelle"]."\">".$elem["libelle"]."</div></div>";
								}
								echo "</div>";
								echo "<img src=\"".PATH_TPL."divers/separateur.gif\" />".$trad["HEADER_MENU_raccourcis"]." &nbsp;<img src=\"".PATH_TPL."divers/raccourci.png\" /> ";
							echo "</span>";
						}

						////	VALIDER L'INSCRIPTION D'UTILISATEURS
						////
						if($_SESSION["espace"]["droit_acces"]==2 && db_valeur("SELECT count(*) FROM  gt_utilisateur_inscription WHERE id_espace='".$_SESSION["espace"]["id_espace"]."'")>0)
							echo "<img src=\"".PATH_TPL."divers/separateur.gif\" /><span id='lib_inscription_users' class='lien' onClick=\"edit_iframe_popup('".ROOT_PATH."module_utilisateurs/utilisateur_inscription_validation.php');\">".$trad["inscription_users_validation"]." &nbsp;<img src=\"".PATH_TPL."divers/check.png\" /></span><script> $('#lib_inscription_users').effect('pulsate',{times:3},1000); </script>";
					}
					?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr style="vertical-align:top;line-height:25px;">
				<td>
					<?php
					////	LIVECOUNTER PRINCIPAL + ICONE MESSENGER
					if(livecounter_messenger_actif()==true)		echo " &nbsp; <span id='livecounter_principal'></span> &nbsp; <img src=\"".PATH_TPL."divers/messenger.png\" id='icone_messenger' style='vertical-align:top;cursor:pointer;".(count(users_connectes())==0?"display:none;":"")."' onClick=\"affichage_messenger();\" ".infobulle($trad["HEADER_MENU_messenger"])." />";
					?>
				</td>
				<td style="text-align:right;font-size:16px;font-style:italic;color:#eee;text-shadow: 2px 1px 1px #777;">
					<?php
					////	TITRE DU MODULE COURANT
					if($_SESSION["agora"]["libelle_module"]=="page" && isset($trad[strtoupper(MODULE_NOM)."_nom_module_header"]))
						echo majuscule($trad[strtoupper(MODULE_NOM)."_nom_module_header"]);
					?>
				</td>
			</tr>
		</table>
	</td>
	<td style="width:65px;background-image:url('<?php echo PATH_TPL."divers/".$bg_header_centre; ?>');background-repeat:no-repeat;background-position:top left">
		&nbsp;
	</td>
	<td id="td_icone_modules" class='pas_selection' style="<?php echo "background-image:url(".PATH_TPL."divers/".$bg_header_droite.");"; ?>background-repeat:repeat-x;">
		<?php
		////	MODULES
		////
		$icone_module_width = (@$_SESSION["cfg"]["resolution_width"]<=1024)  ?  "style='height:45px'"  :  "";
		echo "<table id='table_icone_modules' style='margin:0px;' cellpadding='1px;' cellspacing='0'><tr>";
		foreach($_SESSION["espace"]["modules"] as $module_tmp)
		{
			// Les invités n'affichent pas le module mail
            if (in_array($module_tmp['nom'], $supported_mods) && !droit_acces_module($module_tmp["nom"]) && !droit_ecriture_module($module_tmp["nom"]) || ($module_tmp['nom'] == 'droits_modules' && droit_acces_module($module_tmp["nom"]) && !droit_ecriture_module($module_tmp['nom']) && $_SESSION["user"]["admin_general"] !=1 ) ) { continue; }
			if($_SESSION["user"]["id_utilisateur"]<1 && $module_tmp["nom"]=="mail")	continue;
			// Style
			if(preg_match("/parametrage|espaces|logs/i",MODULE_DOSSIER))	$style_tmp = "icone_module_mask";
			elseif(MODULE_NOM==$module_tmp["nom"])							$style_tmp = "icone_module_select";
			else															$style_tmp = "icone_module";
			// Module utilisateurs
			if($module_tmp["nom"]=="utilisateurs") 	{ $chemin_tmp = ROOT_PATH.$module_tmp["module_dossier_fichier"]."/index.php?affichage_users=espace";	$description_tmp = $trad["UTILISATEURS_utilisateurs_espace"]; }
			else									{ $chemin_tmp = ROOT_PATH.$module_tmp["module_dossier_fichier"]."/";									$description_tmp = $trad[strtoupper($module_tmp["nom"])."_description_module"]; }
			// Libellé au dessus de l'icone ?
			$module_libelle_icones = ($_SESSION["agora"]["libelle_module"]=="icones")  ?  "<div style=\"text-align:center;margin-bottom:-3px;font-style:italic;font-weight:normal;".STYLE_FONT_COLOR_RETRAIT."\">".$trad[strtoupper($module_tmp["nom"])."_nom_module_header"]."</div>"  :"";
			// Affichage
			echo "<td>".$module_libelle_icones."<img src=\"".PATH_TPL.$module_tmp["module_dossier_fichier"]."/menu.png\" class='".$style_tmp."' ".$icone_module_width." onclick=\"module_animeico_redir(this,'".$chemin_tmp."');\"  ".infobulle("<div style='max-width:250px;text-align:center'>".$description_tmp."</div>")." /></td>";
		}
		echo "</tr></table>";
		?>
	</td>
</tr></table>



<!--  Ajustements largeur des icones des module  &  Div du son d'alerte du Messenger  -->
<script type="text/javascript"> element("td_icone_modules").style.width = element("table_icone_modules").offsetWidth + "px"; </script>
<hr style="visibility:hidden;height:60px;" />
<div id="div_son_alert_messerger" style="height:1px;"></div>



<!--  MESSENGER  -->
<?php if(livecounter_messenger_actif()==true){ ?>
<div id="calque_messenger" onMouseOver="$(this).draggable();" style="width:600px;height:450px;position:absolute;z-index:1000;display:none;left:400px;top:120px;background-image:url('<?php echo PATH_TPL; ?>divers/fond_messenger.png');">
	<div style="float:left;cursor:move;width:80%;height:25px;">&nbsp;</div>
	<img src="<?php echo PATH_TPL; ?>divers/supprimer.png" style="float:right;margin-top:5px;margin-right:10px;cursor:pointer;" OnClick="afficher_dynamic('calque_messenger','fade');" <?php echo infobulle($trad["fermer"]); ?>" />
	<table style="width:100%;height:420px;font-weight:bold;" cellpading="0" cellspaing="0">
		<tr>
			<td><div id="messenger_liste_messages" style="height:360px;overflow:auto;">&nbsp;</div></td>
			<td style="width:140px;padding:right:5px;"><div id="livecounter_messenger" style="height:360px;overflow:auto;">&nbsp;</div></td>
		</tr>
		<tr>
			<td style="text-align:center;padding-top:5px;" colspan="2">
				<img src="<?php echo PATH_TPL; ?>divers/telecharger.png" class="lien" <?php echo infobulle($trad["HEADER_MENU_enregistrer_conversation"]); ?> onClick="redir('<?php echo PATH_COMMUN; ?>messenger_enregistrer.php');"/>
			    <?php echo select_couleur("texte_messenger","couleur_messenger","text"); ?>
			    &nbsp;
			    <?php $color_tmp = (isset($_SESSION["couleur_messenger"])) ? $_SESSION["couleur_messenger"] : "#222"; ?>
				<input type="text" name="message" value="<?php echo $trad["HEADER_MENU_ajouter_message"]; ?>" maxlength="500" id="texte_messenger" style="width:400px;height:20px;font-weight:bold;background-color:transparent;<?php echo "border:solid 1px #fff;color:".$color_tmp.";".STYLE_BORDER_RADIUS; ?>" onFocus="if(this.value=='<?php echo $trad["HEADER_MENU_ajouter_message"]; ?>') this.value='';"  onKeyUp="if(event.keyCode==13) post_message_messenger();" />
				<input type="hidden" name="couleur_messenger" id="couleur_messenger" value="<?php echo $color_tmp; ?>" />
				<button OnClick="post_message_messenger();" /><?php echo $trad["envoyer"]; ?></button>
			</td>
		</tr>
	</table>
</div>
<?php } ?>