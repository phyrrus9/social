-- Initial MySQL structure for social script -phyrrus9
-- use this file as part of the setup program. It will ask for an initial
-- database, this is what you need to use
-- I plan to make this process automatic eventually.
-- this file will ONLY set up the structure, no initial data is provided
DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `owner` int(11) NOT NULL,
  `friend` int(11) NOT NULL
);
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`)
);
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(72) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
);