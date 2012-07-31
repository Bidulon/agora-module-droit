<?php
////	INIT
define("CONTROLE_SESSION",false);
require_once "../includes/global.inc.php";
require_once PATH_INC."header.inc.php";
nb_users_depasse();


////	INSCRIPTION DE L'UTILISATEUR
if(isset($_POST["envoi_inscription"]))
{
	db_query("INSERT INTO gt_utilisateur_inscription SET id_espace=".db_format($_POST["id_espace"],'insert_ext').", nom=".db_format($_POST["nom"],'insert_ext').", prenom=".db_format($_POST["prenom"],'insert_ext').", mail=".db_format($_POST["mail"],'insert_ext').", identifiant=".db_format($_POST["identifiant"],'insert_ext').", pass=".db_format($_POST["pass"],'insert_ext').", message=".db_format($_POST["message"],'insert_ext').", date='".db_insert_date()."'");
	alert($trad["inscription_users_enregistre"]);
	reload_close();
}
?>


<script type="text/javascript">
////	Redimensionne
resize_iframe_popup("500px","100%");

////	On contrôle les champs
function controle_formulaire()
{
	// Certains champs sont obligatoire
	if(get_value("nom")=="")		{ alert("<?php echo $trad["UTILISATEURS_specifier_nom"]; ?>");		return false; }
	if(get_value("prenom")=="")		{ alert("<?php echo $trad["UTILISATEURS_specifier_prenom"]; ?>");	return false; }
	// controle le mail (obligatoire + bien formaté + unique sur le site)
	if(get_value("mail")=="" || controle_mail(get_value("mail"))==false)	{ alert("<?php echo $trad["mail_pas_valide"]; ?>");  return false; }
	requete_ajax("GET", "../module_utilisateurs/identifiant_verif.php?mail="+get_value("mail"));
	if(trouver("oui",Http_Request_Result))	{ alert("<?php echo $trad["UTILISATEURS_mail_deja_present"]; ?>"); return false; }
	// controle l'identifiant (obligatoire + unique sur le site)
	if(get_value("identifiant")=="")	{ alert("<?php echo $trad["UTILISATEURS_specifier_identifiant"]; ?>");	return false; }
	requete_ajax("GET", "../module_utilisateurs/identifiant_verif.php?identifiant="+get_value("identifiant"));
	if(trouver("oui",Http_Request_Result))	{ alert("<?php echo $trad["UTILISATEURS_identifiant_deja_present"]; ?>"); return false; }
	// Spécifier mot de passe + vérif confirmation
	if(get_value("pass")=="" || get_value("pass")!=get_value("pass2"))	{ alert("<?php echo $trad["UTILISATEURS_specifier_pass"]; ?>");  return false; }
	// Vérif du captcha
	if(controle_captcha()==false)	return false;
}
</script>


<style type="text/css">
.input_text	{ width:300px; }
</style>


<form action="<?php echo php_self(); ?>" method="post" OnSubmit="return controle_formulaire();" style="margin-top:200px;">
	<fieldset>
		<table>
			<tr>
				<td><?php echo ucfirst($trad["ESPACES_espace"]); ?></td>
				<td>
				<select name="id_espace">
				<?php
				foreach(db_tableau("select * from gt_espace where inscription_users='1'") as $espace_tmp){
					echo "<option value='".$espace_tmp["id_espace"]."' title=\"".$espace_tmp["description"]."\">".$espace_tmp["nom"]."</option>";
				}
				?>
				</select>
				</td>
			<tr>
				<td width="150px"><?php echo $trad["nom"]; ?></td>
				<td><input type="text" name="nom" class="input_text" /></td>
			</tr>
			<tr>
				<td><?php echo $trad["prenom"]; ?></td>
				<td><input type="text" name="prenom" class="input_text" /></td>
			</tr>
			<tr>
				<td><?php echo ucfirst($trad["mail"]); ?></td>
				<td><input type="text" name="mail" class="input_text" /></td>
			</tr>
			<tr>
				<td><?php echo ucfirst($trad["identifiant"]); ?></td>
				<td><input type="text" name="identifiant" class="input_text" /></td>
			</tr>
			<tr>
				<td><?php echo ucfirst($trad["pass"]); ?></td>
				<td><input type="password" name="pass" class="input_text" style="width:150px;" /></td>
			</tr>
			<tr>
				<td><?php echo ucfirst($trad["pass2"]); ?></td>
				<td><input type="password" name="pass2" class="input_text" style="width:150px;" /></td>
			</tr>
			<tr>
				<td><?php echo $trad["UTILISATEURS_invitation_message"]; ?></td>
				<td><textarea name="message" class="input_text" style="height:35px;"><?php echo @$_POST["message"]; ?></textarea></td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<br /><br /><?php echo menu_captcha(); ?><br />
					<input type="hidden" name="envoi_inscription" value="1" />
					<input type="submit" value="<?php echo $trad["envoyer"]; ?>" class="button" />
				</td>
			</tr>
		</table>
	</fieldset>
</form>


<?php require PATH_INC."footer.inc.php"; ?>