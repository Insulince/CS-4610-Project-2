#Create the database.
DROP DATABASE IF EXISTS `videodb`;
CREATE DATABASE IF NOT EXISTS `videodb`
  DEFAULT CHARACTER SET latin1;
USE `videodb`;

#Create the Video table.
DROP TABLE IF EXISTS `video`;
CREATE TABLE IF NOT EXISTS `video` (
  `vid`              INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `youtubeId`        VARCHAR(11)      NOT NULL,
  `title`            VARCHAR(255)     NOT NULL,
  `suggestedQuality` VARCHAR(255)     NOT NULL DEFAULT 'default',
  PRIMARY KEY (`vid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

#Create the Concept table.
DROP TABLE IF EXISTS `concept`;
CREATE TABLE IF NOT EXISTS `concept` (
  `cid`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vid`          INT(10) UNSIGNED NOT NULL,
  `name`         VARCHAR(255)     NOT NULL,
  `startSeconds` INT(10) UNSIGNED NOT NULL,
  `endSeconds`   INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`cid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

#Create the Lesson table.
DROP TABLE IF EXISTS `lesson`;
CREATE TABLE IF NOT EXISTS `lesson` (
  `lid`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255)     NOT NULL,
  PRIMARY KEY (`lid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

#Create the LessonVideo (Segment) table.
DROP TABLE IF EXISTS `lessonvideo`;
CREATE TABLE IF NOT EXISTS `lessonvideo` (
  `lvid`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lid`          INT(10) UNSIGNED NOT NULL,
  `vid`          INT(10) UNSIGNED NOT NULL,
  `startSeconds` INT(10) UNSIGNED NOT NULL,
  `endSeconds`   INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`lvid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;