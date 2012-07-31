<?php
////	FONCTION D'EXPORTATION DE CONTACTS
////
function export_contact($liste_contact)
{
	////	EXPORT CSV
	////
	if(preg_match("/csv/i",$_POST["export_format"])) {
		////	INIT
		$contenu_export = "";
		global $trad, $tab_csv_principal;
		$tab_csv = $tab_csv_principal[$_POST["export_format"]];
		$nom_fichier = $tab_csv["nom_fichier"];
		////	CSV PERSO
		if($_POST["export_format"]=="csv_agora") {
			$tab_csv["delimiteur"] = stripslashes($_POST["delimiteur"]);
			$tab_csv["separateur"] = stripslashes($_POST["separateur"]);
		}
		////	ENTETE DU FICHIER CSV
		if($_POST["export_format"]=="csv_agora") {
			foreach($tab_csv["champs"] as $champ_agora => $champ_csv)	{ $contenu_export .= $tab_csv["delimiteur"].$trad[$champ_agora].$tab_csv["delimiteur"].$tab_csv["separateur"]; }
		}
		else {
			foreach($tab_csv["champs"] as $champ_csv)					{ $contenu_export .= $tab_csv["delimiteur"].$champ_csv.$tab_csv["delimiteur"].$tab_csv["separateur"]; }
		}
		$contenu_export .= "\n";
		////	AJOUT DE CHAQUE CONTACT
		foreach($liste_contact as $contact)
		{
			foreach($tab_csv["champs"] as $champ_agora => $champ_csv)
			{
				// On exporte les champs de chaque contacts
				if($tab_csv["delimiteur"]=="'")		$contact[$champ_agora] = addslashes($contact[$champ_agora]);
				if(isset($contact[$champ_agora]) && $contact[$champ_agora]!="")	{ $contenu_export .= $tab_csv["delimiteur"].$contact[$champ_agora].$tab_csv["delimiteur"].$tab_csv["separateur"]; }
				else															{ $contenu_export .= $tab_csv["separateur"]; }
			}
			$contenu_export .= "\n";
		}
	}

	////	EXPORT LDIF
	////
	elseif($_POST["export_format"]=="ldif") {
		////	INIT
		$nom_fichier = "contact.ldif";
		$contenu_export = "";
		////	AJOUT DE CHAQUE CONTACT
		foreach($liste_contact as $contact)
		{
			$contenu_export .= "dn: cn=".$contact["prenom"]." ".$contact["nom"].", mail=".$contact["mail"]."\n";
			$contenu_export .= "objectclass: top\n";
			$contenu_export .= "objectclass: person\n";
			$contenu_export .= "objectclass: organizationalPerson\n";
			$contenu_export .= "cn: ".$contact["prenom"]." ".$contact["nom"]."\n";
			$contenu_export .= "givenName: ".$contact["prenom"]."\n";
			$contenu_export .= "sn: ".$contact["nom"]."\n";
			if($contact["mail"]!="")				$contenu_export .= "mail: ".$contact["mail"]."\n";
			if($contact["telephone"]!="")			$contenu_export .= "homePhone: ".$contact["telephone"]."\n";
			if($contact["telephone"]!="")			$contenu_export .= "telephonenumber: ".$contact["telephone"]."\n";
			if($contact["fax"]!="")					$contenu_export .= "fax: ".$contact["fax"]."\n";
			if($contact["telmobile"]!="")			$contenu_export .= "mobile: ".$contact["telmobile"]."\n";
			if($contact["adresse"]!="")				$contenu_export .= "homeStreet: ".$contact["adresse"]."\n";
			if($contact["ville"]!="")				$contenu_export .= "mozillaHomeLocalityName: ".$contact["ville"]."\n";
			if($contact["codepostal"]!="")			$contenu_export .= "mozillaHomePostalCode: ".$contact["codepostal"]."\n";
			if($contact["pays"]!="")				$contenu_export .= "mozillaHomeCountryName: ".$contact["pays"]."\n";
			if($contact["societe_organisme"]!="")	$contenu_export .= "company: ".$contact["societe_organisme"]."\n";
			if($contact["fonction"]!="")			$contenu_export .= "title: ".$contact["fonction"]."\n";
			if($contact["commentaire"]!="")			$contenu_export .= "description: ".$contact["commentaire"]."\n";
			$contenu_export .= "\n";
		}
	}

	/////   LANCEMENT DU TELECHARGEMENT
	////
	telecharger($nom_fichier, false, $contenu_export);
}


