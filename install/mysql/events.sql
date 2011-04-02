CREATE TABLE `events` (
  `id` int(11) NOT NULL auto_increment,
  `event` date default NULL,
  `name` varchar(100) collate utf8_swedish_ci NOT NULL,
  `homepage` varchar(100) collate utf8_swedish_ci default NULL,
  `venue_name` varchar(100) collate utf8_swedish_ci default NULL,
  `venue_url` varchar(100) collate utf8_swedish_ci default NULL,
  `city_name` varchar(50) collate utf8_swedish_ci default NULL,
  `dj` text collate utf8_swedish_ci,
  `hours_from` int(11) default NULL,
  `hours_to` int(11) default NULL,
  `age` int(11) default NULL,
  `price` double default NULL,
  `music` text collate utf8_swedish_ci,
  `info` text collate utf8_swedish_ci,
  `modified_by` varchar(512) collate utf8_swedish_ci default NULL,
  `modifies` int(11) default '0',
  `views` int(11) default '0',
  `flyer_front_url` varchar(100) collate utf8_swedish_ci default NULL,
  `flyer_back_url` varchar(100) collate utf8_swedish_ci default NULL,
  `start_time` datetime default NULL,
  `end_time` datetime default NULL,
  `venue_id` int(11) default NULL,
  `city_id` int(11) default NULL,
  `country_id` int(11) default NULL,
  `author_id` int(11) default NULL,
  `title` varchar(50) collate utf8_swedish_ci default NULL,
  `flyer_front_image_id` int(11) default NULL,
  `flyer_back_image_id` int(11) default NULL,
  `price2` double(15,3) default NULL,
  `created` int(11) default NULL,
  `modified` int(11) default NULL,
  `geo_city_id` int(11) default NULL,
  `geo_country_id` int(11) default NULL,
  `stamp_begin` int(11) default NULL,
  `stamp_end` int(11) default NULL,
  `favorite_count` int(11) default '0',
  `venue_hidden` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `author_id` (`author_id`),
  KEY `place_id` (`venue_id`),
  KEY `city_id` (`city_id`),
  KEY `country_id` (`country_id`),
  KEY `flyer_front_image_id` (`flyer_front_image_id`),
  KEY `flyer_back_image_id` (`flyer_back_image_id`),
  KEY `geo_city_id` (`geo_city_id`),
  KEY `geo_country_id` (`geo_country_id`),
  CONSTRAINT `events_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_city_id` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_country_id` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_flyer_back_image_id` FOREIGN KEY (`flyer_back_image_id`) REFERENCES `images` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_flyer_front_image_id` FOREIGN KEY (`flyer_front_image_id`) REFERENCES `images` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_geo_city_id` FOREIGN KEY (`geo_city_id`) REFERENCES `geo_cities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_geo_country_id` FOREIGN KEY (`geo_country_id`) REFERENCES `geo_countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_venue_id` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
