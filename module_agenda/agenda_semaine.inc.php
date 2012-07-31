<?php
////	 PLAGE HORAIRE  &  DIMENSIONS DE L'AGENDA
////
$jour_secondes = 86400;
$nb_jours = round(($config["agenda_fin"]-$config["agenda_debut"]) / $jour_secondes);
// Plage horaire (étendue si ya des evt + tot / tard)
$plage_horaire = ($agenda_tmp["plage_horaire"]!="")  ?  explode("-",$agenda_tmp["plage_horaire"])  :  array("8","21");
foreach(liste_evenements($id_agenda, $config["agenda_debut"], $config["agenda_fin"]) as $evt_tmp)
{
	$heure_debut = abs(strftime("%H",strtotime($evt_tmp["date_debut"])));
	if($heure_debut < $plage_horaire[0])	$plage_horaire[0] = $heure_debut;
	if($plage_horaire[1] <= $heure_debut)	$plage_horaire[1] = $heure_debut + 1;
}
// Dimensions
$id_agenda_bis = "agenda".$id_agenda;
$width_colonne_heures = 40;
$width_ascenseur = 17;
$height_heures = round($height_agenda / ($plage_horaire[1]-$plage_horaire[0]));
if($height_heures<50)	$height_heures = 50;
$scroll_top_agenda = $height_heures * $plage_horaire[0];
$height_min_evt = round($height_heures/2);
if($height_min_evt < 30)	$height_min_evt = 30;


