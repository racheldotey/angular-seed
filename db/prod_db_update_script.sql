	
-- //////// DELETE Tables ////////

DROP TABLE `trv_tokens_team_invites`;

-- //////// RENAME Tables ////////

RENAME TABLE `trv_log_hot_salsa_signup` TO `trv_logs_hot_salsa_signup`;

-- //////// New Tables ////////

CREATE TABLE `trv_email_templates` (
  `id` int(11) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `reply_email` varchar(255) DEFAULT NULL,
  `reply_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `body_plain` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` datetime NOT NULL,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `trv_email_templates` ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`);
ALTER TABLE `trv_email_templates` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE trv_tokens_player_invites (
  `id` int(11) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `team_id` int(11) UNSIGNED NULL,
  `user_id` int(11) UNSIGNED NULL,
  `name_first` varchar(100) NULL,
  `name_last` varchar(100) NULL,
  `email` varchar(255) NULL,
  `phone` varchar(20) NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE trv_tokens_player_invites ADD PRIMARY KEY (`id`);
ALTER TABLE trv_tokens_player_invites MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE trv_logs_game_checkins (
  `id` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `trv_logs_game_checkins` ADD PRIMARY KEY (`id`), 
ADD KEY `game_id` (`game_id`), 
ADD KEY `team_id` (`team_id`);
ALTER TABLE `trv_logs_game_checkins` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `trv_venues_trivia_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_id` int(11) NOT NULL,
  `trivia_day` varchar(255) NOT NULL,
  `trivia_time` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) unsigned NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venue_id` (`venue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
PRIMARY KEY (`id`),
  KEY `trv_users_id` (`trv_users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `trv_hosts_venues` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`host_id` int(11) NOT NULL,
`venue_id` int(11) NOT NULL,
`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`created_user_id` int(11) NOT NULL,
`last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
`last_updated_by` int(11) NOT NULL,
`created_by_user_type` enum('h','o') NOT NULL DEFAULT 'o' COMMENT 'h=host,o=owner',
PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `venue_id` (`venue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `venue_id` (`venue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `trv_logs_hot_salsa_venue_signup` (
  `id` int(11) UNSIGNED NOT NULL,
  `venue_id` int(11) UNSIGNED NOT NULL,
  `salsa_call_status` varchar(255) NOT NULL DEFAULT '',
  `salsa_location_id` varchar(255) DEFAULT NULL,
  `salsa_location_data` text,
  `salsa_error_message` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `trv_logs_hot_salsa_venue_signup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`);
ALTER TABLE `trv_logs_hot_salsa_venue_signup` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- //////// ALTER Tables ////////

ALTER TABLE `trv_email_templates` ADD UNIQUE(`identifier`);

ALTER TABLE `trv_logs_login_location` CHANGE `ip_address` `ip_address` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `trv_venues` ADD `disabled` TIMESTAMP NULL DEFAULT NULL AFTER `last_updated_by`;
ALTER TABLE `trv_venues` ADD `phone_extension` VARCHAR(5) NOT NULL AFTER `zip`;
ALTER TABLE `trv_venues` DROP `hours`;
ALTER TABLE `trv_venues` ADD `salsa_location_id` VARCHAR(255) NOT NULL AFTER `referral`;

ALTER TABLE `trv_game_score_teams` CHANGE `score` `score` DECIMAL(7,2) NOT NULL;
ALTER TABLE `trv_game_score_rounds` CHANGE `score` `score` DECIMAL(7,2) NOT NULL DEFAULT '0.00';

ALTER TABLE `trv_tokens_player_invites` ADD `last_visited` TIMESTAMP NULL DEFAULT NULL AFTER `expires`;
ALTER TABLE `trv_tokens_player_invites` ADD `response` ENUM('accepted','declined') NULL DEFAULT NULL AFTER `user_id`;

ALTER TABLE `trv_users` ADD `phone` VARCHAR(20) NULL DEFAULT NULL AFTER `email_verified`;
ALTER TABLE `trv_users` ADD usertoken VARCHAR(32) NOT NULL AFTER password, 
ADD fortgotpassword_duration DATETIME NOT NULL AFTER usertoken;

ALTER TABLE `trv_teams` ADD `home_venue_id` INT(11) UNSIGNED NOT NULL AFTER `name`;
ALTER TABLE `trv_teams` ADD `current_game_id` INT(11) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `trv_system_config` ADD `description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `value`;

ALTER TABLE `trv_auth_groups` 
ADD `redirect_state` VARCHAR(255) NOT NULL AFTER `desc`, 
ADD `default_group` BOOLEAN NOT NULL DEFAULT FALSE AFTER `redirect_state`, 
ADD `super_admin_group` BOOLEAN NOT NULL DEFAULT FALSE AFTER `default_group`;

ALTER TABLE `trv_venues` ADD `created_by_user_type` ENUM('h','o') NOT NULL DEFAULT 'o' COMMENT 'h=host,o=owner' ;

-- //////// DELETE Bad Data ////////

DELETE FROM `trv_user_additional_info` WHERE `answer` = 'unanswered' OR `answer` = '';
DELETE FROM `trv_tokens_auth` WHERE `expires` < NOW();

-- //////// INDEXES ////////


ALTER TABLE `trv_games` ADD INDEX(`venue_id`);
ALTER TABLE `trv_games` ADD INDEX(`host_user_id`);

ALTER TABLE `trv_game_rounds` ADD INDEX(`game_id`);

ALTER TABLE `trv_game_round_questions` ADD INDEX(`game_id`);
ALTER TABLE `trv_game_round_questions` ADD INDEX(`round_id`);

ALTER TABLE `trv_logs_login_location` ADD INDEX(`user_id`);

ALTER TABLE `trv_team_members` ADD INDEX(`team_id`);
ALTER TABLE `trv_team_members` ADD INDEX(`user_id`);

ALTER TABLE `trv_user_additional_info` ADD INDEX(`user_id`);

ALTER TABLE `trv_auth_fields` ADD INDEX(`initialized`);
ALTER TABLE `trv_auth_fields` ADD INDEX(`disabled`);

ALTER TABLE `trv_venue_roles` ADD INDEX(`user_id`);
ALTER TABLE `trv_venue_roles` ADD INDEX(`venue_id`);

-- //////// FORCE UPDATE Data ////////

TRUNCATE `trv_auth_groups`;
INSERT INTO `trv_auth_groups` (`id`, `group`, `slug`, `desc`, `redirect_state`, `default_group`, `super_admin_group`, `disabled`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'Guests', 'guests', 'Non authenticated users.', 'app.public.landing', 0, 0, 0, '2016-01-13 16:00:27', 1, '2016-01-28 12:54:54', 1),
(2, 'Super Admin', 'super-admin', 'Full administrative privileges. Access to everything.', 'app.admin.dashboard', 0, 1, 0, '2016-01-13 16:00:27', 1, '2016-04-12 12:08:55', 1),
(3, 'Member', 'member', 'Authenticated user.', 'app.member.dashboard', 1, 0, 0, '2016-01-25 18:42:00', 1, '2016-04-12 12:09:04', 1),
(4, 'Game Admin', 'game-admin', 'Has the ability to host and edit games.', 'app.member.dashboard', 0, 0, 0, '2016-02-08 20:09:27', 1, '2016-04-12 21:09:13', 1),
(5, 'Venue Admin', 'venue-admin', 'User who signed up with the venue member form.', 'app.member.dashboard', 0, 0, 0, '2016-03-01 21:08:48', 1, '2016-04-12 21:09:17', 1);

TRUNCATE `trv_auth_lookup_group_role`;
INSERT INTO `trv_auth_lookup_group_role` (`id`, `auth_group_id`, `auth_role_id`, `created`, `created_user_id`) VALUES
(1, 1, 1, '2016-01-13 16:03:51', 1),
(2, 2, 2, '2016-01-13 16:03:57', 1),
(3, 2, 3, '2016-01-13 16:04:07', 1),
(4, 2, 4, '2016-02-08 20:09:43', 1),
(5, 2, 5, '2016-01-25 20:30:01', 1),
(6, 3, 3, '2016-02-09 00:22:08', 1),
(7, 4, 4, '2016-02-09 00:22:10', 1),
(8, 5, 5, '2016-03-23 08:12:35', 1);

TRUNCATE `trv_auth_roles`;
INSERT INTO `trv_auth_roles` (`id`, `role`, `slug`, `desc`, `disabled`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'Public', 'public', 'Accessible to non authenticated users.', 0, '2016-01-13 15:52:24', 1, '2016-01-26 12:06:01', 1),
(2, 'Administrative', 'admin', 'Accessible to system administrators only. These are the people who can access the admin area where the system is configured and sensitive information can be accessed or modified.', 0, '2016-01-13 15:58:48', 1, '2016-05-27 17:57:42', 1),
(3, 'Registered User', 'registered-user', 'Accessible to registered users only. Users must login to access this content.', 0, '2016-01-13 15:58:48', 1, '2016-01-26 12:06:30', 1),
(4, 'Game Host', 'game-host', 'Trivia game host.', 0, '2016-02-08 20:09:43', 1, '2016-03-23 07:55:44', 1),
(5, 'Venue Editor', 'venue-editor', 'Has the ability to edit venues associated with this user.', 0, '2016-03-23 07:48:39', 1, '2016-03-23 07:55:48', 1);

TRUNCATE `trv_email_templates`;
INSERT INTO `trv_email_templates` (`id`, `identifier`, `from_email`, `from_name`, `reply_email`, `reply_name`, `subject`, `body_html`, `body_plain`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'SIGNUP_INVITE_PLAYER', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'A friend has invited you to join !@0@!', '<p>A player at <a href=''!@1@!'' target=''_blank''>!@0@!</a> would like you to join in on the fun!</p><p>Click the link above or paste the following URL into your browser to signup:</p><p>!@1@!</p>', 'A player at !@0@! would like you to join in on the fun!\\n\\rPaste the following URL into your browser to signup:\\n\\r!@1@!', '2016-04-25 16:59:22', 1, '0000-00-00 00:00:00', 1),
(2, 'SIGNUP_TEAM_INVITE', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'Team Up! You''ve been invited to a Trivia Team.', '<p>A player at <a href=''!@1@!'' target=''_blank''>!@0@!</a> would like you to join their team!</p><p>Click the link above or paste the following URL into your browser sign up and join the team ''!@2@!'':</p><p>!@1@!</p>', 'A player at !@0@! would like you to join their team! Paste the following URL into your browser to signup and join the team ''!@2@!'': !@1@!', '2016-04-28 23:45:18', 1, '0000-00-00 00:00:00', 1),
(3, 'TEAM_INVITE_USER', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'Team Up! You''ve been invited to a Trivia Team.', '<p>A player at <a href=''!@1@!'' target=''_blank''>!@0@!</a> would like you to join their team!</p><p>Click the link above or paste the following URL into your browser to join team ''!@2@!'':</p><p>!@1@!</p>', 'A player at !@0@! would like you to join their team! Paste the following URL into your browser to join team ''!@2@!'': !@1@!', '2016-04-25 16:57:10', 1, '0000-00-00 00:00:00', 1),
(4, 'SYSTEM_EMAIL_SERVICE_TEST_EMAIL', 'dev@triviajoint.com', 'Trivia Joint Dev Team', 'noreply@triviajoint.com', 'Do Not Reply', 'Test Email From The Dev Team', '<p>This is a <strong>test email</strong> saved in the email templates database table for <a href="!@1@!" target="_blank">!@0@!</a>.</p><p>!@2@!</p>', 'This is a test email saved in the email templates database table for !@0@! - !@1@! !@2@!', '2016-05-16 18:41:55', 1, '0000-00-00 00:00:00', 1),
(5, 'NEW_USER_SIGNED_UP_ADDED_TO_TEAM', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'New Team Member at !@0@!', '<p>Congratulations!</p><p>You are now a player at <a href=''!@1@!'' target=''_blank''>!@0@!</a>!</p><p>You have also been added to team <strong>!@3@!</strong>.</p><p><a href=''!@2@!'' target=''_blank''>Login Here</a> to check in to a joint and join a game!</p>', ' Congratulations! You are now a player at !@0@!! You have also been added to team - !@3@!. Login to join a game! !@2@!', '2016-05-23 03:28:25', 1, '0000-00-00 00:00:00', 1),
(6, 'NEW_USER_SIGNED_UP', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'New Player at !@0@!', '<p>Congratulations!</p><p>You are now a player at <a href=''!@1@!'' target=''_blank''>!@0@!</a>!</p><p><a href=''!@2@!'' target=''_blank''>Login Here</a> to join the fun!</p>', 'Congratulations! You are now a player at !@0@!. Login to join the fun! !@2@!', '2016-05-23 03:28:25', 1, '0000-00-00 00:00:00', 1);

TRUNCATE `trv_system_config`;
INSERT INTO `trv_system_config` (`id`, `name`, `value`, `description`, `created`, `created_user_id`, `last_updated`, `last_updated_by`, `disabled`, `indestructible`, `locked`) VALUES
(1, 'AUTH_COOKIE_TIMEOUT_HOURS', '72', '', '2016-01-21 23:23:45', 1, '2016-01-24 19:15:01', 1, 0, 1, 1),
(2, 'HOT_SALSA_PLAYER_REGISTRATION_URL', 'https://svcdev.hotsalsainteractive.com/user/registerAPI', '', '2016-04-06 04:20:57', 1, '2016-04-21 14:44:56', 1, 0, 1, 0),
(3, 'HOT_SALSA_PLAYER_REGISTRATION_ENABLED', 'true', '', '2016-04-06 04:20:57', 1, '2016-04-21 14:45:54', 1, 0, 1, 0),
(4, 'HOT_SALSA_APP_VERSION', '2', '', '2016-04-06 04:21:44', 1, '2016-04-06 04:25:47', 1, 0, 1, 0),
(5, 'HOT_SALSA_URL_CODE', 'gBa4U7UYHX4Q3amRXnxGvH1rKAZsHXTXz31tbWsSTwIXG', '', '2016-04-06 04:23:10', 1, '2016-04-06 04:25:59', 1, 0, 1, 0),
(6, 'HOT_SALSA_PACKAGE_CODE', 'com.hotsalsainteractive.browserTrivia', '', '2016-04-06 04:23:10', 1, '2016-04-06 04:26:04', 1, 0, 1, 0),
(7, 'HOT_SALSA_AUTH_KEY', 'W5fLHehgfHUhmI7x7clD8x1Ki1Gf8oY4uePbs7rHOmZb4', '', '2016-04-06 04:26:53', 1, '2016-04-06 04:26:53', 1, 0, 1, 0),
(8, 'HOT_SALSA_OS', '4', '', '2016-04-06 04:26:53', 1, '2016-04-06 04:27:03', 1, 0, 1, 0),
(9, 'SMTP_SERVER_HOST', 'mail.triviaculture.com', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-22 17:21:48', 1, 0, 1, 0),
(10, 'SMTP_SERVER_PORT', '587', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-22 18:46:16', 1, 0, 1, 0),
(11, 'SMTP_SERVER_USERNAME', 'communications@triviajoint.com', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-22 17:22:00', 1, 0, 1, 0),
(12, 'SMTP_SERVER_PASSWORD', 'Tr1v1@#1', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-22 17:22:06', 1, 0, 1, 0),
(13, 'SMTP_SMTP_DEBUG', '0', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-22 17:22:14', 1, 0, 1, 0),
(14, 'SMTP_SECURE', 'tls', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-22 18:46:21', 1, 0, 1, 0),
(15, 'SMTP_AUTH', 'true', 'PHPMailer: ', '2016-04-08 16:34:32', 1, '2016-04-19 09:50:11', 1, 0, 1, 0),
(16, 'SMTP_DEBUGOUTPUT', 'error_log', 'PHPMailer: ', '2016-04-19 09:43:41', 1, '2016-04-19 11:37:09', 1, 0, 1, 1),
(17, 'WEBSITE_TITLE', 'TriviaJoint.com', 'The name of the website used for display purposes.', '2016-05-12 11:54:24', 1, '2016-05-12 11:54:24', 1, 0, 1, 0),
(18, 'WEBSITE_URL', 'https://app-dot-triviajoint-qa2.appspot.com/', 'Website URL with trailing slash.', '2016-05-12 11:54:24', 1, '2016-05-12 11:54:24', 1, 0, 1, 0),
(19, 'PASSWORD_RESET_EMAIL_FROM', 'communications@triviajoint.com', '', '2016-04-15 06:30:57', 1, '2016-04-15 15:15:48', 1, 0, 1, 1),
(20, 'PASSWORD_RESET_EMAIL_SUBJECT', 'Reset Password Link', '', '2016-04-15 06:30:57', 1, '2016-05-17 23:01:27', 1, 0, 1, 1),
(21, 'PASSWORD_RESET_EMAIL_BODY', '<table><tr><td>Dear, !@FIRSTNAME@! !@LASTNAME@!</td></tr><tr><td>Click on the below link to reset password</td></tr><tr><td><a href="!@WEBSITEURL@!/reset_password/!@LINKID@!">!@WEBSITEURL@!/reset_password/!@LINKID@!</a></td></tr></table>', '', '2016-04-15 07:00:32', 1, '2016-04-15 14:20:48', 1, 0, 1, 1),
(22, 'PASSWORD_RESET_ROOT_URL', 'https://app-dot-triviajoint-qa2.appspot.com', '', '2016-04-15 15:02:12', 1, '2016-05-17 23:00:56', 1, 0, 1, 1),
(23, 'PASSWORD_RESET_SUCCESS_EMAIL_SUBJECT', 'Your Password Has Been Changed', '', '2016-04-15 06:30:57', 1, '2016-05-17 23:01:06', 1, 0, 1, 1),
(24, 'PASSWORD_RESET_SUCCESS_EMAIL_BODY', '<table><tr><td>Dear, !@FIRSTNAME@! !@LASTNAME@!</td></tr><tr><td>You have successfully changed your password.</td></tr><tr><td><a href="!@WEBSITEURL@!/login">Click here to Login</a></td></tr></table>', '', '2016-04-15 07:00:32', 1, '2016-04-15 14:20:48', 1, 0, 1, 1),
(25, 'PASSWORD_RESET_FAILED_EMAIL_SUBJECT', 'Your Password Change Has Failed', '', '2016-04-15 06:30:57', 1, '2016-05-17 23:01:17', 1, 0, 1, 1),
(26, 'PASSWORD_RESET_FAILED_EMAIL_BODY', '<table><tr><td>Dear, !@FIRSTNAME@! !@LASTNAME@!</td></tr><tr><td>Your last attempts to change password is failed.</td></tr><tr><td><a href="!@WEBSITEURL@!/login">Click here to Login</a></td></tr></table>', '', '2016-04-15 07:00:32', 1, '2016-04-15 14:20:48', 1, 0, 1, 1),
(27, 'PASSWORD_RESET_EMAIL_FROM_NAME', 'Triviajoint', '', '2016-04-18 08:10:05', 1, '2016-04-18 08:10:18', 1, 0, 1, 1),
(28, 'HOT_SALSA_VENUE_REGISTRATION_ENABLED', 'true', '', '2016-04-05 22:50:57', 1, '2016-04-27 09:41:38', 1, 0, 1, 0),
(29, 'HOT_SALSA_VENUE_REGISTRATION_URL', 'https://svcdev.hotsalsainteractive.com/location/registerLocation', '', '2016-04-05 22:50:57', 1, '2016-04-27 14:59:06', 1, 0, 1, 0);

