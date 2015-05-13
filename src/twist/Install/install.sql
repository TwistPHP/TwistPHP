-- --------------------------------------------------------
--
-- All SQL queries that are required to setup this package
-- as a fresh installation
--
-- New tables       add a CREATE query
-- New records      add an INSERT query
--
-- To Add the Twist table prefix you must use the following syntax /*TABLE_PREFIX*/`table_name`
--
-- ------------------------------------------------------

ALTER DATABASE /*DATABASE_NAME*/ CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`settings` (
  `package` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `group` enum('core','package') COLLATE utf8_unicode_ci NOT NULL,
  `key` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `title` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `default` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `options` text COLLATE utf8_unicode_ci NOT NULL,
  `null` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `deprecated` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Set to ''1'' if the setting is no longer used by the framework',
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installed_packages`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`packages` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `email` char(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Unique email of user',
  `firstname` char(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Users first name',
  `surname` char(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The last (given) name of the user',
  `password` char(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Hash of the users password using SHA512 algorithm',
  `temp_password` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Set to 1 if the users password is temp',
  `verified` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Set to 1 if email address is verified',
  `verification_code` CHAR( 16 ) NOT NULL COMMENT 'Verification code used by the email verification system',
  `joined` datetime DEFAULT NULL COMMENT 'Date that the user joined',
  `level` int(1) NOT NULL DEFAULT '10' COMMENT 'Access level of user',
  `enabled` ENUM('0','1') NOT NULL DEFAULT '1' COMMENT 'Set to 1 to enable the account',
  `last_active` datetime DEFAULT NULL COMMENT 'When the user last did anything',
  `last_login` datetime DEFAULT NULL COMMENT 'Date and time of last login',
  `last_login_ip` char(39) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Last IP address user logged in from (IPv4 or IPv6)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_data_fields`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_data_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8_unicode_ci NOT NULL,
  KEY `id` (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `user_data`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_data` (
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `user_id` (`user_id`,`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_levels`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci COMMENT 'Description of the level',
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Levels of users' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `user_levels`
--

INSERT IGNORE INTO /*TABLE_PREFIX*/`user_levels` (`id`, `slug`, `description`,'level') VALUES
(1, 'member', 'Standard Member',10),
(2, 'advanced_member', 'Advanced member',20),
(3, 'admin', 'Admin',30),
(4, 'super_admin', 'Super Admin',40);

-- --------------------------------------------------------

--
-- Table structure for table `user_levels`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci COMMENT 'Description of user groups',
  `min_level` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Groupd for users' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_group_members`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_group_members` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Link a user to a group';

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `device` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `device_name` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `remote_addr` char(16) COLLATE utf8_unicode_ci NOT NULL,
  `os` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `browser` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_device` (`user_id`,`device`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `structure_routes`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`structure_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the asset',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `type_id` int(11) NOT NULL COMMENT 'Type of asset linked by ID to the asset_types',
  `group_id` int(11) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) NOT NULL COMMENT 'Filesize in bytes',
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `enabled` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Active,0=Inactive',
  `user` int(11) NOT NULL COMMENT 'ID of the user that added the asset',
  `added` datetime NOT NULL COMMENT 'Date that the asset was added to the system',
  `expiry` date DEFAULT NULL COMMENT 'Date when the image licence expires',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='All assets links, videos, images, documents etc' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `asset_groups`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`asset_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `asset_groups`
--

INSERT IGNORE INTO /*TABLE_PREFIX*/`asset_groups` (`id`, `description`, `slug`, `order`) VALUES
(1, 'Main Site Assets', 'default', 0);

-- --------------------------------------------------------

--
-- Table structure for table `asset_support`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`asset_support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `type` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `data` char(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Table structure for table `asset_types`
--

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/`asset_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the asset type',
  `data_description` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_example` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` char(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The slug of the type, used in the assets folder structure',
  `file_extensions` char(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Allowed file extensions',
  `default_file_extension` char(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The main file (if multiple types are uploaded)',
  `template` char(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The file name of the inline template',
  `icon` char(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='All the different types of assets that can be used in the si' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `asset_types`
--

INSERT INTO /*TABLE_PREFIX*/`asset_types` (`id`, `name`, `data_description`, `data_example`, `slug`, `file_extensions`, `default_file_extension`, `template`, `icon`) VALUES
(1, 'Image', NULL, NULL, 'image', 'png,jpg,jpeg,gif,tiff', NULL, 'image.tpl', 'image.png'),
(2, 'Video', NULL, NULL, 'video', 'avi,mpg,mp4,flv,mov,mpeg,rt,webm,ogv,wmv', 'mp4', 'video.tpl', 'movie.png'),
(3, 'PDF', NULL, NULL, 'pdf', 'pdf', NULL, 'link.tpl', 'pdf.png'),
(4, 'Document', NULL, NULL, 'document', 'doc,docx,txt,rtf,ppt,pptx', NULL, 'link.tpl', 'word.png'),
(5, 'Spreadsheet', NULL, NULL, 'spreadsheet', 'xls,xlsx,csv,xml', NULL, 'link.tpl', 'excel.png'),
(6, 'Archive', NULL, NULL, 'archive', 'zip,rar,tar.gz,tar', NULL, 'link.tpl', 'compressed.png'),
(7, 'Flash Object', NULL, NULL, 'flash', 'swf', NULL, 'flash.tpl', 'flash.png'),
(8, 'Vector Graphic', NULL, NULL, 'vector', 'svg', NULL, 'image.tpl', 'image.png'),
(9, 'External Site Box', 'Site URL', 'http://www.google.co.uk/', 'iframe', NULL, NULL, 'iframe.tpl', 'html.png'),
(10, 'Google Map', 'Coordinates', '50.346039,-3.933332', 'gmap', NULL, NULL, 'gmap.tpl', 'map.png'),
(11, 'Google Map with Marker', 'Coordinates', '50.346039,-3.933332', 'gmap-marker', NULL, NULL, 'gmap-marker.tpl', 'map.png'),
(12, 'YouTube Video', 'Video URL', 'http://www.youtube.com/watch?v=tabyvQhQSks', 'youtube', NULL, NULL, 'youtube.tpl', 'youtube.png');

-- --------------------------------------------------------