////	DETERMINE LE TYPE DE CSV (EN FONCTION DE L'ENTETE)
////
function type_csv($csv_import_entete)
{
	global $tab_csv_principal;
	$type_csv_sortie = "";
	$nb_correspondances = 0;
	// On compare avec chaque type de csv
	foreach($tab_csv_principal as $type_csv => $infos_csv)
	{
		$tab_cle_agora_position_csv = array();
		$nb_correspondances_csv = 0;
		// Dans le csv courant on regarde le nombre de champs identiques à l'entête du csv reçu
		foreach($csv_import_entete as $champ_entete_cpt => $champ_entete_tmp)
		{
			$champ_entete_tmp = trim(strtolower(convert_utf8($champ_entete_tmp)),$infos_csv["delimiteur"]);
			$cle_agora_champ = array_search($champ_entete_tmp, $infos_csv["champs"]);
			if($cle_agora_champ!=false)		{  $tab_cle_agora_position_csv[$cle_agora_champ] = $champ_entete_cpt;  $nb_correspondances_csv ++;  }
		}
		// S'il y a plus de correspondances avec le type de csv précédent, c'est actuellement le type de csv qui correspond
		if($nb_correspondances_csv > $nb_correspondances && $nb_correspondances_csv>1) {
			$type_csv_sortie["type"] = $type_csv;
			$type_csv_sortie["tab_cle_agora_position_csv"] = $tab_cle_agora_position_csv; // "cle_agora"=>"cle_entete_csv" / "code_postale"=>"7"
			$nb_correspondances = $nb_correspondances_csv;
		}
	}

	// on retourne le csv correspondant
	return $type_csv_sortie;
}


