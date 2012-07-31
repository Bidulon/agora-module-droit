<?php
////	INIT
define("IS_MAIN_PAGE",true);
require "commun.inc.php";
require PATH_INC."header_menu.inc.php";


////	LISTE DEROULANTE POUR LES FILTRES
////
function getSelect($libelle_champ, $nom_champ, $selected="")
{
	global $trad;
	$filtre_options = db_colonne("SELECT DISTINCT ".$nom_champ." FROM gt_logs L LEFT JOIN gt_espace S ON S.id_espace=L.id_espace  WHERE ".$nom_champ." is not null AND ".$nom_champ."!='' ORDER BY ".$nom_champ." asc");
	echo "<select name=\"search_".$libelle_champ."\" class=\"search_init\">";
	echo "<option value=\"\" ".($selected==''?'selected':'').">".$trad["LOGS_filtre"]." ".$libelle_champ."</option>";
	foreach($filtre_options as $value)
	{
		if(isset($trad["LOGS_".$value]))						$lib_value = $trad["LOGS_".$value];
		elseif(isset($trad[strtoupper($value)."_nom_module"]))	$lib_value = $trad[strtoupper($value)."_nom_module"];
		else													$lib_value = $value;
		echo "<option value=\"".$value."\" ".($selected==$value?'selected':'').">".$lib_value."</option>";
	}
	echo "</select>";
}
?>


<link href="datatables/page.css" rel="stylesheet" type="text/css" />
<link href="datatables/table.css" rel="stylesheet" type="text/css" />
<link href="datatables/theme.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript">
////	PARAMETRAGE DE DataTables
////
var asInitVals = new Array();
jQuery(document).ready(function() {
    oTable = jQuery('#logs').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "logs_json.php",
        "iDisplayLength": 20,
        "aaSorting": [[0,"desc"]],
        "aLengthMenu": [20,100,300],
		"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
			var settings = this.fnSettings();
			var str = settings.oPreviousSearch.sSearch;
			$('td',nRow).each(function(i){
				this.innerHTML = aData[i].replace(new RegExp(str,'i'), function(matched){return "<span class='highlight'>"+matched+"</span>";});
			});
			return nRow;
		},
        "oLanguage": {
            "sLengthMenu": "_MENU_ logs",
            "sZeroRecords": "<?php echo $trad["LOGS_no_logs"]; ?>",
            "sInfo": "_START_-_END_ [_TOTAL_]",
            "sInfoEmpty": "<?php echo $trad["LOGS_no_logs"]; ?>",
            "sInfoFiltered": "(<?php echo $trad["LOGS_filtre_a_partir"]; ?> _MAX_ logs)",
            "sSearch":"<?php echo $trad["LOGS_chercher"]; ?> ",
            "oPaginate":{sFirst:"<<",sPrevious:"<",sNext:">",sLast:">>"}
        }
    });

	$("tfoot input").keyup( function() {
		oTable.fnFilter(this.value, this.parentNode.cellIndex);
	});
	$('tfoot select').change( function() {
		oTable.fnFilter($(this).val(), this.parentNode.cellIndex);
	});
	$("tfoot input").each( function(i) {
		asInitVals[i] = this.value;
	});
	$("tfoot input, tfoot select").focus( function () {
		if(this.className=="search_init"){
			this.className = "";
		}
	});
	$("tfoot input, tfoot select").blur( function (i) {
		if(this.value=="" || $(this).val()==""){
			this.className = "search_init";
			this.value = asInitVals[$("tfoot input").index(this)];
		}
	});
});
</script>


<div class="contenu_principal_centre" style="<?php echo STYLE_SHADOW_SELECT; ?>">
	<table cellpadding="0" cellspacing="0" class="display" id="logs">
		<thead>
			<tr>
				<th width="110"><?php echo $trad["LOGS_date_heure"]; ?></th>
				<th width="150"><?php echo $trad["LOGS_espace"]; ?></th>
				<th width="150"><?php echo $trad["LOGS_module"]; ?></th>
				<th width="80"><?php echo $trad["LOGS_adresse_ip"]; ?></th>
				<th width="120"><?php echo $trad["LOGS_utilisateur"]; ?></th>
				<th width="120"><?php echo $trad["LOGS_action"]; ?></th>
				<th><?php echo $trad["LOGS_commentaire"]; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="7" class="dataTables_empty"><?php echo $trad["LOGS_chargement"]; ?>...</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th><input type="text" name="search_date" value="<?php echo $trad["LOGS_filtre"]." ".$trad["LOGS_date_heure"]; ?>" class="search_init" style="width:100px;" /></th>
				<th><?php getSelect($trad["LOGS_espace"],"S.nom"); ?></th>
				<th><?php getSelect($trad["LOGS_module"],"module"); ?></th>
				<th><input type="text" name="search_IP" value="<?php echo $trad["LOGS_filtre"]." ".$trad["LOGS_adresse_ip"]; ?>" class="search_init" /></th>
				<th><input type="text" name="search_user" value="<?php echo $trad["LOGS_filtre"]." ".$trad["LOGS_utilisateur"]; ?>" class="search_init" style="width:100px;" /></th>
				<th><?php getSelect($trad["LOGS_action"],"action"); ?></th>
				<th><input type="text" name="search_commentaire" value="<?php echo $trad["LOGS_filtre"]." ".$trad["LOGS_commentaire"]; ?>" class="search_init" /></th>
			</tr>
		</tfoot>
	</table>
</div>

<div onClick="redir('logs_csv.php');" class="lien" style="z-index:-10;margin-top:2px;text-align:center;">
	<span style="padding:6px;<?php echo STYLE_BLOCK; ?>">
		<img src="<?php echo PATH_TPL; ?>divers/telecharger.png" style="height:18px;" /> <?php echo $trad["telecharger"]; ?>
	</span>
</div>


<?php require PATH_INC."footer.inc.php"; ?>