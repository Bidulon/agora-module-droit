<?php
////	INIT
define("ROOT_PATH","./");
define("IS_MAIN_PAGE",true);
define("CONTROLE_SESSION",false);
require_once "./includes/global.inc.php";
require_once PATH_INC."header.inc.php";

////	REINITIALISATION DU MOT DE PASSE
////
if(isset($_GET["id_newpassword"]) && isset($_GET["id_utilisateur"]))
{
	$user_tmp = db_ligne("SELECT * FROM gt_utilisateur WHERE id_newpassword=".db_format($_GET["id_newpassword"])." AND id_utilisateur='".intval($_GET["id_utilisateur"])."'");
	////	"id_password" expiré
	if(count($user_tmp)==0)  { alert($trad["PASS_OUBLIE_id_newpassword_expire"]); }
	////	"id_password" valide
	else
	{
		// On demande un nouveau mot de passe  (récup les $_GET avec $_SERVER["QUERY_STRING"])
		if(!isset($_GET["password"]))	{ echo "<script type='text/javascript'>  prompt_page_fantome(\"".$trad["PASS_OUBLIE_prompt_changer_pass"]."\", \"redir('index.php?".$_SERVER["QUERY_STRING"]."&password='+get_value('prompt_result'));\");  </script>"; }
		// Enregistrement du nouveau mot de passe
		else{
			db_query("UPDATE gt_utilisateur SET pass='".sha1_pass($_GET["password"])."', id_newpassword=null WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."'");
			$_COOKIE["AGORAP_LOG"] = $user_tmp["identifiant"];
			alert($trad["PASS_OUBLIE_password_reinitialise"]);
		}
	}
}


////	CONFIRMER INVITATION
////
if(isset($_GET["id_invitation"]) && isset($_GET["mail"]))
{
	$invitation_tmp = db_ligne("SELECT * FROM gt_invitation WHERE id_invitation='".intval($_GET["id_invitation"])."' AND mail=".db_format($_GET["mail"],"insert_ext"));
	////	"id_invitation" expiré
	if(count($invitation_tmp)==0)  { alert($trad["UTILISATEURS_id_invitation_expire"]); }
	////	"id_invitation" valide
	else
	{
		// Choix du mot de passe avant validation
		if(!isset($_GET["password"]))	{ echo "<script type='text/javascript'>  prompt_page_fantome(\"".$trad["UTILISATEURS_invitation_confirmer_password"]."\", \"redir('index.php?".$_SERVER["QUERY_STRING"]."&password='+get_value('prompt_result'));\", \"text\", \"".$invitation_tmp["pass"]."\");  </script>"; }
		// Enregistrement du nouvel utilisateur
		elseif(nb_users_depasse(false,false)!=true)
		{
			$id_user_tmp = creer_utilisateur(addslashes(@$invitation_tmp["nom"]), addslashes(@$invitation_tmp["prenom"]), addslashes($invitation_tmp["mail"]), addslashes($_GET["password"]), addslashes($invitation_tmp["id_espace"]), addslashes($invitation_tmp["mail"]));
			if($id_user_tmp > 0){
				db_query("DELETE FROM gt_invitation WHERE id_invitation=".db_format($_GET["id_invitation"]));
				$_COOKIE["AGORAP_LOG"] = user_infos($id_user_tmp,"identifiant"); //Préremplis le champ 'login'
				alert($trad["UTILISATEURS_invitation_valide"]);
			}
		}
	}
}
?>



<style> .options_connexion { margin-left:15px; } </style>

<script type="text/javascript">
////	ACCES INVITE  (direct / mot de passe)
////
function Espace_Public(id_espace, mot_de_passe)
{
	// Config du navigateur
	url_config_navigateur = "&resolution_width="+$(document).width()+"&resolution_height="+$(document).height()+"&navigateur="+navigateur();
	// Accès direct sans password  /  Saisie du mot de passe  /  Controle Ajax du mot de passe
	if(mot_de_passe=="0")			{ redir("index.php?id_espace_acces="+id_espace+url_config_navigateur); }
	else if(mot_de_passe=="1")		{ prompt_page_fantome("<?php echo $trad["pass"]; ?>", "Espace_Public('"+id_espace+"',get_value('prompt_result'));", "password"); }
	else if(mot_de_passe.length > 2){
		requete_ajax("GET", "divers/espace_password_verif.php?password="+mot_de_passe+"&id_espace="+id_espace);
		if(trouver("oui",Http_Request_Result))	{ redir('index.php?id_espace_acces='+id_espace+'&password='+mot_de_passe+url_config_navigateur); }
		else									{ alert("<?php echo $trad["espace_password_erreur"]; ?>"); return false; }
	}
}

////	version IE inférieur à 7 ?
////
if(navigateur()=="ie" && version_ie()<7)	alert("<?php echo $trad["version_ie"]; ?>");

////	AFFICHAGE LA PAGE DE CONNEXION AVEC "FADE"
////
$(document).ready(function(){
	$("#div_entete").fadeIn(800);
	$("#div_espaces_publics").fadeIn(1000);
	$("#div_connexion").fadeIn(1000);
	element("login").focus();
});
</script>



<table id="div_entete" style="display:none;padding:5px;width:100%;height:25px;background-image:url('<?php echo PATH_TPL."divers/".(@$_SESSION["agora"]["skin"]=="blanc"?"header_droite.png":"header_droite_noir.png"); ?>');"><tr>
	<td style="text-align:left;font-size:16px;font-weight:bold;"><?php echo @$_SESSION["agora"]["nom"]; ?></td>
	<td style="text-align:right;"><?php if(@$_SESSION["agora"]["description"]!="") echo $_SESSION["agora"]["description"]; ?></td>
</tr></table>
<hr style="visibility:hidden;margin-top:150px;" />


<?php
////	ESPACES PUBLICS
////
$liste_espaces = espaces_affectes_user();
if(count($liste_espaces)>0)
{
	echo "<div id='div_espaces_publics' class='div_accueil pas_selection' style='font-size:14px;position:relative;display:none;'>";
		echo "<div style='position:absolute;top:-20px;left:-20px;'><img src=\"".PATH_TPL."divers/connexion_public.png\" /></div>";
		echo "<table style='margin:auto;line-height:25px;font-size:14px;' cellpadding='15px'><tr>";
			echo "<td>".$trad["acces_invite"]." :</td>";
			echo "<td style='text-align:left;'>";
			foreach($liste_espaces as $infos_espace){
				echo "<div onClick=\"Espace_Public('".$infos_espace["id_espace"]."','".($infos_espace["password"]!=""?"1":"0")."');\" class='lien' style='font-size:13px;'><img src=\"".PATH_TPL."divers/point_blanc.png\" />&nbsp; ".$infos_espace["nom"]."</div>";
			}
			echo "</td>";
		echo "</tr></table>";
	echo "</div>";
}
?>


<form action="<?php echo php_self(); ?>" method="post" OnSubmit="return controle_connexion('<?php echo addslashes($trad["specifier_login_password"]) ?>','<?php echo $trad["login"]; ?>','<?php echo $trad["pass"]; ?>');" id="div_connexion" class="div_accueil" style="margin-top:50px;position:relative;display:none;">
	<div style="position:absolute;margin-top:-20px;margin-left:-20px;"><img src="<?php echo PATH_TPL; ?>divers/connexion.png" /></div>
	<table style="width:100%;margin:auto;" cellpadding="20px;">
		<tr>
			<td class="options_connexion">
				<input type="text" name="login" value="<?php echo @$_COOKIE["AGORAP_LOG"]; ?>" style="width:160px" <?php echo infobulle($trad["login"]); ?> /> &nbsp;
				<input type="password" name="password" value="<?php echo $trad["pass"]; ?>" style="width:100px" onfocus="if(this.value=='<?php echo $trad["pass"]; ?>') this.value='';" <?php echo infobulle($trad["pass"]); ?> /> &nbsp;
				<input type="submit" value="<?php echo $trad["connexion"]; ?>" class="button" />
				<script type="text/javascript">
					// Config du navigateur
					document.write("<input type='hidden' name='resolution_width' value='"+$(document).width()+"' />");
					document.write("<input type='hidden' name='resolution_height' value='"+$(document).height()+"' />");
					document.write("<input type='hidden' name='navigateur' value='"+navigateur()+"' />");
				</script>
			</td>
		</tr>
		<tr>
			<td style="text-align:right;font-size:11px;padding:2px;">
				<?php
				////	S'INSCRIRE SUR LE SITE
				if(db_valeur("select count(*) from gt_espace where inscription_users='1'")>0)
					echo "<span class='lien options_connexion' onClick=\"iframe_page_fantome('./module_utilisateurs/utilisateur_inscription.php','500px');\" ".infobulle($trad["inscription_users_info"],500).">".$trad["inscription_users"]."&nbsp; <img src=\"".PATH_TPL."divers/check.png\" style='height:15px;' /></span> ";
				////	PASSWORD OUBLIE
				echo "<span class='lien  options_connexion' id='password_oublie' onClick=\"popup('".PATH_DIVERS."password_oublie.php');\" ".infobulle($trad["password_oublie_info"],500).">".$trad["password_oublie"]."</span> ";
				if(@$_GET["msg_alerte"]=="identification")	echo "<script>  $('#password_oublie').css('color','#d00');  $('#password_oublie').effect('pulsate',{times:5},1000); </script>";
				////	RESTER CONNECTE
				echo "<span class='options_connexion' ".infobulle($trad["connexion_auto_info"],500)." >";
					echo "<span class='lien' id='txt_connexion_auto' onClick=\"check_txt_box(this.id,'connexion_auto');\">".$trad["connexion_auto"]."</span>";
					echo "<input type='checkbox' name='connexion_auto' value='1' id='box_connexion_auto' onClick=\"check_txt_box(this.id,'connexion_auto');\" />";
				echo "</span>";
				////	MEMORISER ID
				echo "<span class='options_connexion' ".infobulle($trad["memoriser_identifiant_info"])." >";
					echo "<span class='lien' id='txt_memoriser_identifiant' onClick=\"check_txt_box(this.id,'memoriser_identifiant');\">".$trad["memoriser_identifiant"]."</span>";
					echo "<input type='checkbox' name='memoriser_identifiant' value='1' id='box_memoriser_identifiant' onClick=\"check_txt_box(this.id,'memoriser_identifiant');\" />";
				echo "</span>";
				?>
			</td>
		</tr>
	</table>
</form>


<?php require PATH_INC."footer.inc.php"; ?>