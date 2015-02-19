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
-- syntax /*TABLE_PREFIX*/`table_name`
--
-- You can use a single line comment above each query using
-- the following syntax "-- @comment This is my query comment"
--
-- ------------------------------------------------------

-- @comment Fix the broken icons in the asset package
UPDATE /*TABLE_PREFIX*/`asset_types` SET `icon` = `image.png` WHERE `icon` = 'photo.png';

RENAME TABLE /*TABLE_PREFIX*/`user_data` TO /*TABLE_PREFIX*/`user_data_deprecated`;

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_data_fields` (
  `id` int(11) DEFAULT NULL,
  `slug` char(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_data` (
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `user_id` (`user_id`,`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
