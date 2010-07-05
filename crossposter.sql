CREATE TABLE `crossposter` (
  `id` 		int(5) NOT NULL auto_increment,
  `link` 	text NOT NULL,
  `livejournal` enum('0','1') NOT NULL default '0',
  `twitpic` 	enum('0','1') NOT NULL default '0',
  `vkontakte` 	enum('0','1') NOT NULL default '0',
  `friendfeed` 	enum('0','1') NOT NULL default '0',
  `facebook` 	enum('0','1') NOT NULL default '0',
  `tumblr` 		enum('0','1') NOT NULL default '0',
  KEY `id` (`id`),
  FULLTEXT KEY `link` (`link`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
