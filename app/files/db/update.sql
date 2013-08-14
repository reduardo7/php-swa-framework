--
-- Execute Queries for update.
-- Version: 1.0
--
-- Eduardo Cuomo | eduardo.cuomo.ar@gmail.com
--
-- URL: https://github.com/reduardo7/db-version-updater-mysql
--

-- Select DB
-- USE eduardoc_mobile;

-- Create table
CREATE TABLE IF NOT EXISTS `database_version` (
  `version` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `executed_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- Procedure
DELIMITER ;;
DROP PROCEDURE IF EXISTS database_version_control ;;

CREATE PROCEDURE database_version_control(IN version INTEGER, IN description VARCHAR(255), IN query TEXT)
BEGIN

  DECLARE vc INT;

  SET @iVersion=version;
  SET @iDescription=description;
  SET @iQuery=query;

  CREATE TEMPORARY TABLE IF NOT EXISTS mem_version_control (v INTEGER) ENGINE=MEMORY;
  DELETE FROM mem_version_control;

  SET @cQuery = CONCAT('INSERT INTO mem_version_control SELECT version FROM database_version WHERE version = ', @iVersion);
  PREPARE QUERY FROM @cQuery;
  EXECUTE QUERY;
  DEALLOCATE PREPARE QUERY;

  SELECT COUNT(1) INTO vc FROM mem_version_control;
  IF vc = 0 THEN
    PREPARE QUERY FROM @iQuery;
    EXECUTE QUERY;
    DEALLOCATE PREPARE QUERY;

    SET @uQuery = CONCAT('INSERT INTO database_version (version, description) VALUE (', @iVersion, ', ''', @iDescription, ''')');
    PREPARE QUERY FROM @uQuery;
    EXECUTE QUERY;
    DEALLOCATE PREPARE QUERY;
  END IF;

END ;;


DELIMITER ;


-- Update DB
-- NOTE: A unique query per call. There is not designed for multi-query per call.


CALL database_version_control(
  1,
  'Quienes Somos agrega ID',
  'ALTER TABLE `quienes_somos` ADD `id` TINYINT(1) NOT NULL FIRST, ADD PRIMARY KEY (`id`)'
);

CALL database_version_control(
  2,
  'Quienes Somos ID 1',
  'UPDATE `quienes_somos` SET `id` = 1 WHERE `quienes_somos`.`id` = 0'
);

CALL database_version_control(
  3,
  'Limite Desktops y Avisos',
  'ALTER TABLE `comercios` ADD `cantidad_desktops` INT NOT NULL DEFAULT 0 AFTER `url_sitio`, ADD `cantidad_avisos` INT NOT NULL DEFAULT 0 AFTER `cantidad_desktops`'
);

CALL database_version_control(
  4,
  'Limite Desktops y Avisos = 5',
  'UPDATE `comercios` SET `cantidad_desktops` = 5, `cantidad_avisos` = 5'
);

CALL database_version_control(
  5,
  'ID Avisos Forma Pago',
  'ALTER TABLE `avisos_formas_pago` ADD `id` BIGINT(20) NULL DEFAULT NULL FIRST'
);

CALL database_version_control(
  6,
  'Comercio Destacado',
  'ALTER TABLE `comercios` ADD `destacado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cantidad_avisos`, ADD INDEX (`destacado`)'
);

CALL database_version_control(
  7,
  'Aviso Destacado',
  'ALTER TABLE `avisos` ADD `destacado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `clicks`, ADD `fecha` DATE NULL DEFAULT NULL AFTER `destacado`, ADD `hora` VARCHAR(5) NULL DEFAULT NULL AFTER `fecha`, ADD `duracion` TINYINT(1) NOT NULL DEFAULT 0 AFTER `hora`'
);

CALL database_version_control(
  8,
  'Config Table',
  'CREATE TABLE IF NOT EXISTS `config` (`key` varchar(64) NOT NULL, `value` varchar(255) NOT NULL, PRIMARY KEY (`key`))'
);

CALL database_version_control(
  9,
  'Config Table',
  'ALTER TABLE `config` CHANGE `value` `value` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL'
);

CALL database_version_control(
  10,
  'Config Table',
  'ALTER TABLE `config` CHANGE `key` `key` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL'
);

CALL database_version_control(
  11,
  'Aviso Hora to DECIMAL',
  'ALTER TABLE  `avisos` CHANGE `hora` `hora` DECIMAL(4, 2) UNSIGNED ZEROFILL NOT NULL DEFAULT \'0.00\''
);

CALL database_version_control(
  12,
  'Aviso Destacado NULLs',
  'ALTER TABLE `avisos` CHANGE `hora` `hora` DECIMAL(4, 2) UNSIGNED ZEROFILL NULL DEFAULT NULL'
);

CALL database_version_control(
  13,
  'Avisos no Destacados NULLs',
  'UPDATE `avisos` SET `fecha` = NULL, `hora` = NULL WHERE `destacado` = 0'
);

CALL database_version_control(
  14,
  'Ultimo Aviso Destacado',
  'ALTER TABLE  `comercios` ADD `ultimo_destacado` DATETIME NULL DEFAULT NULL AFTER `destacado`'
);

CALL database_version_control(
  15,
  'Contador Reenvios Redes Sociales',
  'CREATE TABLE IF NOT EXISTS `redes_sociales_redirect_count` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `id_red_social` bigint(20) NOT NULL,
    `id_comercio` bigint(20) NOT NULL,
    `ip` varchar(14) NOT NULL,
    `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `id_red_social` (`id_red_social`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;'
);

CALL database_version_control(
  16,
  'Ultimo Aviso Destacado',
  'ALTER TABLE `redes_sociales_redirect_count`
    ADD CONSTRAINT `redes_sociales_redirect_count_ibfk_2` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id`),
    ADD CONSTRAINT `redes_sociales_redirect_count_ibfk_1` FOREIGN KEY (`id_red_social`) REFERENCES `redes_sociales` (`id`);'
);

CALL database_version_control(
  17,
  'Registro de Avisos en Busquedas',
  'CREATE TABLE IF NOT EXISTS `avisos_busquedas` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `id_aviso` bigint(20) NOT NULL,
    `ip` varchar(14) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `id_aviso` (`id_aviso`)
  ) ENGINE=InnoDB'
);

CALL database_version_control(
  18,
  'Registro de Avisos en Busquedas - FK',
  'ALTER TABLE `avisos_busquedas` ADD CONSTRAINT `avisos_busquedas_ibfk_1` FOREIGN KEY (`id_aviso`) REFERENCES `avisos` (`id`)'
)