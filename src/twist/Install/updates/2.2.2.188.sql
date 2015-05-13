
-- @comment Add new deprecated field to the settings table
ALTER TABLE /*TABLE_PREFIX*/`settings`
ADD `deprecated` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Set to ''1'' if the setting is no longer used by the framework'
AFTER `null`;
