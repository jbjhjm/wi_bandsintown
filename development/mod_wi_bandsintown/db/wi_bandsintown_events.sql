
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `#__wi_bandsintown_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bitid` int(10) unsigned NOT NULL COMMENT 'bandsintown event id',
  `visible` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `datetime` datetime NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ticket_url` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ticket_type` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ticket_status` varchar(255) CHARACTER SET utf8 NOT NULL,
  `facebook_rsvp_url` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `artists` text CHARACTER SET utf8 NOT NULL,
  `venue` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bitid` (`bitid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
