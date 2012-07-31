<style type="text/css">
<?php
////	SKIN PRINCIPAL DE L'AGORA : BLANC / NOIR
////
define("STYLE_FONT_BOLD", "font-weight:bold; font-size:11px;");
define("STYLE_BORDER_RADIUS", "border-radius:4px; -moz-border-radius:4px;");
define("STYLE_BORDER_RADIUS2", "border-radius:2px; -moz-border-radius:2px;");
define("STYLE_SHADOW", "box-shadow: 3px 3px 7px #444 ;-moz-box-shadow: 3px 3px 7px #444;");
define("STYLE_SHADOW_SELECT", "box-shadow: 3px 3px 7px #eee; -moz-box-shadow: 3px 3px 7px #eee;");
define("STYLE_SHADOW_LEGER", "box-shadow: 2px 2px 5px #999; -moz-box-shadow: 2px 2px 5px #999;");
if(@$_SESSION["agora"]["skin"]=="blanc")
{
	define("STYLE_FONT_COLOR", "color:#000;");
	define("STYLE_FONT_COLOR_RETRAIT", "color:#444;");
	define("STYLE_SELECT_BLUE", "color:#039;");
	define("STYLE_SELECT_YELLOW", "color:#c60;");
	define("STYLE_SELECT_ORANGE", "color:#d40;");
	define("STYLE_SELECT_RED", "color:#d00;");
	define("STYLE_TR_SELECT", "#bbb");
	define("STYLE_TR_DESELECT", "#f9f9f9");
	define("STYLE_BACKGROUND_COLOR", "background-color:#fff;");
	define("STYLE_BACKGROUND_COLOR2", "background-color:#ddd;");
	define("STYLE_FOND_OPAQUE", "background-image:url(".PATH_TPL."divers/fond_opaque.png);");
	define("STYLE_BORDER_IE",(@$_SESSION["cfg"]["navigateur"]=="ie"?"border:#bbb solid 1px;":""));
	define("STYLE_BORDER_MENU_CONTEXT","border:#bbb solid 1px;");
	define("STYLE_BLOCK", STYLE_BORDER_IE.STYLE_BORDER_RADIUS.STYLE_SHADOW.STYLE_BACKGROUND_COLOR."background-image:url(".PATH_TPL."divers/background.jpg); background-repeat:no-repeat;");
	define("STYLE_BLOCK_SELECT", STYLE_BORDER_IE.STYLE_BORDER_RADIUS.STYLE_SHADOW_SELECT."background-color:#aaa;");
	define("STYLE_ELEMENT_LISTE", "width:99.5%; margin:0px; border-bottom:#ccc solid 1px;");
}
else
{
	define("STYLE_FONT_COLOR", "color:#fff;");
	define("STYLE_FONT_COLOR_RETRAIT", "color:#ccc;");
	define("STYLE_SELECT_BLUE", "color:#9bf;");
	define("STYLE_SELECT_YELLOW", "color:#fa0;");
	define("STYLE_SELECT_ORANGE", "color:#f60;");
	define("STYLE_SELECT_RED", "color:#f55;");
	define("STYLE_TR_SELECT", "#444");
	define("STYLE_TR_DESELECT", "#222");
	define("STYLE_BACKGROUND_COLOR", "background-color:#000;");
	define("STYLE_BACKGROUND_COLOR2", "background-color:#333;");
	define("STYLE_FOND_OPAQUE", "background-image:url(".PATH_TPL."divers/fond_opaque_noir.png);");
	define("STYLE_BORDER_IE",(@$_SESSION["cfg"]["navigateur"]=="ie"?"border:#555 solid 1px;":""));
	define("STYLE_BORDER_MENU_CONTEXT","border:#555 solid 1px;");
	define("STYLE_BLOCK", STYLE_BORDER_IE.STYLE_BORDER_RADIUS.STYLE_SHADOW.STYLE_BACKGROUND_COLOR."background-image:url(".PATH_TPL."divers/background_noir.jpg); background-repeat:no-repeat;");
	define("STYLE_BLOCK_SELECT", STYLE_BORDER_IE.STYLE_BORDER_RADIUS.STYLE_SHADOW_SELECT."background-color:#777;");
	define("STYLE_ELEMENT_LISTE", "width:99.5%; margin:0px; border-bottom:#555 solid 1px;");
}

////	FOND D'ECRAN EN MEMOIRE : REVOIR LE ROOT_PATH ?
$fond_ecran_affiche = (ROOT_PATH=="./")  ?  str_replace("../","./",$_SESSION["cfg"]["espace"]["fond_ecran"])  :  $_SESSION["cfg"]["espace"]["fond_ecran"];
////	LARGEUR DU CORP DE PAGE FIXE, EN FONCTION DE LA RESOLUTION d'ECRAN
$contenu_principal_centre_width = (@$_SESSION["cfg"]["resolution_width"]<=1250) ? "980px" : "1200px";
?>


