INSERT INTO `gt_module` (`nom`, `module_dossier_fichier`) VALUES ('droits_modules', 'module_droits_modules');

CREATE TABLE `gt_jointure_droits_module_utilisateur`  (
  `id_espace` INT NOT NULL ,
  `nom_module` TINYTEXT ,
  `id_utilisateur` INT NOT NULL,
  `droit` INT NOT NULL
);

CREATE TABLE `gt_jointure_droits_module_groupe`  (
  `id_espace` INT NOT NULL ,
  `nom_module` TINYTEXT ,
  `id_groupe` INT NOT NULL,
  `droit` INT NOT NULL
);
