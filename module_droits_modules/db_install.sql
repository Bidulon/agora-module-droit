INSERT INTO `gt_module` (`nom`, `module_dossier_fichier`) VALUES ('droits_modules', 'module_droits_modules');

ALTER TABLE `gt_module` ADD COLUMN `id_module` INT NOT NULL FIRST;