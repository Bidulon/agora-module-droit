<!DOCTYPE html>
<html>

<head>
	<!--  AGORA-PROJECT is under the GNU General Public License (http://www.gnu.org/licenses/gpl.html)  -->
	<title><?php echo $_SESSION["agora"]["nom"]; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="content-language" content="<?php echo $trad["HEADER_HTTP"]; ?>" />
	<meta http-equiv="cache-control" content="no-cache"> 
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta name="Description" content="<?php echo $_SESSION["agora"]["description"]." - ".@$_SESSION["espace"]["description"]; ?>">
	<link rel="icon" type="image/gif" href="<?php echo PATH_TPL; ?>divers/icone.gif" />
	<?php
	////	STYLE CSS
	include_once PATH_TPL."style.css.php";
	////	EDITION DES ELEMENTS DANS UN POPUP/IFRAME? DANS L'ESPACE?
	echo "<script type=\"text/javascript\">  edition_popup='".@$_SESSION["agora"]["edition_popup"]."';  </script>";
	?>
	<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>jquery/jquery-ui.js"></script>
	<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>jquery/effects.js"></script>
	<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>jquery/pulsate.js"></script>
	<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>jquery/floating.js"></script>
	<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>javascript.js"></script> <!-- toujours après Jquery -->
</head>

<body onkeyup="action_clavier(event);">

	<div id="infobulle" class="infobulle">&nbsp;</div>

	<?php
	////	IMAGE BACKGROUND
	if(IS_MAIN_PAGE==true)
		echo "<div style='z-index:-1000000;position:fixed;left:0px;top:0px;height:100%;width:100%;'><img src=\"".$_SESSION["cfg"]["espace"]["fond_ecran"]."\" class='noprint' style='width:100%;height:100%;' /></div>";
	?>

	<div id="page_fantome" style="position:fixed;top:0px;left:0px;z-index:100000;display:none;width:100%;height:100%;text-align:center;vertical-align:middle;color:#fff;<?php echo STYLE_FOND_OPAQUE; ?>">
		<button onClick="page_fantome_close();" id="page_fantome_fermer" class="button" style="position:absolute;top:0px;right:0px;margin:3px;font-style:italic;width:120px;"><?php echo $trad["fermer"]; ?> <img src="<?php echo PATH_TPL; ?>divers/supprimer.png" /></button>
		<table  style="width:100%;height:100%;text-align:center;vertical-align:middle;" cellpadding="0" cellspacing="0"><tr><td>
			<div id="page_fantome_contenu"></div>
			<iframe id="page_fantome_iframe" name="page_fantome_iframe" allowtransparency="true" frameborder="0">NO IFRAME</iframe>
		</td></tr></table>
	</div>