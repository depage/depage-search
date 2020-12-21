/*
    @tablename _search

    @version 1.0.0
*/
CREATE TABLE `_search` (
  `url` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `title` text NOT NULL,
  `description` text NOT NULL,
  `headlines` text NOT NULL,
  `content` longtext NOT NULL,
  `metaphone` longtext NOT NULL,
  PRIMARY KEY (`url`),
  FULLTEXT KEY `content` (`title`,`description`,`headlines`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
