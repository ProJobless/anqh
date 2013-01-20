CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8_swedish_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_swedish_ci NOT NULL,
  `homepage` varchar(250) COLLATE utf8_swedish_ci DEFAULT NULL,
  `gender` char(1) COLLATE utf8_swedish_ci DEFAULT NULL,
  `picture` varchar(250) COLLATE utf8_swedish_ci DEFAULT NULL,
  `description` text COLLATE utf8_swedish_ci,
  `city_name` varchar(30) COLLATE utf8_swedish_ci DEFAULT NULL,
  `login_count` int(11) DEFAULT '0',
  `post_count` int(11) DEFAULT '0',
  `adds` int(11) DEFAULT '0',
  `mods` int(11) DEFAULT '0',
  `signature` varchar(200) COLLATE utf8_swedish_ci DEFAULT NULL,
  `avatar` varchar(250) COLLATE utf8_swedish_ci DEFAULT NULL,
  `title` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `forum_areas` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `views` int(11) DEFAULT '0',
  `heardfrom` varchar(250) COLLATE utf8_swedish_ci DEFAULT NULL,
  `hidemail` int(11) DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_swedish_ci DEFAULT NULL,
  `hostname` varchar(150) COLLATE utf8_swedish_ci DEFAULT NULL,
  `new_comment_count` int(11) DEFAULT '0',
  `comment_count` int(11) DEFAULT '0',
  `left_comment_count` int(11) DEFAULT '0',
  `notifychanges` int(11) DEFAULT NULL,
  `address_street` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `address_city` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `address_zip` varchar(5) COLLATE utf8_swedish_ci DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `position` varchar(100) COLLATE utf8_swedish_ci DEFAULT NULL,
  `stylesheet` varchar(250) COLLATE utf8_swedish_ci DEFAULT NULL,
  `theme` int(11) DEFAULT NULL,
  `name_aim` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `name_msn` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `name_yahoo` varchar(50) COLLATE utf8_swedish_ci DEFAULT NULL,
  `name_icq` varchar(15) COLLATE utf8_swedish_ci DEFAULT NULL,
  `name_skype` varchar(32) COLLATE utf8_swedish_ci DEFAULT NULL,
  `smileys` int(11) DEFAULT NULL,
  `allow_comments` int(11) DEFAULT NULL,
  `online` int(11) DEFAULT NULL,
  `window` text COLLATE utf8_swedish_ci,
  `language` int(11) DEFAULT NULL,
  `password` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  `last_login` int(11) DEFAULT NULL,
  `geo_city_id` int(11) DEFAULT NULL,
  `default_image_id` int(11) DEFAULT NULL,
  `old_login` int(11) DEFAULT NULL,
  `username_clean` varchar(30) COLLATE utf8_swedish_ci DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `username_2` (`username`),
  UNIQUE KEY `username_clean` (`username_clean`),
  KEY `city_id` (`geo_city_id`),
  KEY `default_image_id` (`default_image_id`),
  CONSTRAINT `users_default_image_id` FOREIGN KEY (`default_image_id`) REFERENCES `images` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;