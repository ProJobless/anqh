CREATE TABLE `images` (
  `id` int(11) NOT NULL auto_increment,
  `status` char(1) collate utf8_swedish_ci default 'v',
  `author_id` int(11) default NULL,
  `view_count` int(11) default NULL,
  `last_view` datetime default NULL,
  `comment_count` int(11) default NULL,
  `rate_count` int(11) default NULL,
  `rate_total` int(11) default NULL,
  `description` varchar(250) collate utf8_swedish_ci default NULL,
  `original_size` int(11) default NULL,
  `original_width` int(11) default NULL,
  `original_height` int(11) default NULL,
  `width` int(11) default NULL,
  `height` int(11) default NULL,
  `thumb_width` int(11) default NULL,
  `thumb_height` int(11) default NULL,
  `format` varchar(3) collate utf8_swedish_ci default NULL,
  `created` int(11) default NULL,
  `modified` int(11) default NULL,
  `postfix` varchar(8) collate utf8_swedish_ci default NULL,
  `new_comment_count` int(11) default NULL,
  `legacy_filename` varchar(64) collate utf8_swedish_ci default NULL,
  `file` varchar(64) collate utf8_swedish_ci default NULL,
  `rand` int(11) default NULL,
  `remote` varchar(250) collate utf8_swedish_ci default NULL,
  `description_old` varchar(250) collate utf8_swedish_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `images_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