////	FONCTION DE MISE EN FORME D'UN AGENDA DE SEMAINE ET PLACEMENT DE SES EVENEMENTS
////
if(empty($cpt_block_evt))
{
	$cpt_block_evt = 0; //Premier agenda affiché : on déclare la fonction
?>
	<script type="text/javascript">
	////	CONSTRUCTION D'UN AGENDA
	////
	tab_evt = new Array();
	function agenda_semaine_construct(id_agenda, scroll_top)
	{
		////	MODIF LARGEUR AGENDA (DIV OVERFLOW)  &  CALCUL LARGEUR MOYENNE DES JOURS (EN FONCTION DE L'ENTETE)
		id_agenda_bis = "agenda"+id_agenda;
		largeur_agenda = element(id_agenda_bis+"_entete").offsetWidth;
		element(id_agenda_bis+"_contenu").style.width = largeur_agenda + "px";
		width_jour = Math.round(<?php echo "(largeur_agenda - ".($width_colonne_heures-$width_ascenseur).") / ".$nb_jours; ?>);
		////	MODIF LARGEUR JOURS ENTETE & CELLULES PREMIERE HEURE
		for(j=0; j < <?php echo $nb_jours; ?>; j++)
		{
			element(id_agenda_bis+"_libjour"+j).style.width	= width_jour+"px";
			element(id_agenda_bis+"_jour"+j+"_heure<?php echo $plage_horaire[0]; ?>").style.width = width_jour+"px";
		}

		////	RECUPERE LARGEUR REELLE DE CHAQUE JOUR AFFICHE (en fonction des cellules de premieres heures)
		var width_cells_jours = new Array();
		for(j=0; j < <?php echo $nb_jours; ?>; j++)		{ width_cells_jours[j] = element(id_agenda_bis+"_jour"+j+"_heure<?php echo $plage_horaire[0]; ?>").offsetWidth - 0.5; }

		////	CHAQUE EVENEMENT : PLACEMENT A GAUCHE + LARGEUR
		for(var i=0; i < tab_evt.length; ++i)
		{
			// evenement du même agenda?
			if(tab_evt[i]["id_agenda"]==id_agenda)
			{
				// D'autre evt sur le même crenaux horaire ?
				decalage_evt = 0;
				for(i2=0; i2 < tab_evt.length; ++i2)
				{
					// Début de l'evt courant dans le créneau de l'evt_tmp ? => on ajoute 20 pixels de décalage en plus
					if(tab_evt[i2]["id_agenda"]==id_agenda  &&  tab_evt[i]["id_evenement"]!=tab_evt[i2]["id_evenement"]  &&  tab_evt[i]["T_debut"]>=tab_evt[i2]["T_debut"]  &&  tab_evt[i]["T_debut"]<tab_evt[i2]["T_fin"]  &&  typeof tab_evt[i2]["decalage_evt"]=="number")
						decalage_evt = tab_evt[i2]["decalage_evt"] + 20;
				}
				// Enregistre le décalage  +  Placement horizontal evt  +  largeur evt  +  bordure evt?
				tab_evt[i]["decalage_evt"] = decalage_evt;
				var margin_left_tmp = <?php echo $width_colonne_heures; ?>;
				for(k=0; k < tab_evt[i]["jour"]; k++)	{ margin_left_tmp = margin_left_tmp + width_cells_jours[k]; }
				element(tab_evt[i]["id_div_evt"]).style.marginLeft	= (margin_left_tmp + decalage_evt) + "px";
				element(tab_evt[i]["id_div_evt"]).style.width		= (width_cells_jours[tab_evt[i]["jour"]] - decalage_evt - 1) + "px";
				if(decalage_evt>0 && element(tab_evt[i]["id_div_evt"]).style.border=="")	element(tab_evt[i]["id_div_evt"]).style.border = "1px solid #999";
			}
		}

		////	AFFICHAGE DE L'AGENDA  +  SCROLL A L'HEURE DEMANDEE
		element(id_agenda_bis+"_contenu").style.visibility = "visible";
		element(id_agenda_bis+"_contenu").scrollTop = scroll_top;
	}


	////	SELECTION D'HEURES
	////
	function select_heures(id_agenda, selected_jour, selected_heure)
	{
		// (re-)Initialise la sélection
		if(isMouseDown==false)
		{
			// Réinitialise le css des cellules (si l'heure n'est pas initialisée)
			if(select_heure_debut!=null)
			{
				for(jour_tmp=0; jour_tmp<<?php echo $nb_jours; ?>; jour_tmp++){
					for(heure_tmp=0; heure_tmp<24; heure_tmp++)		{ $("#agenda"+id_agenda+"_jour"+jour_tmp+"_heure"+heure_tmp).removeClass("cellule_heure_selected"); }
				}
			}
			// Réinitialise les variables
			selected_jour_tmp = select_heure_debut = select_heure_fin = null;
		}
		// Sélection d'heures
		else
		{
			// Initialise "selected_jour_tmp"
			if(selected_jour_tmp==null)		selected_jour_tmp = selected_jour;
			// Etend la plage horaire (on reste sur le même jour)
			if(selected_jour_tmp==selected_jour)
			{
				// Initialise le début & (re)définit la fin
				if(select_heure_debut==null)				select_heure_debut = selected_heure;
				if(select_heure_debut <= selected_heure)	select_heure_fin = selected_heure;
				// Sélectionne la cellule en CSS
				for(heure_tmp=0; heure_tmp<24; heure_tmp++){
					if(select_heure_debut <= heure_tmp && heure_tmp	<= select_heure_fin)	$("#agenda"+id_agenda+"_jour"+selected_jour+"_heure"+heure_tmp).addClass("cellule_heure_selected");
					else																	$("#agenda"+id_agenda+"_jour"+selected_jour+"_heure"+heure_tmp).removeClass("cellule_heure_selected");
				}
			}
		}
	}
	////	Initialise les variables  &  Détection de Mousedown / Mouseup
	isMouseDown = false;
	selected_jour_tmp = select_heure_debut = select_heure_fin = null;
	$(document).ready(function(){
		$('.cellule_heure').mousedown(function(){ isMouseDown=true; });
		$('body').mouseup(function(){ isMouseDown=false; });
	});
	</script>
<?php
}
?>