////	TABLEAUX DE VALEURS DES CHAMPS  AGORA => CSV
////
$tab_csv_principal = array(
	////	CSV PERSONNALISE
	"csv_agora" => array(
		"nom_fichier" => "csv_agora.csv",
		"separateur" => ";",
		"delimiteur" => '"',
		"champs" => array(
			"civilite" => "civilité",
			"nom" => "nom",
			"prenom" => "prénom",
			"societe_organisme" => "organisme / Société",
			"fonction" => "fonction",
			"adresse" => "adresse",
			"codepostal" => "code postal",
			"ville" => "ville",
			"pays" => "pays",
			"telephone" => "téléphone",
			"telmobile" => "téléphone mobile",
			"fax" => "fax",
			"mail" => "email",
			"siteweb" => "site Web",
			"competences" => "competences",
			"hobbies" => "hobbies",
			"commentaire" => "commentaire"
		)
	),

	////	CSV THUNDERBIRD
	"csv_thunderbird" => array(
		"nom_fichier" => "csv_thunderbird.csv",
		"separateur" => ",",
		"delimiteur" => "",
		"champs" => array(
			"prenom" => "Prénom",
			"nom" => "Nom de famille",
			"Nom à afficher" => "Nom à afficher",
			"Surnom" => "Surnom",
			"mail" => "Première adresse électronique",
			"Deuxième adresse électronique" => "Deuxième adresse électronique",
			"Nom à l'écran" => "Nom à l'écran",
			"Tél. professionnel" => "Tél. professionnel",
			"telephone" => "Tél. personnel",
			"fax" => "Fax",
			"Pager" => "Pager",
			"telmobile" => "Portable",
			"adresse" => "Adresse privée",
			"Adresse privée 2" => "Adresse privée 2",
			"ville" => "Ville",
			"pays" => "Pays/État",
			"codepostal" => "Code postal",
			"Région" => "Région",
			"Adresse professionnelle" => "Adresse professionnelle",
			"Adresse professionnelle 2" => "Adresse professionnelle 2",
			"Ville" => "Ville",
			"Pays/État" => "Pays/État",
			"Code postal" => "Code postal",
			"Région" => "Région",
			"fonction" => "Profession",
			"Service" => "Service",
			"societe_organisme" => "Société",
			"siteweb" => "Page Web 1",
			"Page Web 2" => "Page Web 2",
			"année de naissance" => "année de naissance",
			"A" => "A",
			"Mois" => "Mois",
			"Jour" => "Jour",
			"Divers 1" => "Divers 1",
			"Divers 2" => "Divers 2",
			"Divers 3" => "Divers 3",
			"Divers 4" => "Divers 4",
			"commentaire" => "Notes"
		)
	),

	////	CSV YAHOO
	"csv_yahoo" => array(
		"nom_fichier" => "csv_yahoo.csv",
		"separateur" => ",",
		"delimiteur" => '"',
		"champs" => array(
			"prenom" => "Premier",
			"Deuxième prénom" => "Deuxième prénom",
			"nom" => "Dernier",
			"Surnom" => "Surnom",
			"mail" => "Mail",
			"Catégorie" => "Catégorie",
			"Listes de diffusion" => "Listes de diffusion",
			"Compte Messenger" => "Compte Messenger",
			"Domicile" => "Domicile",
			"Professionnelle" => "Professionnelle",
			"Pager" => "Pager",
			"fax" => "Fax",
			"telmobile" => "Tél. mobile",
			"Autre" => "Autre",
			"Téléphone Yahoo!" => "Téléphone Yahoo!",
			"Principal" => "Principal",
			"Autre mail 1" => "Autre mail 1",
			"Autre mail 2" => "Autre mail 2",
			"siteweb" => "Site Web",
			"Site web" => "Site web",
			"fonction" => "Fonction",
			"societe_organisme" => "Société",
			"Bureau" => "Bureau",
			"Ville" => "Ville",
			"Département (bureau)" => "Département (bureau)",
			"Code postal (bureau)" => "Code postal (bureau)",
			"Pays" => "Pays",
			"adresse" => "Domicile",
			"ville" => "Ville (domicile)",
			"Département (domicile)" => "Département (domicile)",
			"codepostal" => "Code postal (domicile)",
			"pays" => "Pays (domicile)",
			"Anniversaire" => "Anniversaire",
			"Anniversaire/Fête" => "Anniversaire/Fête",
			"Personnalisation 1" => "Personnalisation 1",
			"Personnalisation 2" => "Personnalisation 2",
			"Personnalisation 3" => "Personnalisation 3",
			"Personnalisation 4" => "Personnalisation 4",
			"commentaire" => "Commentaires",
			"Nom_1 pour Messenger" => "Nom_1 pour Messenger",
			"Nom_2 pour Messenger" => "Nom_2 pour Messenger",
			"Nom_3 pour Messenger" => "Nom_3 pour Messenger",
			"Nom_4 pour Messenger" => "Nom_4 pour Messenger",
			"Nom_5 pour Messenger" => "Nom_5 pour Messenger",
			"Nom_6 pour Messenger" => "Nom_6 pour Messenger",
			"Nom_7 pour Messenger" => "Nom_7 pour Messenger",
			"Nom_8 pour Messenger" => "Nom_8 pour Messenger",
			"Nom_9 pour Messenger" => "Nom_9 pour Messenger",
			"Nom pour Skype" => "Nom pour Skype",
			"Nom pour IRC" => "Nom pour IRC",
			"Nom pour ICQ" => "Nom pour ICQ",
			"Nom pour Google" => "Nom pour Google",
			"Nom pour MSN" => "Nom pour MSN",
			"Nom pour AIM" => "Nom pour AIM",
			"Nom pour QQ" => "Nom pour QQ"
		)
	),

	////	CSV OUTLOOK
	"csv_outlook" => array(
		"nom_fichier" => "csv_outlook.csv",
		"separateur" => ",",
		"delimiteur" => '"',
		"champs" => array(
			"Fonction" => "Fonction",
			"prenom" => "Prénom",
			"Deuxième prénom" => "Deuxième prénom",
			"nom" => "Nom",
			"Suffixe" => "Suffixe",
			"societe_organisme" => "Société",
			"Service" => "Service",
			"fonction" => "Fonction",
			"Rue (bureau)"=> "Rue (bureau)",
			"Rue (bureau) 2" => "Rue (bureau) 2",
			"Rue (bureau) 3" => "Rue (bureau) 3",
			"Ville (bureau)" => "Ville (bureau)",
			"Département (bureau)" => "Département (bureau)",
			"Code postal (bureau)" => "Code postal (bureau)",
			"Pays (bureau)" => "Pays (bureau)",
			"adresse" => "Rue (domicile)",
			"Rue (domicile) 3" => "Rue (domicile) 3",
			"Rue (domicile) 3" => "Rue (domicile) 3",
			"ville" => "Ville (domicile)",
			"Département" => "Département",
			"adresse" => "Code postal (domicile)",
			"pays" => "Pays (domicile)",
			"Rue (autre)" => "Rue (autre)",
			"Rue (autre) 2" => "Rue (autre) 2",
			"Rue (autre) 3" => "Rue (autre) 3",
			"Ville (autre)" => "Ville (autre)",
			"Région (autre)" => "Région (autre)",
			"Code postal (autre)" => "Code postal (autre)",
			"Pays (autre)" => "Pays (autre)",
			"Téléphone de l'assistant(e)" => "Téléphone de l'assistant(e)",
			"Fax (bureau)" => "Fax (bureau)",
			"Téléphone (bureau)" => "Téléphone (bureau)",
			"Téléphone 2 (bureau)" => "Téléphone 2 (bureau)",
			"Rappel" => "Rappel",
			"Téléphone (voiture)" => "Téléphone (voiture)",
			"Téléphone société" => "Téléphone société",
			"Fax (domicile)" => "Fax (domicile)",
			"telephone" => "Téléphone (domicile)",
			"Téléphone 2 (domicile)" => "Téléphone 2 (domicile)",
			"RNIS" => "RNIS",
			"telmobile" => "Tél. mobile",
			"fax" => "Fax (autre)",
			"Téléphone (autre)" => "Téléphone (autre)",
			"Pager" => "Pager",
			"Tél. principal" => "Tél. principal",
			"Radio téléphone" => "Radio téléphone",
			"Téléphone TDD/TTY" => "Téléphone TDD/TTY",
			"Télex" => "Télex",
			"Compte" => "Compte",
			"Anniversaire/Fête" => "Anniversaire/Fête",
			"Nom de l'assistant(e)" => "Nom de l'assistant(e)",
			"Infos de facturation" => "Infos de facturation",
			"Anniversaire" => "Anniversaire",
			"Catégories" => "Catégories",
			"Enfants" => "Enfants",
			"mail" => "Adresse mail",
			"Nom complet de l'adresse mail" => "Nom complet de l'adresse mail",
			"Adresse mail 2" => "Adresse mail 2",
			"Nom complet de l'adresse mail 2" => "Nom complet de l'adresse mail 2",
			"Adresse mail 3" => "Adresse mail 3",
			"Nom complet de l'adresse mail 3" => "Nom complet de l'adresse mail 3",
			"Sexe" => "Sexe",
			"Code gouvernement" => "Code gouvernement",
			"Passe-temps" => "Passe-temps",
			"Initiales" => "Initiales",
			"Mots clés" => "Mots clés",
			"Langue" => "Langue",
			"Lieu" => "Lieu",
			"Kilométrage" => "Kilométrage",
			"commentaire" => "Notes",
			"Bureau" => "Bureau",
			"Numéro d'identification de l'organisation" => "Numéro d'identification de l'organisation",
			"B.P." => "B.P.",
			"Privé" => "Privé",
			"Profession" => "Profession",
			"Recommandé par" => "Recommandé par",
			"Conjoint(e)" => "Conjoint(e)",
			"Utilisateur 1" => "Utilisateur 1",
			"Utilisateur 2" => "Utilisateur 2",
			"Utilisateur 3" => "Utilisateur 3",
			"Utilisateur 4" => "Utilisateur 4",
			"siteweb" => "Page Web"
		)
	),

	////	CSV WINDOWS MAIL
	"csv_windowsmail" => array(
		"nom_fichier" => "csv_windowsmail.csv",
		"separateur" => ";",
		"delimiteur" => "",
		"champs" => array(
			"prenom" => "Prénom",
			"nom" => "Nom",
			"Deuxième prénom" => "Deuxième prénom",
			"Nom complet" => "Nom complet",
			"Surnom" => "Surnom",
			"mail" => "Adresse de messagerie",
			"adresse" => "Rue (domicile)",
			"ville" => "Ville (domicile)",
			"codepostal" => "Code postal (domicile)",
			"Département (domicile)" => "Département (domicile)",
			"pays" => "Pays/région (domicile)",
			"telephone" => "Téléphone personnel",
			"fax" => "Télécopie personnelle",
			"telmobile" => "Téléphone mobile",
			"siteweb" => "Page Web (domicile)",
			"Rue (bureau)" => "Rue (bureau)",
			"Ville (bureau)" => "Ville (bureau)",
			"Code postal (bureau)" => "Code postal (bureau)",
			"Département (bureau)" => "Département (bureau)",
			"Pays/région (bureau)" => "Pays/région (bureau)",
			"Page Web (bureau)" => "Page Web (bureau)",
			"Téléphone professionnel" => "Téléphone professionnel",
			"Télécopie professionnelle" => "Télécopie professionnelle",
			"Radiomessagerie" => "Radiomessagerie",
			"societe_organisme" => "Société",
			"fonction" => "Fonction",
			"Service" => "Service",
			"Adresse professionnelle" => "Adresse professionnelle",
			"commentaire" => "Remarques"
		)
	),

	////	CSV HOTMAIL
	"csv_hotmail" => array(
		"nom_fichier" => "csv_hotmail.csv",
		"separateur" => ";",
		"delimiteur" => '"',
		"champs" => array(
			"civilite" => "Title",
			"prenom" => "First Name",
			"Middle Name" => "Middle Name",
			"nom" => "Last Name",
			"Suffix" => "Suffix",
			"societe_organisme" => "Company",
			"Department" => "Department",
			"fonction" => "Job Title",
			"Business Street" => "Business Street",
			"Business City" => "Business City",
			"Business State" => "Business State",
			"Business Postal Code" => "Business Postal Code",
			"Business Country" => "Business Country",
			"adresse" => "Home Street",
			"ville" => "Home City",
			"Home State" => "Home State",
			"codepostal" => "Home Postal Code",
			"pays" => "Home Country",
			"Business Fax" => "Business Fax",
			"Business Phone" => "Business Phone",
			"Business Phone 2" => "Business Phone 2",
			"Callback" => "Callback",
			"Car Phone" => "Car Phone",
			"Company Main Phone" => "Company Main Phone",
			"fax" => "Home Fax",
			"telephone" => "Home Phone",
			"Home Phone 2" => "Home Phone 2",
			"ISDN" => "ISDN",
			"telmobile" => "Mobile Phone",
			"Other Fax" => "Other Fax",
			"Other Phone" => "Other Phone",
			"Pager" => "Pager",
			"Primary Phone" => "Primary Phone",
			"Radio Phone" => "Radio Phone",
			"TTY/TDD Phone" => "TTY/TDD Phone",
			"Telex" => "Telex",
			"Account" => "Account",
			"Anniversary" => "Anniversary",
			"Assistant's Name" => "Assistant's Name",
			"Billing Information" => "Billing Information",
			"Birthday" => "Birthday",
			"Business Address PO Box" => "Business Address PO Box",
			"Categories" => "Categories",
			"Children" => "Children",
			"Company Yomi" => "Company Yomi",
			"Directory Server" => "Directory Server",
			"mail" => "E-mail Address",
			"E-mail Type" => "E-mail Type",
			"E-mail Display Name" => "E-mail Display Name",
			"E-mail 2 Address" => "E-mail 2 Address",
			"E-mail 2 Type" => "E-mail 2 Type",
			"E-mail 2 Display Name" => "E-mail 2 Display Name",
			"E-mail 3 Address" => "E-mail 3 Address",
			"E-mail 3 Type" => "E-mail 3 Type",
			"E-mail 3 Display Name" => "E-mail 3 Display Name",
			"Gender" => "Gender",
			"Given Yomi" => "Given Yomi",
			"Government ID Number" => "Government ID Number",
			"Hobby" => "Hobby",
			"Home Address PO Box" => "Home Address PO Box",
			"Initials" => "Initials",
			"Internet Free Busy" => "Internet Free Busy",
			"Keywords" => "Keywords",
			"Language" => "Language",
			"Location" => "Location",
			"Manager's Name" => "Manager's Name",
			"Mileage" => "Mileage",
			"commentaire" => "Notes",
			"Office Location" => "Office Location",
			"Organizational ID Number" => "Organizational ID Number",
			"Other Address PO Box" => "Other Address PO Box",
			"Priority" => "Priority",
			"Private" => "Private",
			"Profession" => "Profession",
			"Referred By" => "Referred By",
			"Sensitivity" => "Sensitivity",
			"Spouse" => "Spouse",
			"Surname Yomi" => "Surname Yomi",
			"User 1" => "User 1",
			"User 2" => "User 2",
			"siteweb" => "Web Page"
		)
	)
);
?>