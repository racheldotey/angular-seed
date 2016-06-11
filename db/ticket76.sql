
ALTER TABLE `trv_venues` ADD `created_by_user_type` ENUM('h','o') NOT NULL DEFAULT 'o' COMMENT 'h=host,o=owner' ;


CREATE TABLE IF NOT EXISTS `trv_hosts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`trv_users_id` int(11) NOT NULL,
`address` varchar(255) NOT NULL,
`address_b` varchar(255) NOT NULL,
`phone_extension` varchar(5) NOT NULL,
`phone` varchar(17) NOT NULL,
`city` varchar(255) NOT NULL,
`state` varchar(2) NOT NULL,
`zip` int(5) NOT NULL,
`website` varchar(255) NOT NULL,
`facebook_url` varchar(255) NOT NULL,
`accepted_terms` enum('y','n') NOT NULL DEFAULT 'n' COMMENT 'Y=yes,N=no',
`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`created_user_id` int(11) NOT NULL,
`last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`disabled` timestamp NULL DEFAULT NULL,
`last_updated_by` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `trv_hosts_venues` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`host_id` int(11) NOT NULL,
`venue_id` int(11) NOT NULL,
`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`created_user_id` int(11) NOT NULL,
`last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`last_updated_by` int(11) NOT NULL,
`created_by_user_type` enum('h','o') NOT NULL DEFAULT 'o' COMMENT 'h=host,o=owner',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `trv_hosts_trivia_nights` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`host_id` int(11) NOT NULL,
`venue_id` int(11) NOT NULL,
`trivia_day` varchar(255) NOT NULL,
`trivia_time` varchar(255) NOT NULL,
`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`created_user_id` int(11) NOT NULL,
`last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`last_updated_by` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;