<style type="text/css">
.agenda_contenu			{ width:100%; margin:0px; padding:0px; <?php if(!isset($_GET["printmode"])) echo "position:absolute;overflow:auto;visibility:hidden;";/*PAS DE SCROLL EN PRINTMODE*/ ?> }
.ligne_heure			{ background-color:#fff; }
.ligne_heure_creuse		{ background-color:#f3f3f3; }
.cellule_heure_libelle	{ width:<?php echo $width_colonne_heures; ?>px; background-color:#ddd; text-align:center; vertical-align:top; color:#000; font-weight:bold; border-top:#eee solid 1px; }
.cellule_heure			{ border-width:1px 1px 1px 1px; border-color:#fff #ccc #ddd #fff; border-style:solid; } /* haut-droite-bas-hauche*/
.cellule_heure:hover	{ border:#333 inset 1px; }
.cellule_heure_old		{ background-color:#eee; }
.cellule_heure_courante	{ border-top:#f33 solid 1px; }
.cellule_heure_selected	{ background-color:#333; border:#000 solid 1px; <?php echo STYLE_BORDER_RADIUS; ?> }
/* IMPRESSION */
@media print {
	.block_evt	{ color:#000; }
}
</style>


<?php
////	ENTETE
////
echo "<table id='".$id_agenda_bis."_entete' style='width:100%;text-align:center;font-weight:bold;' cellpadding='0px' cellspacing='0px'><tr>";
	echo "<td style='width:".$width_colonne_heures."px;'>&nbsp;</td>";
	////	JOURS DE LA SEMAINE
	for($jour_tmp=0; $jour_tmp<$nb_jours; $jour_tmp++)
	{
		$jour_T = $config["agenda_debut"] + ($jour_secondes*$jour_tmp);
		$style_jour_tmp = (strftime("%Y-%m-%d",$jour_T)==strftime("%Y-%m-%d",time()))  ?  STYLE_SELECT_RED  :  "";
		if(array_key_exists(strftime("%Y-%m-%d",$jour_T),$tab_jours_feries))   $jour_ferie =  "&nbsp; <img src=\"".PATH_TPL."module_agenda/ferie.png\" ".infobulle($tab_jours_feries[strftime("%Y-%m-%d",$jour_T)])." />";   else   $jour_ferie = "";
		echo "<td id='".$id_agenda_bis."_libjour".$jour_tmp."' style='".$style_jour_tmp."'> ".formatime("%A %d/%m",$jour_T).$jour_ferie."</td>";
	}
	echo "<td style='width:".$width_ascenseur."px;'>&nbsp;</td>";
echo "</tr></table>";
?>


<!-- EVENEMENTS + TABLEAU -->
<div id="<?php echo $id_agenda_bis; ?>_conteneur2" style="height:<?php echo $height_agenda; ?>px;">
	<div id="<?php echo $id_agenda_bis; ?>_contenu" class="agenda_contenu pas_selection" style="height:<?php echo $height_agenda; ?>px;">
		<?php
		////	EVENEMENTS DE CHAQUE JOUR
		////
		for($jour_tmp=0; $jour_tmp < $nb_jours; $jour_tmp++)
		{
			////	AFFICHAGE DE CHAQUE EVENEMENT
			$T_jour_debut = $config["agenda_debut"] + ($jour_secondes*$jour_tmp);
			$T_jour_fin   = $T_jour_debut + $jour_secondes -1;
			$jour_ymd = strftime("%Y-%m-%d", $T_jour_debut);
			foreach(liste_evenements($id_agenda, $T_jour_debut, $T_jour_fin) as $evt_tmp)
			{
				////	INIT
				$T_evt_debut = strtotime($evt_tmp["date_debut"]);
				$T_evt_fin   = strtotime($evt_tmp["date_fin"]);
				$temps_evt = temps($evt_tmp["date_debut"],"mini",$evt_tmp["date_fin"]);
				$infobulle = $temps_evt." ".$evt_tmp["titre"]."<br /><span style='font-weight:normal'>".text_reduit(strip_tags($evt_tmp["description"]),300)."</span>";
				$evt_tmp["important"]	= ($evt_tmp["important"]>0)		?  "&nbsp;<img src=\"".PATH_TPL."divers/important_small.png\" />"  :  "";
				$evt_tmp["couleur_cat"]	= ($evt_tmp["id_categorie"]>0)	?  db_valeur("SELECT couleur FROM gt_agenda_categorie WHERE id_categorie='".$evt_tmp["id_categorie"]."'")  :  "#333";

				////	MENU CONTEXTUEL  &  PLACEMENT DE L'EVT  &  SCROOLTOP DE L'AGENDA
				$id_div_evt = "div_evt".$cpt_block_evt;
				$cfg_menu_elem = evt_cfg_menu_elem($evt_tmp, $agenda_tmp, $jour_ymd);
				$cfg_menu_elem["id_div_element"] = $id_div_evt;
				$cfg_menu_elem["action_click_block"] = "popup('evenement.php?id_evenement=".$evt_tmp["id_evenement"]."','".$evt_tmp["id_evenement"]."');";
				if($T_evt_debut <= $T_jour_debut && $T_jour_debut < $T_evt_fin)		{ $evt_position_top = 0; }
				else																{ $evt_position_top = $height_heures * (abs(strftime("%H",$T_evt_debut)) + abs(strftime("%M",$T_evt_debut)/60)); }
				$scroll_top_evt = $height_heures * ((strftime("%H",$T_evt_debut)) + round(strftime("%M",$T_evt_debut)/60,2));
				if($scroll_top_evt < $scroll_top_agenda)	$scroll_top_agenda = $scroll_top_evt-20;
				// Mode Impression : on remonte les evenements (margin-top), car on masque les heures précédant la plage horaire
				if(isset($_GET["printmode"]))	$evt_position_top -= round($plage_horaire[0]*$height_heures);

				////	HAUTEUR DE L'EVT
				// Evt simple  ||  Evt debut < periode < Evt fin  ||  Evt debut avant la periode + Evt fin dans la période  ||  Evt debut dans la periode + Evt fin après la période  ||  Evt dans la période (debut+fin)
				if($evt_tmp["date_debut"]==$evt_tmp["date_fin"])					{ $evt_height = $height_min_evt; }
				elseif($T_evt_debut <= $T_jour_debut && $T_jour_fin <= $T_evt_fin)	{ $evt_height = $height_heures * 24; }
				elseif($T_evt_debut < $T_jour_debut && $T_evt_fin <= $T_jour_fin)	{ $evt_height = $height_heures * (($T_evt_fin-$T_jour_debut)/3600);  }
				elseif($T_jour_debut <= $T_evt_debut && $T_jour_fin < $T_evt_fin)	{ $evt_height = $height_heures * (($T_jour_fin-$T_evt_debut)/3600);  }
				elseif($T_jour_debut <= $T_evt_debut && $T_evt_fin <= $T_jour_fin)	{ $evt_height = $height_heures * (($T_evt_fin-$T_evt_debut)/3600);  }
				// Hauteur inférieur à la taille minimum (& evenement périodique commencé avant la période affichée?)
				if($evt_height < $height_min_evt){
					$evt_height = ($evt_height<0)  ?  ($height_heures * (($T_evt_fin-$T_evt_debut)/3600))  :  $height_min_evt;
				}
				// HAUTEUR + LARGEUR (2 pixels de bordure)
				$evt_height = floor($evt_height)-2;

				////	TABLEAU JAVASCRIPT
				echo "<script type='text/javascript'>";
					echo "tab_evt[".$cpt_block_evt."] = new Array();";
					echo "tab_evt[".$cpt_block_evt."]['id_div_evt'] = '".$id_div_evt."';";
					echo "tab_evt[".$cpt_block_evt."]['id_evenement'] = ".$evt_tmp["id_evenement"].";";
					echo "tab_evt[".$cpt_block_evt."]['id_agenda'] = ".$id_agenda.";";
					echo "tab_evt[".$cpt_block_evt."]['jour'] = '".$jour_tmp."';";
					echo "tab_evt[".$cpt_block_evt."]['T_debut'] = ".$T_evt_debut.";";
					echo "tab_evt[".$cpt_block_evt."]['T_fin'] = ".$T_evt_fin.";";
				echo "</script>";

				////	AFFICHAGE
				echo "<div id='".$id_div_evt."' class='block_evt' style='position:absolute;margin-top:".$evt_position_top."px;height:".$evt_height."px;".style_evt($agenda_tmp,$evt_tmp)."'>";
					require PATH_INC."element_menu.inc.php";
					echo "<div class='div_evt_contenu' style='height:80%;overflow:hidden;' ".infobulle($infobulle)." >";
						echo "<b>".$temps_evt."</b> &nbsp; ".$evt_tmp["titre"].$evt_tmp["important"];
					echo "</div>";
				echo "</div>";
				$cpt_block_evt ++;
			}
		}

		////	CORPS DE L'AGENDA (MATRICE DES JOURS / HEURES)
		////
		echo "<table id='".$id_agenda_bis."_tableau' cellpadding='0px' cellspacing='0px'>";
		for($heure_tmp=0; $heure_tmp < 24; $heure_tmp++)
		{
			////	IMPRESSION : ON AFFICHE PAS LES HEURES EN DEHORS DE LA PLAGE HORAIRE
			if(isset($_GET["printmode"]) && ($plage_horaire[0] > $heure_tmp ||  $heure_tmp > $plage_horaire[1]))	continue;
			////	STYLE DE LA LIGNE DE L'HEURE & AFFICHAGE A L'IMPRESSION (si demandé, on masque les heures qui ne sont pas dans la plage horaire)
			$style_heure =  (($plage_horaire[0]<=$heure_tmp && $heure_tmp<12) || ($heure_tmp>=14 && $heure_tmp<$plage_horaire[1]))  ?  "ligne_heure"  :  "ligne_heure_creuse";
			echo "<tr class='".$style_heure."' style='height:".$height_heures."px;'>";
				echo "<td class='cellule_heure_libelle'>".$heure_tmp.":00</td>";
				////	CELLULE HEURE DE CHAQUE JOUR
				for($jour_tmp=0; $jour_tmp < $nb_jours; $jour_tmp++)
				{
					$agenda_proposer_affecter_evt = agenda_proposer_affecter_evt($agenda_tmp);
					$infobulle_ajouter = $lien_ajouter = $cellule_heure_old = $cellule_heure_courante = "";
					////	AJOUTER UN EVT ?
					if($agenda_proposer_affecter_evt!="")
					{
						$T_heure = $config["agenda_debut"] + ($jour_secondes*$jour_tmp) + (3600*$heure_tmp);
						$proposer = ($agenda_proposer_affecter_evt=="proposer") ? "(".$trad["AGENDA_proposer"].")" : "";
						if(($T_heure+3600) < time())	$cellule_heure_old = "cellule_heure_old";
						if(strftime("%Y-%m-%d %H",$T_heure)==strftime("%Y-%m-%d %H",time()))	$cellule_heure_courante = "cellule_heure_courante";
						$lien_ajouter = "onMouseMove=\"select_heures(".$id_agenda.", ".$jour_tmp.",".$heure_tmp.");\" onMouseUp=\"edit_iframe_popup('evenement_edit.php?id_agenda=".$id_agenda."&date=".$T_heure."&select_heure_debut='+select_heure_debut+'&select_heure_fin='+select_heure_fin);\"";
					}
					echo "<td id=\"".$id_agenda_bis."_jour".$jour_tmp."_heure".$heure_tmp."\" class='cellule_heure ".$cellule_heure_old." ".$cellule_heure_courante."'  ".infobulle("<img src='".PATH_TPL."divers/plus.png' /> ".$trad["AGENDA_ajouter_evt_heure"]." ".$heure_tmp.$trad["separateur_horaire"]." ".$proposer)."  ".$lien_ajouter.">&nbsp;</td>";
				}
			echo "</tr>";
		}
		echo "</table>";
		?>
	</div>
</div>


<script type="text/javascript">  $(window).load(function(){  <?php echo "agenda_semaine_construct('".$id_agenda."',".$scroll_top_agenda.");"; ?>  });  </script>
