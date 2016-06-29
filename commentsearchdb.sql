create database if not exists commentsearchdb;
use commentsearchdb;

drop table if exists comments;
CREATE TABLE `comments` (
  `comment_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `comment_text` longtext,
  PRIMARY KEY (`comment_id`),
  FULLTEXT KEY `comment_text` (`comment_text`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

