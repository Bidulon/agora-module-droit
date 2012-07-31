<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";
if($_SESSION["user"]["envoi_invitation"]!="1")	exit();
nb_users_depasse();
titre_popup($trad["UTILISATEURS_envoi_invitation"]);


////	ENVOI D'INVITATION PAR MAIL
////
if(isset($_POST["envoi_invitation"]))
{
	// Init
	$id_invitation = mt_rand(1000000000,9999999999);
	$password = mt_rand(10000,99999);
	$adresse_confirmation = $_SESSION["agora"]["adresse_web"]."?id_invitation=".$id_invitation."&mail=".urlencode($_POST["mail"]);
	$expediteur = $_SESSION["user"]["nom"]." ".$_SESSION["user"]["prenom"];
	$br = "<br />";
	// Envoi du mail d'invitation
	$objet_mail = $trad["UTILISATEURS_objet_mail_invitation"]." ".$expediteur; // Invitation de Jean DUPOND
	$contenu_mail  = "<b>".$expediteur." ".$trad["UTILISATEURS_admin_invite_espace"]." ".$_SESSION["espace"]["nom"]." :</b>".$br.$br; // Jean DUPOND vous invite à rejoindre l'espace Mon Espace :
	$contenu_mail .= $trad["login"]." : &nbsp; ".$_POST["mail"].$br;
	$contenu_mail .= $trad["pass"]." : &nbsp; ".$password.$br.$br;
	if($_POST["message"]!="")	$contenu_mail .= $_POST["message"].$br.$br;
	$contenu_mail .= "<a href=\"".$adresse_confirmation."\" target=\"_blank\"><u><b>".$trad["UTILISATEURS_confirmer_invitation"]."</u></b></a>"; // Confirmer l'invitation ?
	$envoi_mail = envoi_mail($_POST["mail"], $objet_mail, magicquotes_strip($contenu_mail), array("header_footer"=>false));
	// On ajoute l'invitation temporaire
	if($envoi_mail==true)	db_query("INSERT INTO gt_invitation SET id_invitation=".db_format($id_invitation).", id_espace='".$_SESSION["espace"]["id_espace"]."', nom=".db_format($_POST["nom"]).", prenom=".db_format($_POST["prenom"]).", mail=".db_format($_POST["mail"]).", pass=".db_format($password).", date_crea='".db_insert_date()."', id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."'");
}


////	SUPPRESSION D'INVITATION
////
if(isset($_GET["suppr_invitation"]))
{
	$infos_invit = db_valeur("SELECT count(*) FROM gt_invitation WHERE id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."' AND id_invitation='".$_GET["suppr_invitation"]."'");
	if($infos_invit > 0)	db_query("DELETE FROM gt_invitation WHERE id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."' AND id_invitation=".db_format($_GET["suppr_invitation"]));
}
?>


<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(500,350);

////    On contrôle les champs
function controle_formulaire()
{
	// Il doit y avoir un texte
	if (get_value("nom")=="" || get_value("prenom")=="" || get_value("mail")=="")		{ alert("<?php echo $trad["remplir_tous_champs"]; ?>"); return false; }
	// Vérification du mail
	if (controle_mail(get_value("mail"))==false)	{ alert("<?php echo $trad["mail_pas_valide"]; ?>");	return false; }
	// controle existance du mail
	requete_ajax("GET", "identifiant_verif.php?mail="+get_value("mail"));
	if(trouver("oui",Http_Request_Result))	{ alert("<?php echo $trad["UTILISATEURS_mail_deja_present"]; ?>"); return false; }
}
</script>

<style type="text/css">
body		{ background-image:url('<?php echo PATH_TPL; ?>module_utilisateurs/fond_popup.png'); font-weight:bold; }
.input_text	{ width:300px }
</style>


<form action="<?php echo php_self(); ?>" method="post" OnSubmit="return controle_formulaire();" style="padding:10px;">
	<table>
		<tr>
			<td width="150px"><?php echo $trad["nom"]; ?></td>
			<td><input type="text" name="nom" class="input_text" /></td>
		</tr>
		<tr>
			<td><?php echo $trad["prenom"]; ?></td>
			<td><input type="text" name="prenom" class="input_text" /></td>
		</tr>
		<tr>
			<td><?php echo $trad["mail"]; ?></td>
			<td><input type="text" name="mail" class="input_text" /></td>
		</tr>
		<tr>
			<td><?php echo $trad["UTILISATEURS_invitation_message"]; ?></td>
			<td><textarea name="message" class="input_text" style="height:35px;"><?php echo @$_POST["message"]; ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2" align="right" height="40px">
				<input type="hidden" name="envoi_invitation" value="1" />
				<input type="submit" value="<?php echo $trad["envoyer"]; ?>" class="button" />
			</td>
		</tr>
	</table>
</form>


<?php  $liste_invitations = db_tableau("SELECT * FROM gt_invitation WHERE id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."'");  ?>
<div style="position:absolute;bottom:0px;width:96%;text-align:center;">
	<div style="display:<?php echo (count($liste_invitations)>0)?"block":"none"; ?>;margin:7px;font-size:13px;" class="lien_select2" onClick="afficher('liste_invitations');resize_iframe_popup(500,400);">
		<img src="<?php echo PATH_TPL; ?>divers/acces_utilisateur_plus.png" /> &nbsp; <?php echo count($liste_invitations)." ".$trad["UTILISATEURS_invitation_a_confirmer"]; ?>
	</div>
	<ul id="liste_invitations" class="cacher">
		<?php foreach($liste_invitations as $invit)	{ echo"<li style='margin-bottom:5px;'>".$invit["nom"]." ".$invit["prenom"]." - ".$invit["mail"]." - ".info_espace($invit["id_espace"],"nom")." - ".temps($invit["date_crea"])." &nbsp; ".icone_suppr("invitation.php?suppr_invitation=".$invit["id_invitation"])."</li>"; }	?>
	</ul>
</div>


<?php require PATH_INC."footer.inc.php"; ?>