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

/*
  @version 1.1.0
*/
ALTER TABLE `_search`
  DROP metaphone,
  ADD COLUMN `lastModified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `content`,
  ADD COLUMN `lastPublished` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `lastModified`,
  ADD COLUMN `lastIndexed` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `lastPublished`,
  ADD COLUMN `priority` float DEFAULT 0.5 AFTER `content`
;