/* STYLE DE LA PAGE (PRINCIPALE / POPUP)  +  BLOCKS PRINCIPAUX  */
body						{ <?php echo STYLE_FONT_COLOR.STYLE_BACKGROUND_COLOR.(IS_MAIN_PAGE==true?"background:url(".$fond_ecran_affiche.");background-position:center center;":"background-position:bottom right;"); ?> background-attachment:fixed; background-repeat:no-repeat; margin:0px; font-family:arial,helvetica,sans-serif; font-size:12px; }
.content					{ <?php echo STYLE_BLOCK; ?> padding:10px; vertical-align:top; }
.div_menu_horizontal		{ <?php echo STYLE_BLOCK; ?> float:left; padding:3px; }
.contenu_principal_centre	{ width:<?php echo $contenu_principal_centre_width; ?>; min-width:<?php echo $contenu_principal_centre_width; ?>; max-width:98%; margin-left:auto; margin-right:auto; margin-top:10px; }
#contenu_principal_table	{ width:100%; padding:5px; }


/* CONTENU */
.div_elem_deselect		{ <?php echo STYLE_BLOCK; ?> float:left; margin:7px; margin-top:0px; margin-left:0px; }
.div_elem_select		{ <?php echo STYLE_BLOCK_SELECT; ?> float:left; margin:7px; margin-top:0px; margin-left:0px; }
.div_elem_deselect:hover{ <?php echo STYLE_SHADOW_SELECT; ?> }
.div_elem_contenu		{ height:100%; position:relative; overflow:hidden; background-repeat:no-repeat; }
.div_elem_table			{ height:100%; width:100%; }
.div_elem_td			{ <?php echo STYLE_FONT_BOLD; ?> vertical-align:middle; padding:0px; }
.div_elem_td_right		{ text-align:right;padding-right:10px; }
.div_elem_image			{ width:85px; cursor:pointer; vertical-align:middle; }
.div_elem_infos			{ <?php echo STYLE_FONT_BOLD; ?> text-align:center; vertical-align:middle; }
.div_elem_aucun			{ <?php echo STYLE_BLOCK; ?> padding:2px; width:99%; height:80px; padding-top:50px; text-align:center; font-size:14px; font-weight:bold; opacity:0.8; filter:alpha(opacity=80); }
.div_infos				{ <?php echo STYLE_BORDER_RADIUS; ?> border: dashed 1px #b00; text-align:center; font-style:italic; padding:5px; }


/* LIENS */
.lien, a				{ <?php echo STYLE_FONT_COLOR.STYLE_FONT_BOLD; ?> cursor:pointer; text-decoration:none; }
.lien:hover, a:hover	{ <?php echo STYLE_FONT_COLOR_RETRAIT; ?> }
.lien_select			{ <?php echo STYLE_SELECT_RED.STYLE_FONT_BOLD; ?> cursor:pointer; text-decoration:none; }
.lien_select2			{ <?php echo STYLE_SELECT_YELLOW.STYLE_FONT_BOLD; ?> cursor:pointer; text-decoration:none; }
.lien_loupe				{ cursor:url('<?php echo PATH_TPL; ?>divers/recherche.png'),pointer; }


/* LIBELLES & CHAMPS DES FORMULAIRES  */
input[type=text], input[type=password], input[type=file]	{ border: #aaa 1px solid; font-size:11px; font-weight:normal; color:#000; border-radius:2px; -moz-border-radius:2px; box-shadow: 0 0 3px #ddd inset;  }	/* champs texte ou textarea */
input[type=text]:hover, input[type=password]:hover			{ border: #777 1px solid; }
input[type=text]:focus, input[type=password]:focus			{ border: #777 1px solid; box-shadow: 0 0 2px #888 inset; }
input[type=checkbox], input[type=radio]						{ vertical-align:middle; }
.button														{ <?php echo STYLE_FONT_BOLD; ?> cursor:pointer; width:100px; }
.button_big													{ <?php echo STYLE_FONT_BOLD; ?> cursor:pointer; width:150px; height:28px; }
.button_small												{ <?php echo STYLE_FONT_BOLD; ?> cursor:pointer; width:70px; }
fieldset			{ <?php echo STYLE_BLOCK; ?> width:95%; margin-top:25px; padding:15px; padding-left:5px; padding-right:5px; border: #888 1px solid;  }
legend				{ <?php echo STYLE_BLOCK.STYLE_FONT_BOLD.STYLE_FONT_COLOR_RETRAIT.STYLE_SHADOW_LEGER; ?> position:absolute; z-index:-1; margin-top:-35px; padding:10px; padding-top:3px; padding-bottom:5px; font-style:italic; }
.fieldset_titre		{ text-align:center; font-style:italic; color:#999; margin-top:-5px; margin-bottom:10px; }
select				{ background-color:#fff; font-size:11px; color:#000; }
.input_select		{ text-align:center;<?php echo STYLE_FONT_COLOR.STYLE_BLOCK.STYLE_SHADOW_LEGER; ?> }
form				{ margin:0px; padding:0px; }
.form_libelle		{ <?php echo STYLE_FONT_BOLD; ?> }
.txt_acces_user		{ <?php echo STYLE_FONT_BOLD.STYLE_SELECT_BLUE; ?> cursor:pointer; }
.txt_acces_admin	{ <?php echo STYLE_FONT_BOLD.STYLE_SELECT_ORANGE; ?> cursor:pointer; }
.champ_desactive	{ opacity:0.70; filter:alpha(opacity=70); }


/* MENU DE GAUCHE : FIXE / FLOTTANT (garder les min & max en cas de dépassements de blocks !)  */
#menu_gauche_block_td		{ <?php echo "width:".(LARGEUR_MENU_GAUCHE-30)."px; min-width:".(LARGEUR_MENU_GAUCHE-30)."px; max-width:".(LARGEUR_MENU_GAUCHE-10)."px;"; ?> vertical-align:top; <?php echo STYLE_FONT_BOLD; ?> }
#menu_gauche_block_flottant	{ <?php echo "width:".(LARGEUR_MENU_GAUCHE-40)."px; min-width:".(LARGEUR_MENU_GAUCHE-40)."px; max-width:".(LARGEUR_MENU_GAUCHE-30)."px;"; ?> margin:0px; padding:0px; }
.menu_gauche_block			{ margin-bottom:10px; }
.menu_gauche_ligne			{ display:table-row; }
.menu_gauche_img			{ display:table-cell; padding-bottom:4px; vertical-align:middle; width:30px; }
.menu_gauche_img img		{ max-height : 28px; max-width : 30px; }
.menu_gauche_txt			{ display:table-cell; padding-bottom:4px; vertical-align:middle; }


/* MENUS CONTEXTUELS (clignotte tjs sous IE7/8!) */
.menu_context				{ <?php echo STYLE_BLOCK.STYLE_BORDER_MENU_CONTEXT.STYLE_FONT_BOLD.STYLE_FONT_COLOR; ?> position:absolute; z-index:10000; display:none; max-width:350px; text-align:left; margin:4px; padding:10px; cursor:default; -moz-user-select:none; -webkit-user-select:none; -ms-user-select:none; overflow:auto; max-height:500px; }
.menu_context_ligne			{ display:table; }
.menu_context_img			{ display:table-cell; width:25px; padding:2px; vertical-align:middle; }
.menu_context_img img		{ max-height:20px; max-width:22px; }
.menu_context_txt_left		{ display:table-cell; width:58px; padding:2px; }
.menu_context_txt			{ display:table-cell; padding:2px; }
.menu_context_hr			{ height:1px; max-width:300px; opacity:0.4; filter:alpha(opacity=40); text-align:center; margin-top:5px; margin-bottom:5px; }


/* DIVERSES BALISES */
img					{ border:0px; vertical-align:middle; }
table				{ margin:0px; padding:0px; }
td					{ vertical-align:top; }
acronym				{ border-bottom: 1px dotted #777; cursor:help; }
hr					{ height:1px; opacity:0.4; filter:alpha(opacity=40); }
p					{ margin:0; padding: 0; }


/* DIVERSES CLASS */
.forum_citation		{ <?php echo STYLE_BACKGROUND_COLOR2.STYLE_BORDER_RADIUS; ?> padding:8px; margin:0px; margin-top:10px; margin-bottom:10px; max-height:80px; overflow:auto; opacity:0.75; filter:alpha(opacity=75); }
.forum_citation_ico	{ float:right; opacity:0.7; filter:alpha(opacity=70); }
.mot_search_result	{ border-bottom: 1px dotted #f00; <?php echo STYLE_SELECT_RED; ?> }
.pas_selection		{ -moz-user-select:none; -webkit-user-select:none; -ms-user-select:none; }
.info				{ cursor:help; }
.cacher				{ display:none; }
.infobulle			{ <?php echo STYLE_BORDER_IE.STYLE_BORDER_RADIUS.STYLE_SHADOW; ?> position:absolute; z-index:100000; visibility:hidden; padding:5px; border:1px solid #555; background-image:url('<?php echo PATH_TPL; ?>divers/fond_opaque_infobulle.png'); }
.infobulle_contenu	{ <?php echo STYLE_FONT_BOLD.STYLE_BORDER_RADIUS; ?> background-color:#000000; padding:8px; max-width:500px; color:#ffffff; line-height:18px; }
.div_accueil		{ <?php echo STYLE_BLOCK.STYLE_FONT_BOLD; ?> width:700px; margin:auto; text-align:center; }
.tr_survol:hover	{ background-color:<?php echo STYLE_TR_DESELECT; ?>; }
.calendrier_flottant{ width:220px; height:175px; padding:0px; margin:0px; margin-top:20px; margin-left:-70px; overflow:auto; border:1px solid #555; }
.calendrier_input	{ width:70px; cursor:pointer; }
.icone				{ opacity:0.9; filter:alpha(opacity=90); }
.icone:hover		{ opacity:1; filter:alpha(opacity=100); }
.icone_groupe		{ height:16px; margin:2px; }


/* IMPRESSION  (page-break-after:always; pour les sauts de page (d'agenda)) */
@media print{
	body		{ background-color:#fff; color:#000; }
	.noprint	{ display:none; }
}
</style>