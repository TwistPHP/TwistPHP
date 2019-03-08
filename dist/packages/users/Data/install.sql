
--
-- Table structure for table `notifications`
--

/*CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `read` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `notification_queue`
--

CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` char(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `user_email` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_cc` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` char(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `html` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` char(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','processing','sent','delete','failed','restricted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `error` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_attempts` int(11) NOT NULL DEFAULT 0,
  `started` datetime DEFAULT NULL,
  `added` datetime NOT NULL,
  `sent` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ; */