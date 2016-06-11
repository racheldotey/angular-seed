--
-- Table structure for table `trv_log_hot_salsa_venue_signup`
--

CREATE TABLE IF NOT EXISTS `trv_log_hot_salsa_venue_signup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) unsigned NOT NULL,
  `salsa_call_status` varchar(255) NOT NULL DEFAULT '',
  `salsa_location_id` varchar(255) DEFAULT NULL,
  `salsa_location_data` text,
  `salsa_error_message` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`venue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------

--
-- Table structure for table `trv_venues_trivia_schedules`
--

CREATE TABLE IF NOT EXISTS `trv_venues_trivia_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) NOT NULL,
  `trivia_day` varchar(255) NOT NULL,
  `trivia_time` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_user_id` int(11) unsigned NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE `trv_venues` ADD `phone_extension` VARCHAR(5) NOT NULL AFTER `zip`;
ALTER TABLE `trv_venues` DROP `hours`;
ALTER TABLE `trv_venues` ADD `salsa_location_id` VARCHAR(255) NOT NULL AFTER `referral`;
