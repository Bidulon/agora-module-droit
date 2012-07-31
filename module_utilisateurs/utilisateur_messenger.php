<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";
droit_modif_utilisateur($_REQUEST["id_utilisateur"],true);


////	VALIDATION DU FORMULAIRE DE VISIBILITE DU LIVECOUNTER & DU MESSENGER
////
if(isset($_POST["id_utilisateur"])) {
	// On réinitialise la table de jointure
	db_query("DELETE FROM gt_jointure_messenger_utilisateur WHERE id_utilisateur_messenger=".db_format($_POST["id_utilisateur"]));
	// On affecte à tous les utilisateurs
	if($_POST["selection_utilisateurs"]=="tous")	db_query("INSERT INTO gt_jointure_messenger_utilisateur SET id_utilisateur_messenger=".db_format($_POST["id_utilisateur"]).", id_utilisateur=null, tous_utilisateurs='1'");
	// On affecte à certains utilisateurs
	if($_POST["selection_utilisateurs"]=="certains")	{    foreach($_POST["liste_users"] as $id_user)  { db_query("INSERT INTO gt_jointure_messenger_utilisateur SET id_utilisateur_messenger=".db_format($_POST["id_utilisateur"]).", id_utilisateur=".db_format($id_user).", tous_utilisateurs=null"); }    }
	reload_close();
}


////	MENU PRINCIPAL DU LIVECOUNTER
$style_aucun = $style_tous = $style_certains = "lien";
$check_aucun = $check_tous = $check_certains = "";
$aucun_utilisateur = db_valeur("SELECT count(*) FROM gt_jointure_messenger_utilisateur WHERE id_utilisateur_messenger='".intval($_GET["id_utilisateur"])."'");
$tous_utilisateur = db_valeur("SELECT count(*) FROM gt_jointure_messenger_utilisateur WHERE id_utilisateur_messenger='".intval($_GET["id_utilisateur"])."' AND tous_utilisateurs='1'");
if($aucun_utilisateur==0)		{ $check_aucun = "checked";		$style_aucun = "lien_select";		$div_certains  = "none";  }
elseif($tous_utilisateur > 0)	{ $check_tous = "checked";		$style_tous = "lien_select";		$div_certains  = "none";  }
else							{ $check_certains = "checked";	$style_certains = "lien_select";	$div_certains  = "block";  }
?>


<style type="text/css">  body { background-image:url('<?php echo PATH_TPL; ?>module_utilisateurs/fond_popup.png'); } </style>
<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(470,400);

////	AFFICHAGE DE LA LISTE DES UTILISATEURS
function Affiche_Utilisateurs(methode)
{
	// Réinitialisation
	element("txt_aucun").className="lien";
	element("txt_tous").className="lien";
	element("txt_certains").className="lien";
	
	// On sélectionne le bon bouton radio lors d'une sélection par texte
	if(methode=="aucun")		{ set_check("input_aucun", true);      element("txt_aucun").className="lien_select"; }
	else if(methode=="tous")	{ set_check("input_tous", true);       element("txt_tous").className="lien_select"; }
	else						{ set_check("input_certains", true);   element("txt_certains").className="lien_select"; }
	
	// On affiche ou masque la liste des utilisateurs
	if(methode=="certains")    { afficher("block_users", true); }
	else                       { afficher("block_users", false); }
}
</script>


<h4 class="content" align="center"><?php echo $trad["UTILISATEURS_visibilite_messenger_livecounter"]; ?></h4>


<form action="<?php echo php_self(); ?>" method="post" style="padding:10px;font-weight:bold;">

	<div style="padding:10px;">
		<input type="radio" name="selection_utilisateurs" value="aucun" id="input_aucun" onClick="Affiche_Utilisateurs('aucun');" <?php echo $check_aucun; ?> /> &nbsp; <span class="<?php echo $style_aucun; ?>" style="cursor:pointer" id="txt_aucun" onClick="Affiche_Utilisateurs('aucun');"><?php echo $trad["UTILISATEURS_voir_aucun_utilisateur"]; ?></span><br />
		<input type="radio" name="selection_utilisateurs" value="tous" id="input_tous" onClick="Affiche_Utilisateurs('tous');" <?php echo $check_tous; ?> /> &nbsp; <span class="<?php echo $style_tous; ?>" style="cursor:pointer" id="txt_tous" onClick="Affiche_Utilisateurs('tous');"><?php echo $trad["UTILISATEURS_voir_tous_utilisateur"]; ?></span><br />
		<input type="radio" name="selection_utilisateurs" value="certains" id="input_certains" onClick="Affiche_Utilisateurs('certains');" <?php echo $check_certains; ?> /> &nbsp;	<span class="<?php echo $style_certains; ?>" style="cursor:pointer" id="txt_certains" onClick="Affiche_Utilisateurs('certains');"><?php echo $trad["UTILISATEURS_voir_certains_utilisateur"]; ?> </span><br />
		
		<div style="display:<?php echo $div_certains; ?>;" id="block_users">
			<table class="pas_selection" style="margin-left:30px;margin-top:10px;"
<?php
			////	LISTE DES UTILISATEURS
			////
			$users_tmp = users_visibles(user_infos($_REQUEST["id_utilisateur"]));
			if(count($users_tmp) > 0)
			{
				// Liste des utilisateurs restreints
				$users_restreints = db_colonne("SELECT id_utilisateur FROM gt_jointure_messenger_utilisateur WHERE id_utilisateur_messenger='".intval($_GET["id_utilisateur"])."' AND id_utilisateur > 0");
				// Affichage des utilisateurs
				foreach($users_tmp as $compteur => $user_tmp)
				{					
					// Acces de l'utilisateur en question au messenger ?
					if((in_array($user_tmp["id_utilisateur"],$users_restreints)))	{ $check_user = "checked";	$style_user = "lien_select"; }
					else															{ $check_user = "";			$style_user = "lien"; }
					$id_tmp = "user".$compteur;
				    //	On affiche!
				    echo "<tr>";
				    	echo "<td style='width:150px' class='".$style_user."' id='txt_".$id_tmp."' onClick=\"check_txt_box(this.id,'".$id_tmp."');\">".$user_tmp["prenom"]." ".$user_tmp["nom"]."</td>";
						echo "<td style='width:20px'><input type='checkbox' name='liste_users[]' value='".$user_tmp["id_utilisateur"]."' id='box_".$id_tmp."' onClick=\"check_txt_box(this.id,'".$id_tmp."');\" ".$check_user." /></td>";
					echo "</tr>";
				}
			}
			////	PAS D'UTILISATEUR
			else	{ echo "<tr style='margin-left:40px;font-weight:normal'><td colspan='2'>".$trad["UTILISATEURS_aucun_utilisateur_messenger"]."</td></tr>"; }
?>
			</table>
		</div>
	</div>

	<div style="text-align:right;margin-top:20px;">
		<input type="hidden" name="id_utilisateur" value="<?php echo $_GET["id_utilisateur"]; ?>" />
		<input type="submit" value="<?php echo $trad["modifier"]; ?>" class="button_big" />
	</div>

</form>


<?php require PATH_INC."footer.inc.php"; ?>
