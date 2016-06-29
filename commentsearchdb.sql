create database commentsearchdb;

use commentsearchdb;

CREATE TABLE `comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment_text` longtext,
  PRIMARY KEY (`comment_id`),
  FULLTEXT KEY `comment_text` (`comment_text`)
) ENGINE=MyISAM AUTO_INCREMENT=201908 DEFAULT CHARSET=latin1;
