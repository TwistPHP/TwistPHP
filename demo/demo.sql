-- MySQL dump 10.13  Distrib 5.5.55, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: twistphp
-- ------------------------------------------------------
-- Server version	5.5.55-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `twist_asset_groups`
--

DROP TABLE IF EXISTS `twist_asset_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_asset_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_asset_groups`
--

LOCK TABLES `twist_asset_groups` WRITE;
/*!40000 ALTER TABLE `twist_asset_groups` DISABLE KEYS */;
INSERT INTO `twist_asset_groups` VALUES (1,'Main Site Assets','default',0);
/*!40000 ALTER TABLE `twist_asset_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_asset_support`
--

DROP TABLE IF EXISTS `twist_asset_support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_asset_support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `type` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_asset_support`
--

LOCK TABLES `twist_asset_support` WRITE;
/*!40000 ALTER TABLE `twist_asset_support` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_asset_support` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_asset_types`
--

DROP TABLE IF EXISTS `twist_asset_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_asset_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the asset type',
  `data_description` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_example` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The slug of the type, used in the assets folder structure',
  `file_extensions` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Allowed file extensions',
  `default_file_extension` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The main file (if multiple types are uploaded)',
  `template` char(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The file name of the inline template',
  `icon` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='All the different types of assets that can be used in the si';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_asset_types`
--

LOCK TABLES `twist_asset_types` WRITE;
/*!40000 ALTER TABLE `twist_asset_types` DISABLE KEYS */;
INSERT INTO `twist_asset_types` VALUES (1,'Image',NULL,NULL,'image','png,jpg,jpeg,gif,tiff',NULL,'image.tpl','image.png'),(2,'Video',NULL,NULL,'video','avi,mpg,mp4,flv,mov,mpeg,rt,webm,ogv,wmv','mp4','video.tpl','movie.png'),(3,'PDF',NULL,NULL,'pdf','pdf',NULL,'link.tpl','pdf.png'),(4,'Document',NULL,NULL,'document','doc,docx,txt,rtf,ppt,pptx',NULL,'link.tpl','word.png'),(5,'Spreadsheet',NULL,NULL,'spreadsheet','xls,xlsx,csv,xml',NULL,'link.tpl','excel.png'),(6,'Archive',NULL,NULL,'archive','zip,rar,tar.gz,tar',NULL,'link.tpl','compressed.png'),(7,'Flash Object',NULL,NULL,'flash','swf',NULL,'flash.tpl','flash.png'),(8,'Vector Graphic',NULL,NULL,'vector','svg',NULL,'image.tpl','image.png'),(9,'External Site Box','Site URL','http://www.google.co.uk/','iframe',NULL,NULL,'iframe.tpl','html.png'),(10,'Google Map','Coordinates','50.346039,-3.933332','gmap',NULL,NULL,'gmap.tpl','map.png'),(11,'Google Map with Marker','Coordinates','50.346039,-3.933332','gmap-marker',NULL,NULL,'gmap-marker.tpl','map.png'),(12,'YouTube Video','Video URL','http://www.youtube.com/watch?v=tabyvQhQSks','youtube',NULL,NULL,'youtube.tpl','youtube.png');
/*!40000 ALTER TABLE `twist_asset_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_assets`
--

DROP TABLE IF EXISTS `twist_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the asset',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` int(11) NOT NULL COMMENT 'Type of asset linked by ID to the asset_types',
  `group_id` int(11) NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int(11) NOT NULL COMMENT 'Filesize in bytes',
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `enabled` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Active,0=Inactive',
  `user` int(11) NOT NULL COMMENT 'ID of the user that added the asset',
  `added` datetime NOT NULL COMMENT 'Date that the asset was added to the system',
  `expiry` date DEFAULT NULL COMMENT 'Date when the image licence expires',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='All assets links, videos, images, documents etc';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_assets`
--

LOCK TABLES `twist_assets` WRITE;
/*!40000 ALTER TABLE `twist_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_hooks`
--

DROP TABLE IF EXISTS `twist_hooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_hooks` (
  `hook` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `registered` datetime NOT NULL COMMENT 'Date that the hook was registered',
  UNIQUE KEY `hook` (`hook`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_hooks`
--

LOCK TABLES `twist_hooks` WRITE;
/*!40000 ALTER TABLE `twist_hooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_hooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_packages`
--

DROP TABLE IF EXISTS `twist_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for this installation',
  `slug` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` char(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` char(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder` char(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Set to 1 if this package extends the framework directly',
  `resources` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of resources that are provided',
  `routes` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of routes that are provided',
  `blocks` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of blocks that are provided',
  `extensions` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of packages that are extended',
  `installed` datetime DEFAULT NULL COMMENT 'Date the package was installed',
  `updated` datetime DEFAULT NULL COMMENT 'Date the package was updated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_packages`
--

LOCK TABLES `twist_packages` WRITE;
/*!40000 ALTER TABLE `twist_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_sessions`
--

DROP TABLE IF EXISTS `twist_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_sessions` (
  `id` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_modified` datetime NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PHP Sessions storage for TwistPHP';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_sessions`
--

LOCK TABLES `twist_sessions` WRITE;
/*!40000 ALTER TABLE `twist_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_settings`
--

DROP TABLE IF EXISTS `twist_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_settings` (
  `package` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` enum('core','package') COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` char(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `default` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `null` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `deprecated` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Set to ''1'' if the setting is no longer used by the framework',
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_settings`
--

LOCK TABLES `twist_settings` WRITE;
/*!40000 ALTER TABLE `twist_settings` DISABLE KEYS */;
INSERT INTO `twist_settings` VALUES ('core','core','ARCHIVE_HANDLER','native','Archive Handling Library','Set the library that will handle the creation for archive libraries, PCLZip is available as a package, see twistphp.com','native','options','native','0','0'),('core','core','ASSET_DEFAULT_GROUP','1','Default Asset Group','Default group for assets to be uploaded and browsed','1','integer','','0','0'),('core','core','ASSET_THUMBNAIL_SIZES','32,64,128,256','Asset Default Thumbnail Sizes','Comma separated pixel size, thumbnails of each size specified wil be created upon upload of an Asset. Can be left blank if no thumbnails are required','32,64,128,256','string','','0','0'),('core','core','ASSET_THUMBNAIL_SQUARE_SIZES','32,64,128,256','Asset Default Thumbnail Square Sizes','Comma separated pixel size, square thumbnails of each size specified wil be created upon upload of an Asset. Can be left blank if no thumbnails are required','32,64,128,256','string','','0','0'),('core','core','CACHE_DELAYED_WRITE','1','Cache Delayed Write','Delay writing the cache files to disk, the files will be written during the shutdown cycle of the current PHP runtime','1','boolean','0,1','0','0'),('core','core','CACHE_ENABLED','1','Cache Enabled','Caching of data, templates and pages are handled with the Twist Cache Package. Disabling the cache will return \'null\' for all cache requests, no new data will be stored. Cache files can still be removed when disabled','1','boolean','0,1','0','0'),('core','core','CACHE_GB_PROBABILITY','0','Cache Garbage Collection','Set the probability of a user collecting garbage between 1-10 when and instance of cache is launched. Default is set to 0, will never collect garbage (Expired items deleted when next used)','0','options','0,1,2,3,4,5,6,7,8,9,10','0','0'),('core','core','CURRENCY_CONVERSION_API','YahooAPIs','Currency Conversion API','Set the currency converter to use either Yahoo Currency API or WebserviceX.net Currency API both of which are free to use.','YahooAPIs','options','WebserviceX.net,YahooAPIs','0','0'),('core','core','DATETIME_SOURCE','php','Time Source','Choose where the datetime class should obtain its timestamp from','php','options','php,mysql','0','0'),('core','core','DEFAULT_COUNTRY','GB','Default Country','Set to the default country ISO code of your site','GB','country_iso','','0','0'),('core','core','DEFAULT_CURRENCY','GBP','Default Currency','Set to the default currency ISO code of your site','GBP','currency_iso','','0','0'),('core','core','DEFAULT_LANGUAGE','EN','Default Language','Set to the default language ISO code of your site','EN','language_iso','','0','0'),('core','core','DEVELOPMENT_DEBUG_BAR','0','Development Debug Bar','Display the debug bar on the site when the framework is in development mode','0','boolean','1,0','0','0'),('core','core','DEVELOPMENT_EVENT_RECORDER','1','Development Event Recorder','Record loading times of various parts for the framework, the results can be seen in Debug Bar and on the exception/Inspector page. (Only works when the framework is in development mode)','1','boolean','1,0','0','0'),('core','core','DEVELOPMENT_MODE','1','Development Mode','Output full exceptions and allow dumping of variables to screen','1','boolean','1,0','0','0'),('core','core','EMAIL_CHAR_ENCODING','ISO-8859-1','Default Char Encoding','Set the default char encoding to be used when sending emails','ISO-8859-1','string','','0','0'),('core','core','EMAIL_MULTIPART_ENCODING','7bit','Default Encoding','Set the default encoding type to use when sending emails','7bit','options','7bit,base64','0','0'),('core','core','EMAIL_PROTOCOL','native','Email Send Protocol','Select the protocol to use when sending all emails','native','options','native,smtp','0','0'),('core','core','EMAIL_SMTP_HOST','localhost','SMTP Host','Set the SMTP Host needed to login to the SMTP email server','localhost','string','','0','0'),('core','core','EMAIL_SMTP_PASSWORD','','SMTP Password','Set the SMTP Password needed to login to the SMTP email server','','string','','0','0'),('core','core','EMAIL_SMTP_PORT','25','SMTP Port','Set the SMTP Port (Default: 25) needed to login to the SMTP email server','25','integer','','0','0'),('core','core','EMAIL_SMTP_USERNAME','','SMTP Username','Set the SMTP Username (usually an email address) needed to login to the SMTP email server','','string','','0','0'),('core','core','ERROR_EXCEPTION_HANDLING','1','Exception Handling','Exception handling to output a more user friendly page in the event of a exception.','1','boolean','1,0','0','0'),('core','core','ERROR_FATAL_HANDLING','1','Fatal Error Handling','Fatal Error handling to output a more user friendly page in the event of a fatal error.','1','boolean','1,0','0','0'),('core','core','ERROR_HANDLING','1','Error Handling','Error handlers are very important, stops the output of errors to the screen.','1','boolean','1,0','0','0'),('core','core','ERROR_LOG','Daily','PHP Error Logging','Log all PHP errors to a file in app/Logs, the output can either be one file (Single) or split into Daily, Weekly or Monthly files. Single log files will be split when they reach 5MB in size.','Daily','options','Off,Single,Daily,Weekly,Monthly','0','0'),('core','core','ERROR_LOGS_MAX','7','Max PHP Error Logs','Number of PHP Error log files file keep, log files will be removed starting with the oldest first.','7','options','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,31,62,365','0','0'),('core','core','ERROR_SCREEN','0','Output Errors to Screen','Output Errors to Screen','0','boolean','1,0','0','0'),('core','core','FILE_MAX_UPLOAD_SIZE','50M','Max Upload Size','Max allowed file size of assets uploaded to the system, PHP Upload and form post limits still apply','50M','options','1M,2M,5M,10M,20M,30M,50M,100M,250M,375M,500M,750M,1000M','0','0'),('core','core','FTP_LIBRARY','native','FTP Library','Choose which connection library the FTP package should use, to use \'native\' the ftp_ PHP functions must be installed','native','options','native,socket','0','0'),('core','core','GZIP_COMPRESSION','1','GZip Compress PHP Output','Compress all the PHP output to the browser using GZip compression, currently this setting only applies to output when served by Routes.','1','boolean','0,1','0','0'),('core','core','GZIP_COMPRESSION_LEVEL','6','GZip Compression Level','Set a level between 1 and 6, 1 being the lowest amount of compression and 6 being the highest. Yopu might want to decrease the CPU load on a busy server by lowering the compression level. Default level is 6.','6','option','1,2,3,4,5,6','0','0'),('core','core','HTACCESS_CACHE_CSS','0','CSS Cache Control','Set the number of seconds for the CSS to be cached in the browser - Cache Control (max-age)','0','integer','','0','0'),('core','core','HTACCESS_CACHE_HTML','0','HTML Cache Control','Set the number of seconds for the HTML to be cached in the browser - Cache Control (max-age)','0','integer','','0','0'),('core','core','HTACCESS_CACHE_IMAGES','0','Image Cache Control','Set the number of seconds for the Image to be cached in the browser - Cache Control (max-age)','0','integer','','0','0'),('core','core','HTACCESS_CACHE_JS','0','JS Cache Control','Set the number of seconds for the JS to be cached in the browser - Cache Control (max-age)','0','integer','','0','0'),('core','core','HTACCESS_CUSTOM','','Custom','Enter all custom .htaccess functionality here, the .htaccess file is rebuilt by TwistPHP, any changes to the file directly may be lost if rebuilt with the manager','','string','','0','0'),('core','core','HTACCESS_DEFLATE_CSS','0','CSS Compression','Compress the output of CSS from the server using DEFLATE','0','boolean','0,1','0','0'),('core','core','HTACCESS_DEFLATE_HTML','0','HTML Compression','Compress the output of HTML from the server using DEFLATE','0','boolean','0,1','0','0'),('core','core','HTACCESS_DEFLATE_IMAGES','0','Image Compression','Compress the output of Image from the server using DEFLATE','0','boolean','0,1','0','0'),('core','core','HTACCESS_DEFLATE_JS','0','JS Compression','Compress the output of JS from the server using DEFLATE','0','boolean','0,1','0','0'),('core','core','HTACCESS_DIRECTORY_INDEX','index.php index.html index.htm','Directory Index','Set the index order for apache to lookup when serving a directory','index.php index.html index.htm','string','','0','0'),('core','core','HTACCESS_DISABLE_DIRBROWSING','1','Disable Directory Browsing','Disallow the browsing of server/site directories in the web browser','1','boolean','0,1','0','0'),('core','core','HTACCESS_DISABLE_EXTENSIONS','bash|git|hg|log|svn|swp|cvs','Disallow File Extensions','Disallow files with the extensions from being served. Each file extension must be separated with a | symbol, Default value: bash|git|hg|log|svn|swp|cvs','bash|git|hg|log|svn|swp|cvs','string','','0','0'),('core','core','HTACCESS_DISABLE_HOTLINKS','0','Disabled Image Hotlinks','Disallow hotlinking of images from your website or server, remote image requested will not be served','0','boolean','0,1','0','0'),('core','core','HTACCESS_DISABLE_HTACCESS','1','Disallow serving .htaccess','Do not allow the contents of HTaccess and HTPassword files to be served by apache','1','boolean','0,1','0','0'),('core','core','HTACCESS_DISABLE_QUERYSTRINGS','1','Disable Suspicious Query Strings','Disallow all page requests that contain suspicious query strings','1','boolean','0,1','0','0'),('core','core','HTACCESS_DISABLE_UPLOADEDPHP','1','Disable Uploaded PHP','Stop PHP files being executed from within the sites upload directory','1','boolean','0,1','0','0'),('core','core','HTACCESS_ETAG','1','Allow ETags','Allow the ETag header to be used','1','boolean','0,1','0','0'),('core','core','HTACCESS_REVALIDATE_CSS','1','CSS Allow Revalidate','Allow the cached CSS to be revalidated using the last-modified header','1','boolean','0,1','0','0'),('core','core','HTACCESS_REVALIDATE_HTML','1','HTML Allow Revalidate','Allow the cached HTML to be revalidated using the last-modified header','1','boolean','0,1','0','0'),('core','core','HTACCESS_REVALIDATE_IMAGES','1','Image Allow Revalidate','Allow the cached Image to be revalidated using the last-modified header','1','boolean','0,1','0','0'),('core','core','HTACCESS_REVALIDATE_JS','1','JS Allow Revalidate','Allow the cached JS to be revalidated using the last-modified header','1','boolean','0,1','0','0'),('core','core','HTACCESS_REWRITES','','Rewrite Rule','A JSON string containing all the re-write rules format per rule {\'rule\':\'\',\'redirect\':\'\',\'options\':\'\'}, use the manager to edit this option','','string','','0','0'),('core','core','MAINTENANCE_MODE','0','Maintenance Mode','Output the 503 SEO friendly maintenance page to all visitors of the website (search engines will not index this page)','0','boolean','1,0','0','0'),('core','core','PHP_MAX_EXECUTION','30','PHP Max Execution','Set the max execution time for all your PHP scripts that use the framework','30','integer','','0','0'),('core','core','PHP_MEMORY_LIMIT','128M','PHP Memory Limit','Set the memory limit for all your PHP scripts that use the framework','128M','options','32M,64M,128M,256M,512M,1G,2G,3G,4G','0','0'),('core','core','RANDOM_STRING_LENGTH','16','Random String Length','Set the default length for the output of the Tools()->randomString() function call','16','integer','','0','0'),('core','core','RESOURCE_INCLUDE_ONCE','1','Resource Include Once','When using the resource View tags, css and js resources will only be included once per page load/render.','1','boolean','1,0','0','0'),('core','core','RESOURCE_VERSION_OVERRIDE','1','Resource Version Override','Allow calls to Resource->extendLibrary to override existing (matching) version of a file, for example jQuery v2.1.1 from the core would be over-ridden by the new jQuery v2.1.1 where as jQuery v2.1.2 would be added as a new resource.','1','boolean','1,0','0','0'),('core','core','ROUTE_CASE_SENSITIVE','1','Case Sensitive URI Routing','Enable case sensitive URI routing in the routes module, turning this off may result in multiples of a single page being indexed by search engines. This setting dose not effect the wild card portion of URIs.','1','boolean','1,0','0','0'),('core','core','SESSION_HANDLER','native','Session Handler','Choose the PHP session handler to use, \'native\' is the default builtin PHP session handler','native','options','native,file,memcache,mysql','0','0'),('core','core','SITE_AUTHOR','','Site Author','Enter the default author of your website','','string','','0','0'),('core','core','SITE_DESCRIPTION','','Site Description','Enter the default meta description for your website','','string','','0','0'),('core','core','SITE_HOST','localhost:8080','Site Host','Enter the HTTP Host name of the website for instance http://www.example.com/index.php would be \'example.com\'','','string','','0','0'),('core','core','SITE_KEYWORDS','','Site Keywords','Enter the default set of keywords for your website','','string','','0','0'),('core','core','SITE_NAME','TwistPHP Sandbox','Site Name','Enter the default name of your website','','string','','0','0'),('core','core','SITE_PROTOCOL','http','Site Protocol','Select the primary protocol for the website','http','options','http,https','0','0'),('core','core','SITE_PROTOCOL_FORCE','0','Force Site Protocol/Prefix','Redirect the user to the selected SITE_PROTOCOL and SITE_WWW prefix, changing this will require a rebuild of the .htaccess file','0','boolean','','0','0'),('core','core','SITE_TRAILING_SLASH','0','Site Trailing Slash','URIs and URLs in Routes and Structure will have a trailing slash \'/\' suffix','0','boolean','','0','0'),('core','core','SITE_WWW','0','Site Use WWW','Redirect the user to WWW. if they are not already on that sub-domain. Also used when building the full site URL','0','boolean','','0','0'),('core','core','TIMEZONE','Europe/London','Timezone','Set the timezone of your website','Europe/London','string','','0','0'),('core','core','USER_AUTO_AUTHENTICATE','0','Auto Authenticate','Automatically log the user in upon successful registration. This will only work if USER_EMAIL_VERIFICATION is \'off\' and USER_REGISTER_PASSWORD is \'on\'','0','boolean','1,0','1','0'),('core','core','USER_COMMON_PASSWORD_FILTER','1','Common Password Filter','Stop the user from using any of the top 10,000 most common passwords','1','boolean','1,0','0','0'),('core','core','USER_EMAIL_VERIFICATION','0','Verify Email','Verify a registered user by email before they can login to their account','0','boolean','1,0','0','0'),('core','core','USER_LEVEL_ADMIN','30','User Level (Admin)','Level (integer) of a admin user used to restrict access through routes','30','integer','','0','0'),('core','core','USER_LEVEL_ADVANCED','20','User Level (Advanced)','Level (integer) of a advanced user used to restrict access through routes','20','integer','','0','0'),('core','core','USER_LEVEL_MEMBER','10','User Level (Member)','Level (integer) of a member user used to restrict access through routes','10','integer','','0','0'),('core','core','USER_LEVEL_SUPERADMIN','40','User Level (Super Admin)','Level (integer) of a super admin user used to restrict access through routes','40','integer','','0','0'),('core','core','USER_MIN_PASSWORD_LENGTH','6','Min Password Length','Set the minimum password length to allow','6','integer','','0','0'),('core','core','USER_PASSWORD_CHANGE','1','Force Temporary Password Change','Require a user to change there password upon login (if the password is a temporary one)','1','boolean','1,0','0','0'),('core','core','USER_PASSWORD_CHANGE_EMAIL','0','Send Password Change Email','Determine if an email should be send when a user changes their account password (Excludes password reset)','0','boolean','1,0','0','0'),('core','core','USER_PASSWORD_ENCRYPTION','sha1','Standard Encryption','If \'Strong Password Encryption\' is set to false then encryption type must be set, Options \'sha1\',\'sha512\'','sha1','options','sha1,sha512','0','0'),('core','core','USER_PASSWORD_HASH','0','Strong Password Encryption','Hash the passwords, this function requires PHP MCRYPT to be installed, More secure and reliable than SHA1','0','boolean','1,0','0','0'),('core','core','USER_REGISTER_PASSWORD','1','User Register Password','The user must enter a password during the registration process (enables a password field on registration form)','1','boolean','1,0','0','0'),('core','core','USER_REMEMBER_LENGTH','168','Remember Length','Set the time in Hours (Default: 168, which is 7 Days in Hours)','168','integer','','0','0'),('core','core','VIEW_PRE_PROCESS_CACHE','84600','PreProcess Cache Life','The amount of time to store a Pre Processed view cache, this cache helps speed up the processing of views','84600','integer','','0','0');
/*!40000 ALTER TABLE `twist_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_user_data`
--

DROP TABLE IF EXISTS `twist_user_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_user_data` (
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci,
  UNIQUE KEY `user_id` (`user_id`,`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_user_data`
--

LOCK TABLES `twist_user_data` WRITE;
/*!40000 ALTER TABLE `twist_user_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_user_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_user_data_fields`
--

DROP TABLE IF EXISTS `twist_user_data_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_user_data_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `slug` (`slug`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_user_data_fields`
--

LOCK TABLES `twist_user_data_fields` WRITE;
/*!40000 ALTER TABLE `twist_user_data_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_user_data_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_user_group_members`
--

DROP TABLE IF EXISTS `twist_user_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_user_group_members` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  UNIQUE KEY `user_group` (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link a user to a group';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_user_group_members`
--

LOCK TABLES `twist_user_group_members` WRITE;
/*!40000 ALTER TABLE `twist_user_group_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_user_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_user_groups`
--

DROP TABLE IF EXISTS `twist_user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of user groups',
  `min_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Groupd for users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_user_groups`
--

LOCK TABLES `twist_user_groups` WRITE;
/*!40000 ALTER TABLE `twist_user_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_user_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_user_levels`
--

DROP TABLE IF EXISTS `twist_user_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_user_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of the level',
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Levels of users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_user_levels`
--

LOCK TABLES `twist_user_levels` WRITE;
/*!40000 ALTER TABLE `twist_user_levels` DISABLE KEYS */;
INSERT INTO `twist_user_levels` VALUES (1,'member','Standard Member',10),(2,'advanced_member','Advanced member',20),(3,'admin','Admin',30),(4,'super_admin','Super Admin',40);
/*!40000 ALTER TABLE `twist_user_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_user_sessions`
--

DROP TABLE IF EXISTS `twist_user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` char(230) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device` char(230) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remote_addr` char(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `os` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `browser` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_validated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_device` (`user_id`,`device`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twist_user_sessions`
--

LOCK TABLES `twist_user_sessions` WRITE;
/*!40000 ALTER TABLE `twist_user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `twist_user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twist_users`
--

DROP TABLE IF EXISTS `twist_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twist_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `email` char(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique email of user',
  `firstname` char(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Users first name',
  `surname` char(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The last (given) name of the user',
  `password` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash of the users password using SHA512 algorithm',
  `temp_password` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Set to 1 if the users password is temp',
  `verified` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Set to 1 if email address is verified',
  `verification_code` char(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Verification code used by the email verification system',
  `joined` datetime DEFAULT NULL COMMENT 'Date that the user joined',
  `level` int(1) NOT NULL DEFAULT '10' COMMENT 'Access level of user',
  `enabled` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'Set to 1 to enable the account',
  `last_active` datetime DEFAULT NULL COMMENT 'When the user last did anything',
  `last_login` datetime DEFAULT NULL COMMENT 'Date and time of last login',
  `last_login_ip` char(39) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Last IP address user logged in from (IPv4 or IPv6)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-19 18:49:17
