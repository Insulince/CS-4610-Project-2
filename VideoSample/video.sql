DROP TABLE IF EXISTS `video`;
CREATE TABLE IF NOT EXISTS `video` (
`vid` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `youtube_id` varchar(50) NOT NULL,
  PRIMARY KEY  (vid)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

INSERT INTO `video` (`vid`, `title`, `description`, `youtube_id`) VALUES
(1, 'title 1', 'description 1', 'dQ2YNKbGqFc'),
(2, 'title', 'description', 'rIBRcQdzWQs'),
(3, 'title', 'description', 'UVUwqxuDb9A'),
(4, 'title', 'description', 'SZUcEmREZ9Y'),
(5, 'title', 'description', 'tFdlhlmQ-ek'),
(6, 'title', 'description', 'A6YB233OdSg'),
(7, 'title', 'description', 'p3PIn2o78nM'),
(8, 'title', 'description', 'U4S8TxR-AKg'),
(9, 'title', 'description', 'myatGlT7R4w'),
(10, 'title', 'description', 'VLW6kT0-qeQ'),
(11, 'title', 'description', 'S3xeouPc504');