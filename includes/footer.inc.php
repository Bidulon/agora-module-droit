<?php
////	Tps d'execution
$mtime = explode(" ",microtime());
$endtime = $mtime[1] + $mtime[0];
$tps_execution = round($endtime-$starttime, 2);
if(is_file(ROOT_PATH."host.inc.php"))	alert_domaine();

////	PAGE PRINCIPALE
if(IS_MAIN_PAGE==true)
{
	// LOGO DANS LA BARRE DE MENU DU HAUT  +  MENU CONTEXTUEL DE GAUCHE FLOTTANT (si plus petit que la hauteur de la page)
	$logo_tmp = (@$_SESSION["agora"]["skin"]=="blanc")  ?  PATH_TPL."divers/logo_noir.png"  :  PATH_TPL."divers/logo.png";
	echo "<script type='text/javascript'>";
		echo "element('menu_principal_logo_agora').innerHTML = \"<a href='".URL_AGORA_PROJECT."' target='_blank' title='".URL_AGORA_PROJECT." - Page générée en ".$tps_execution." secondes'><img src='".$logo_tmp."' /></a>\";";
		echo "if(existe('menu_gauche_block_flottant') && (navigateur()!='ie' || version_ie()>7) && (element('contenu_principal_table').offsetTop + element('menu_gauche_block_flottant').offsetHeight) < document.documentElement.clientHeight)  $('#menu_gauche_block_flottant').makeFloat({x:'current',y:'current',Speed:'fast'});";
	echo "</script>";
	// ENREGISTRE LA CONFIG DU NAVIGATEUR EN AJAX
	if(empty($_SESSION["cfg"]["navigateur"]))
		echo "<script type='text/javascript'>  requete_ajax(\"GET\", \"".PATH_DIVERS."browser_config.php?resolution_width=\"+$(document).width()+\"&resolution_height=\"+$(document).height()+\"&navigateur=\"+navigateur());  </script>";
	// LOGO DANS LE FOOTER DU BAS
	echo "<a href=\"".$_SESSION["agora"]["logo_url"]."\" target='_blank' style='position:fixed;bottom:0px;right:0px;z-index:1000;margin:5px;' id='footer_debug'><img src=\"".path_logo_footer()."\" id='logo_footer' style='max-height:".@$_SESSION["logo_footer_height"]."px;'  ".infobulle(URL_AGORA_PROJECT."<br />generation : ".$tps_execution." sec.")." /></a>";
}

////	FOOTER HTML (stats, etc)
if(@$_SESSION["agora"]["footer_html"]!="")	echo $_SESSION["agora"]["footer_html"];

////	MESSAGE D'ALERTE
if(isset($_GET["msg_alerte"]))	alert($trad["MSG_ALERTE_".$_GET["msg_alerte"]]);

////	FERMETURE BDD
db_close();
?>


</body>
</html>