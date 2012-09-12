<?php
if (!function_exists("droit_ecriture_module")) {
    /**
     * @param null $pSpecificMod Nom du module dont le droit en écriture doit être vérifié, si ce paramètre est omis on vérifie pour le module courant
     * @return bool
     */
    function droit_ecriture_module($pSpecificMod = null) {
		$groupes_utilisateur = groupes_users($_SESSION["espace"]["id_espace"], $_SESSION['user']['id_utilisateur']);

		// Récupération de l'id_module
        if ($pSpecificMod != null) {
            $module_infos = db_tableau("SELECT * FROM gt_module WHERE nom='".$pSpecificMod."'");
        } else {
		    $module_infos = db_tableau("SELECT * FROM gt_module WHERE nom='".MODULE_NOM."'");
        }
		if (!is_array($module_infos)) { return false; }

		$module_infos = $module_infos[0];

		$groupes_2	= objet_affectations(array('type_objet' => 'module'), $module_infos['id_module'], "groupes", 2);
		if (is_null($groupes_2)) { return false; }

		// Parcours de $groupes_2 pour vérifier si un des groupes de l'utilisateur à le droit d'écrire
		$ecriture_ok = false;
		for ($i=0; $i < count($groupes_2) && !$ecriture_ok; $i++) {
			$ecriture_ok = $ecriture_ok || in_array($groupes_2[$i]['id_groupe'], array_keys($groupes_utilisateur));
		}
		return $ecriture_ok;
	}
}

if (!function_exists("droit_acces_module")) {
    /**
     * @param null $pSpecificMod Nom du module dont le droit d'accès doit être vérifié, si ce paramètre est omis on vérifie pour le module courant
     * @return bool
     */
    function droit_acces_module($pSpecificMod = null) {
        $groupes_utilisateur = groupes_users($_SESSION["espace"]["id_espace"], $_SESSION['user']['id_utilisateur']);

        // Récupération de l'id_module
        if ($pSpecificMod != null) {
            $module_infos = db_tableau("SELECT * FROM gt_module WHERE nom='".$pSpecificMod."'");
        } else {
            $module_infos = db_tableau("SELECT * FROM gt_module WHERE nom='".MODULE_NOM."'");
        }
        if (!is_array($module_infos)) { return false; }

        $module_infos = $module_infos[0];

        $groupes_1	= objet_affectations(array('type_objet' => 'module'), $module_infos['id_module'], "groupes", 1);
        if (is_null($groupes_1)) { return false; }

        // Parcours de $groupes_1 pour vérifier si un des groupes de l'utilisateur à le droit d'écrire
        $ecriture_ok = false;
        for ($i=0; $i < count($groupes_1) && !$ecriture_ok; $i++) {
            $ecriture_ok = $ecriture_ok || in_array($groupes_1[$i]['id_groupe'], array_keys($groupes_utilisateur));
        }
        return $ecriture_ok;
    }
}

?>