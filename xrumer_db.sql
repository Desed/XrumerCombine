
-- ----------------------------
-- Table structure for `xrumer_db`
-- ----------------------------
DROP TABLE IF EXISTS `xrumer_db`;
CREATE TABLE `xrumer_db` (
  `guid` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `url_host` varchar(255) DEFAULT NULL,
  `domain_level` int(3) DEFAULT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `href_rel` varchar(128) DEFAULT NULL,
  `ya_x` int(11) DEFAULT NULL,
  `recaptcha` int(3) DEFAULT NULL,
  `user_login` varchar(128) DEFAULT NULL,
  `user_password` varchar(128) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `xrumer_log`
-- ----------------------------
DROP TABLE IF EXISTS `xrumer_log`;
CREATE TABLE `xrumer_log` (
  `guid` int(11) NOT NULL AUTO_INCREMENT,
  `count_file` int(12) NOT NULL,
  `count_file_new` int(12) NOT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8;

