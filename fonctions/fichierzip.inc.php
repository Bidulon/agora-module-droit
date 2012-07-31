<?php
////	CREATION D'ARCHIVE VIA ZIPARCHIVE
////	>>>>  include dans creer_envoyer_archive() car PHP-4 affiche une erreur si pas dans un include, même si c'est pas utilisé !!!

////	Création de l'archive
$zip = new ZipArchive();
$archive_tmp = PATH_TMP.uniqid(mt_rand())."archive.zip";
$zip->open($archive_tmp, ZipArchive::CREATE);

////	Ajout de chaque dossier / fichier à l'archive
foreach($tab_fichiers as $elem){
	if(is_dir($elem["path_source"]))		$zip->addFile($fichier_vide, $elem["path_zip"]."/.void");
	elseif(is_file($elem["path_source"]))	$zip->addFile($elem["path_source"], $elem["path_zip"]);
}
$zip->close();

////	Envoi du zip, puis suppression
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($nom_archive).'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: '.filesize($archive_tmp));
ob_clean();
flush();
@readfile($archive_tmp);
unlink($archive_tmp);
?>
