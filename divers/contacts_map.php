<?php
////	INIT
require_once "../".$_GET["module_dossier"]."/commun.inc.php";
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
	<head>
		<title>Google MAP</title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>  <!-- Carte affiché en plein écran et sans redimensionnement possible -->
		<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>javascript.js"></script>
		<script type="text/javascript" src="<?php echo PATH_COMMUN; ?>jquery/jquery.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>  <!-- API Google MAPS (sensor=false -> pas de positionnement de l'utilisateur) -->
		<script type="text/javascript">
		////	REDIMENTIONNE
		$(window).load(function(){ resize_iframe_popup('85%','85%'); });

		////	USERS/CONTACTS -> ADRESSE & LIBELLE
		var adresses = [];
		<?php
		foreach(text2tab($_GET["elements"]) as $cpt => $elem_tmp)
		{
			$id_contact = str_replace(array("utilisateur-","contact-"),"",$elem_tmp);
			$contact_photo = PATH_TPL."divers/inconnu.png";
			// Infos sur le user/contact  &  controle d'accès
			if(preg_match("/utilisateur/i",$elem_tmp)){
				$personne_tmp = user_infos($id_contact);
				controle_affichage_utilisateur($id_contact);
				if($personne_tmp["photo"]!="")	$contact_photo = PATH_MOD_USER.$personne_tmp["photo"];
			}else{
				$personne_tmp = objet_infos($objet["contact"],$id_contact);
				if(droit_acces($objet["contact"],$personne_tmp)<1)	continue;
				if($personne_tmp["photo"]!="")	$contact_photo = PATH_MOD_CONTACT.$personne_tmp["photo"];
			}
			// Ajoute la personne au tableau javascript
			if($personne_tmp["adresse"]!="" || $personne_tmp["codepostal"]!="" || $personne_tmp["ville"]!="" || $personne_tmp["pays"]!="")
			{
				$adresse_tmp = trim($personne_tmp["adresse"].", ".$personne_tmp["codepostal"]." ".$personne_tmp["ville"]." ".$personne_tmp["pays"],  ", ");
				$libelle_tmp = htmlspecialchars(trim($personne_tmp["nom"]." ".$personne_tmp["prenom"]." - ".$personne_tmp["fonction"]." - ".$personne_tmp["societe_organisme"]." - ".$adresse_tmp,  " - "));
				echo 'adresses.push( {"adresse":"'.$adresse_tmp.'", "libelle":"'.$libelle_tmp.'", "photo":"'.$contact_photo.'"} );';
			}
		}
		?>

		////	INITIALISE LA CARTE GOOGLE MAP
		function initialize()
		{
			// Charge la carte (avec options)  +  Instancie le gécodeur  +  Objet définissant les limites de la carte
			map		 = new google.maps.Map(document.getElementById("map_canvas"), {zoom:8, mapTypeId:google.maps.MapTypeId.ROADMAP});
			geocoder = new google.maps.Geocoder();
			bounds	 = new google.maps.LatLngBounds();
			// Géocode et marque chaque adresse
			for(key in adresses)	{ geocoderMarkerAddresse(key); }
		}

		////	GEOCODER + MARKER UNE ADRESSE
		function geocoderMarkerAddresse(key)
		{
			// Latitude et Longitude en fonction de l'adresse
			geocoder.geocode( {'address':adresses[key]['adresse']}, function(results, status)
			{
				// Géolocalisation OK
				if(status==google.maps.GeocoderStatus.OK)
				{
					// Récupère la latitude et longitude
					adresses[key]['lat'] = results[0].geometry.location.lat();
					adresses[key]['lng'] = results[0].geometry.location.lng();
					// Prépare l'icone du marker (url + position de la photo par rapport au point du marker = centre/bottom + dimension de a photo)
					iconePhoto = new google.maps.MarkerImage(adresses[key]['photo'], null, null, new google.maps.Point(18,0), new google.maps.Size(36,36));
					// Ajoute le marker
					adresses[key]['marker'] = new google.maps.Marker({
						map:map,
						title:adresses[key]['libelle'],
						position:results[0].geometry.location,
						icon:iconePhoto
					});
					// Infobulle du marqueur
					adresses[key]['infobulle_html'] = adresses[key]['libelle']+"<div id='streetView"+key+"' style='width:500px;height:300px;'>Street View loading ...</div>";
					adresses[key]['infobulle'] = new google.maps.InfoWindow( {content:adresses[key]['infobulle_html']} );
					google.maps.event.addListener(adresses[key]['marker'], 'click', function() {
						adresses[key]['infobulle'].open(map, adresses[key]['marker']);
						setTimeout("displayStreetView("+key+");",1000);
					});
					// Etend et repositionne la carte
					bounds.extend(new google.maps.LatLng(adresses[key]['lat'],adresses[key]['lng']));
					map.fitBounds(bounds);
				}
			});
		}

		////	APPEL DE StreetView UNE FOIS L'INFOBULLE CHARGEE
		function displayStreetView(key)
		{
			StreetView = new google.maps.StreetViewPanorama(element("streetView"+key));
			StreetView.setPosition(new google.maps.LatLng(adresses[key]['lat'],adresses[key]['lng']));
		}
		</script>
	</head>


	<body onload="initialize();">
		<div id="map_canvas" style="height:100%;width:100%;"></div>
	</body>
</html>