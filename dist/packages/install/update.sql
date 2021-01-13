-- ------------------------------------------------------
--
-- All SQL updates that have been made to the default tables
-- since the last version should be placed in here. All queries
-- are run in the order that they appear in this file.
--
-- Changes to tables      add an ALTER query
-- New tables             add a CREATE query
-- Removal of tables      add a DROP query
-- Additional records     add an INSERT query
-- Removal of records     add a DELETE query
-- Updating of records    add a UPDATE query
--
-- Any changes that are made by queries in this file
-- must also be made to the corresponding install.sql
--
-- To Add the Twist table prefix you must use the following
-- syntax /*TWIST_DATABASE_TABLE_PREFIX*/`table_name`
--
-- You can use a single line comment above each query using
-- the following syntax "-- @comment This is my query comment"
--
-- ------------------------------------------------------

-- @comment Fix the broken icons in the asset package
UPDATE /*TWIST_DATABASE_TABLE_PREFIX*/`asset_types` SET `icon` = `image.png` WHERE `icon` = 'photo.png';

RENAME TABLE /*TWIST_DATABASE_TABLE_PREFIX*/`user_data` TO /*TWIST_DATABASE_TABLE_PREFIX*/`user_data_deprecated`;

CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`user_data_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id` (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`user_data` (
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `user_id` (`user_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for this installation',
  `slug` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `name` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `version` char(16) COLLATE utf8_unicode_ci NOT NULL,
  `folder` char(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `package` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Set to 1 if this package extends the framework directly',
  `resources` text COLLATE utf8_unicode_ci COMMENT 'JSON array of resources that are provided',
  `routes` text COLLATE utf8_unicode_ci COMMENT 'JSON array of routes that are provided',
  `blocks` text COLLATE utf8_unicode_ci COMMENT 'JSON array of blocks that are provided',
  `extensions` text COLLATE utf8_unicode_ci COMMENT 'JSON array of packages that are extended',
  `installed` datetime DEFAULT NULL COMMENT 'Date the package was installed',
  `updated` datetime DEFAULT NULL COMMENT 'Date the package was updated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE /*TWIST_DATABASE_TABLE_PREFIX*/`settings` CHANGE `group` `group` CHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`apikeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` char(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowed_ips` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Leave blank for no IP restrictions',
  `enabled` ENUM('0','1') NOT NULL DEFAULT '1' COMMENT 'Set to 1 to enable this API key',
  `ext_id` char(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Any external info or reference to help identify this key',
  `created` datetime DEFAULT NULL COMMENT 'Date the API key was created',
  KEY `id` (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;