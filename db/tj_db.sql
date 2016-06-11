-- phpMyAdmin SQL Dump
-- version 4.5.0.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2016 at 07:20 PM
-- Server version: 5.6.21
-- PHP Version: 5.5.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tj_prod_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_auth_fields`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_auth_fields` (
  `id` int(11) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `type` enum('state','element') NOT NULL,
  `desc` text,
  `initialized` datetime DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_auth_fields`:
--

--
-- Dumping data for table `trv_auth_fields`
--

INSERT INTO `trv_auth_fields` (`id`, `identifier`, `type`, `desc`, `initialized`, `disabled`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'app.members', 'state', 'Logged in member state (for authorized users of this application).', '2016-01-28 21:58:05', 0, '2016-01-23 04:13:03', 1, '2016-01-28 20:58:05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trv_auth_groups`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_auth_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `desc` text,
  `redirect_state` varchar(255) NOT NULL,
  `default_group` tinyint(1) NOT NULL DEFAULT '0',
  `super_admin_group` tinyint(1) NOT NULL DEFAULT '0',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_auth_groups`:
--

--
-- Dumping data for table `trv_auth_groups`
--

INSERT INTO `trv_auth_groups` (`id`, `group`, `slug`, `desc`, `redirect_state`, `default_group`, `super_admin_group`, `disabled`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'Guests', 'guests', 'Non authenticated users.', 'app.public.landing', 0, 0, 0, '2016-01-13 21:00:27', 1, '2016-01-28 17:54:54', 1),
(2, 'Super Admin', 'super-admin', 'Full administrative privileges. Access to everything.', 'app.admin.dashboard', 0, 1, 0, '2016-01-13 21:00:27', 1, '2016-04-12 16:08:55', 1),
(3, 'Member', 'member', 'Authenticated user.', 'app.member.dashboard', 1, 0, 0, '2016-01-25 23:42:00', 1, '2016-04-12 16:09:04', 1),
(4, 'Game Admin', 'game-admin', 'Has the ability to host and edit games.', 'app.member.dashboard', 0, 0, 0, '2016-02-09 01:09:27', 1, '2016-04-13 01:09:13', 1),
(5, 'Venue Admin', 'venue-admin', 'User who signed up with the venue member form.', 'app.member.dashboard', 0, 0, 0, '2016-03-02 02:08:48', 1, '2016-04-13 01:09:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trv_auth_lookup_group_role`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_auth_lookup_group_role` (
  `id` int(11) UNSIGNED NOT NULL,
  `auth_group_id` int(11) UNSIGNED NOT NULL,
  `auth_role_id` int(11) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_auth_lookup_group_role`:
--

--
-- Dumping data for table `trv_auth_lookup_group_role`
--

INSERT INTO `trv_auth_lookup_group_role` (`id`, `auth_group_id`, `auth_role_id`, `created`, `created_user_id`) VALUES
(1, 1, 1, '2016-01-13 21:03:51', 1),
(2, 2, 2, '2016-01-13 21:03:57', 1),
(3, 2, 3, '2016-01-13 21:04:07', 1),
(4, 2, 4, '2016-02-09 01:09:43', 1),
(5, 2, 5, '2016-01-26 01:30:01', 1),
(6, 3, 3, '2016-02-09 05:22:08', 1),
(7, 4, 4, '2016-02-09 05:22:10', 1),
(8, 5, 5, '2016-03-23 12:12:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trv_auth_lookup_role_field`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_auth_lookup_role_field` (
  `id` int(11) UNSIGNED NOT NULL,
  `auth_role_id` int(11) UNSIGNED NOT NULL,
  `auth_field_id` int(11) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_auth_lookup_role_field`:
--

--
-- Dumping data for table `trv_auth_lookup_role_field`
--

INSERT INTO `trv_auth_lookup_role_field` (`id`, `auth_role_id`, `auth_field_id`, `created`, `created_user_id`) VALUES
(1, 3, 1, '2016-01-23 04:18:42', 1),
(6, 2, 1, '2016-01-28 20:57:49', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trv_auth_lookup_user_group`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_auth_lookup_user_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `auth_group_id` int(11) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_auth_lookup_user_group`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_auth_roles`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_auth_roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `desc` text,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_auth_roles`:
--

--
-- Dumping data for table `trv_auth_roles`
--

INSERT INTO `trv_auth_roles` (`id`, `role`, `slug`, `desc`, `disabled`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'Public', 'public', 'Accessible to non authenticated users.', 0, '2016-01-13 20:52:24', 1, '2016-01-26 17:06:01', 1),
(2, 'Administrative', 'admin', 'Accessible to system administrators only. These are the people who can access the admin area where the system is configured and sensitive information can be accessed or modified.', 0, '2016-01-13 20:58:48', 1, '2016-05-27 21:57:42', 1),
(3, 'Registered User', 'registered-user', 'Accessible to registered users only. Users must login to access this content.', 0, '2016-01-13 20:58:48', 1, '2016-01-26 17:06:30', 1),
(4, 'Game Host', 'game-host', 'Trivia game host.', 0, '2016-02-09 01:09:43', 1, '2016-03-23 11:55:44', 1),
(5, 'Venue Editor', 'venue-editor', 'Has the ability to edit venues associated with this user.', 0, '2016-03-23 11:48:39', 1, '2016-03-23 11:55:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trv_email_templates`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

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

--
-- RELATIONS FOR TABLE `trv_email_templates`:
--

--
-- Dumping data for table `trv_email_templates`
--

INSERT INTO `trv_email_templates` (`id`, `identifier`, `from_email`, `from_name`, `reply_email`, `reply_name`, `subject`, `body_html`, `body_plain`, `created`, `created_user_id`, `last_updated`, `last_updated_by`) VALUES
(1, 'SIGNUP_INVITE_PLAYER', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'A friend has invited you to join !@0@!', '<p>A player at <a href=''!@1@!'' target=''_blank''>!@0@!</a> would like you to join in on the fun!</p><p>Click the link above or paste the following URL into your browser to signup:</p><p>!@1@!</p>', 'A player at !@0@! would like you to join in on the fun!\\n\\rPaste the following URL into your browser to signup:\\n\\r!@1@!', '2016-04-25 20:59:22', 1, '0000-00-00 00:00:00', 1),
(2, 'SIGNUP_TEAM_INVITE', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'Team Up! You''ve been invited to a Trivia Team.', '<p>A player at <a href=''!@1@!'' target=''_blank''>!@0@!</a> would like you to join their team!</p><p>Click the link above or paste the following URL into your browser sign up and join the team ''!@2@!'':</p><p>!@1@!</p>', 'A player at !@0@! would like you to join their team! Paste the following URL into your browser to signup and join the team ''!@2@!'': !@1@!', '2016-04-29 03:45:18', 1, '0000-00-00 00:00:00', 1),
(3, 'TEAM_INVITE_USER', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'Team Up! You''ve been invited to a Trivia Team.', '<p>A player at <a href=''!@1@!'' target=''_blank''>!@0@!</a> would like you to join their team!</p><p>Click the link above or paste the following URL into your browser to join team ''!@2@!'':</p><p>!@1@!</p>', 'A player at !@0@! would like you to join their team! Paste the following URL into your browser to join team ''!@2@!'': !@1@!', '2016-04-25 20:57:10', 1, '0000-00-00 00:00:00', 1),
(4, 'SYSTEM_EMAIL_SERVICE_TEST_EMAIL', 'dev@triviajoint.com', 'Trivia Joint Dev Team', 'noreply@triviajoint.com', 'Do Not Reply', 'Test Email From The Dev Team', '<p>This is a <strong>test email</strong> saved in the email templates database table for <a href="!@1@!" target="_blank">!@0@!</a>.</p><p>!@2@!</p>', 'This is a test email saved in the email templates database table for !@0@! - !@1@! !@2@!', '2016-05-16 22:41:55', 1, '0000-00-00 00:00:00', 1),
(5, 'NEW_USER_SIGNED_UP_ADDED_TO_TEAM', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'New Team Member at !@0@!', '<p>Congratulations!</p><p>You are now a player at <a href=''!@1@!'' target=''_blank''>!@0@!</a>!</p><p>You have also been added to team <strong>!@3@!</strong>.</p><p><a href=''!@2@!'' target=''_blank''>Login Here</a> to check in to a joint and join a game!</p>', ' Congratulations! You are now a player at !@0@!! You have also been added to team - !@3@!. Login to join a game! !@2@!', '2016-05-23 07:28:25', 1, '0000-00-00 00:00:00', 1),
(6, 'NEW_USER_SIGNED_UP', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'communications@triviajoint.com', 'TriviaJoint.com Communications', 'New Player at !@0@!', '<p>Congratulations!</p><p>You are now a player at <a href=''!@1@!'' target=''_blank''>!@0@!</a>!</p><p><a href=''!@2@!'' target=''_blank''>Login Here</a> to join the fun!</p>', 'Congratulations! You are now a player at !@0@!. Login to join the fun! !@2@!', '2016-05-23 07:28:25', 1, '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trv_games`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_games` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `venue_id` int(11) UNSIGNED NOT NULL,
  `host_user_id` int(11) UNSIGNED NOT NULL,
  `scheduled` datetime NOT NULL,
  `game_started` datetime DEFAULT NULL,
  `game_ended` datetime DEFAULT NULL,
  `max_points` decimal(7,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_games`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_game_rounds`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_game_rounds` (
  `id` int(11) UNSIGNED NOT NULL,
  `order` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `max_points` decimal(7,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `default_question_points` decimal(7,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_game_rounds`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_game_round_questions`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_game_round_questions` (
  `id` int(11) UNSIGNED NOT NULL,
  `order` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `question` varchar(255) NOT NULL DEFAULT '',
  `game_id` int(11) UNSIGNED NOT NULL,
  `round_id` int(11) UNSIGNED NOT NULL,
  `max_points` float(7,2) NOT NULL DEFAULT '0.00',
  `wager` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_game_round_questions`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_game_score_questions`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_game_score_questions` (
  `id` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `round_id` int(11) UNSIGNED NOT NULL,
  `question_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `wager` float(7,2) DEFAULT NULL,
  `answer` varchar(225) NOT NULL DEFAULT '',
  `score` float(7,2) NOT NULL DEFAULT '0.00',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_game_score_questions`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_game_score_rounds`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_game_score_rounds` (
  `id` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `round_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `round_rank` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `trv_game_score_rounds`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_game_score_teams`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_game_score_teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `score` decimal(7,2) NOT NULL,
  `game_rank` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `game_winner` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `trv_game_score_teams`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_hosts`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_hosts` (
  `id` int(11) NOT NULL,
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
  `last_updated_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_hosts`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_hosts_trivia_nights`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_hosts_trivia_nights` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `trivia_day` varchar(255) NOT NULL,
  `trivia_time` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_user_id` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_hosts_trivia_nights`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_hosts_venues`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_hosts_venues` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_user_id` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) NOT NULL,
  `created_by_user_type` enum('h','o') NOT NULL DEFAULT 'o' COMMENT 'h=host,o=owner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_hosts_venues`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_logs_game_checkins`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_logs_game_checkins` (
  `id` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_logs_game_checkins`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_logs_hot_salsa_signup`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_logs_hot_salsa_signup` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `salsa_call_status` varchar(255) NOT NULL DEFAULT '',
  `salsa_user_id` varchar(255) DEFAULT NULL,
  `salsa_user_data` text,
  `salsa_error_message` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_logs_hot_salsa_signup`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_logs_hot_salsa_venue_signup`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_logs_hot_salsa_venue_signup` (
  `id` int(11) UNSIGNED NOT NULL,
  `venue_id` int(11) UNSIGNED NOT NULL,
  `salsa_call_status` varchar(255) NOT NULL DEFAULT '',
  `salsa_location_id` varchar(255) DEFAULT NULL,
  `salsa_location_data` text,
  `salsa_error_message` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_logs_hot_salsa_venue_signup`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_logs_login_location`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_logs_login_location` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_logs_login_location`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_system_config`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_system_config` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` longtext,
  `description` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `indestructible` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_system_config`:
--

--
-- Dumping data for table `trv_system_config`
--

INSERT INTO `trv_system_config` (`id`, `name`, `value`, `description`, `created`, `created_user_id`, `last_updated`, `last_updated_by`, `disabled`, `indestructible`, `locked`) VALUES
(1, 'AUTH_COOKIE_TIMEOUT_HOURS', '72', '', '2016-01-22 04:23:45', 1, '2016-01-25 00:15:01', 1, 0, 1, 1),
(2, 'HOT_SALSA_PLAYER_REGISTRATION_URL', 'https://svcdev.hotsalsainteractive.com/user/registerAPI', '', '2016-04-06 08:20:57', 1, '2016-04-21 18:44:56', 1, 0, 1, 0),
(3, 'HOT_SALSA_PLAYER_REGISTRATION_ENABLED', 'true', '', '2016-04-06 08:20:57', 1, '2016-04-21 18:45:54', 1, 0, 1, 0),
(4, 'HOT_SALSA_APP_VERSION', '2', '', '2016-04-06 08:21:44', 1, '2016-04-06 08:25:47', 1, 0, 1, 0),
(5, 'HOT_SALSA_URL_CODE', 'gBa4U7UYHX4Q3amRXnxGvH1rKAZsHXTXz31tbWsSTwIXG', '', '2016-04-06 08:23:10', 1, '2016-04-06 08:25:59', 1, 0, 1, 0),
(6, 'HOT_SALSA_PACKAGE_CODE', 'com.hotsalsainteractive.browserTrivia', '', '2016-04-06 08:23:10', 1, '2016-04-06 08:26:04', 1, 0, 1, 0),
(7, 'HOT_SALSA_AUTH_KEY', 'W5fLHehgfHUhmI7x7clD8x1Ki1Gf8oY4uePbs7rHOmZb4', '', '2016-04-06 08:26:53', 1, '2016-04-06 08:26:53', 1, 0, 1, 0),
(8, 'HOT_SALSA_OS', '4', '', '2016-04-06 08:26:53', 1, '2016-04-06 08:27:03', 1, 0, 1, 0),
(9, 'SMTP_SERVER_HOST', 'mail.triviaculture.com', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-22 21:21:48', 1, 0, 1, 0),
(10, 'SMTP_SERVER_PORT', '587', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-22 22:46:16', 1, 0, 1, 0),
(11, 'SMTP_SERVER_USERNAME', 'communications@triviajoint.com', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-22 21:22:00', 1, 0, 1, 0),
(12, 'SMTP_SERVER_PASSWORD', 'Tr1v1@#1', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-22 21:22:06', 1, 0, 1, 0),
(13, 'SMTP_SMTP_DEBUG', '0', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-22 21:22:14', 1, 0, 1, 0),
(14, 'SMTP_SECURE', 'tls', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-22 22:46:21', 1, 0, 1, 0),
(15, 'SMTP_AUTH', 'true', 'PHPMailer: ', '2016-04-08 20:34:32', 1, '2016-04-19 13:50:11', 1, 0, 1, 0),
(16, 'SMTP_DEBUGOUTPUT', 'error_log', 'PHPMailer: ', '2016-04-19 13:43:41', 1, '2016-04-19 15:37:09', 1, 0, 1, 1),
(17, 'WEBSITE_TITLE', 'TriviaJoint.com', 'The name of the website used for display purposes.', '2016-05-12 15:54:24', 1, '2016-05-12 15:54:24', 1, 0, 1, 0),
(18, 'WEBSITE_URL', 'https://app-dot-triviajoint-qa2.appspot.com/', 'Website URL with trailing slash.', '2016-05-12 15:54:24', 1, '2016-05-12 15:54:24', 1, 0, 1, 0),
(19, 'PASSWORD_RESET_EMAIL_FROM', 'communications@triviajoint.com', '', '2016-04-15 10:30:57', 1, '2016-04-15 19:15:48', 1, 0, 1, 1),
(20, 'PASSWORD_RESET_EMAIL_SUBJECT', 'Reset Password Link', '', '2016-04-15 10:30:57', 1, '2016-05-18 03:01:27', 1, 0, 1, 1),
(21, 'PASSWORD_RESET_EMAIL_BODY', '<table><tr><td>Dear, !@FIRSTNAME@! !@LASTNAME@!</td></tr><tr><td>Click on the below link to reset password</td></tr><tr><td><a href="!@WEBSITEURL@!/reset_password/!@LINKID@!">!@WEBSITEURL@!/reset_password/!@LINKID@!</a></td></tr></table>', '', '2016-04-15 11:00:32', 1, '2016-04-15 18:20:48', 1, 0, 1, 1),
(22, 'PASSWORD_RESET_ROOT_URL', 'https://app-dot-triviajoint-qa2.appspot.com', '', '2016-04-15 19:02:12', 1, '2016-05-18 03:00:56', 1, 0, 1, 1),
(23, 'PASSWORD_RESET_SUCCESS_EMAIL_SUBJECT', 'Your Password Has Been Changed', '', '2016-04-15 10:30:57', 1, '2016-05-18 03:01:06', 1, 0, 1, 1),
(24, 'PASSWORD_RESET_SUCCESS_EMAIL_BODY', '<table><tr><td>Dear, !@FIRSTNAME@! !@LASTNAME@!</td></tr><tr><td>You have successfully changed your password.</td></tr><tr><td><a href="!@WEBSITEURL@!/login">Click here to Login</a></td></tr></table>', '', '2016-04-15 11:00:32', 1, '2016-04-15 18:20:48', 1, 0, 1, 1),
(25, 'PASSWORD_RESET_FAILED_EMAIL_SUBJECT', 'Your Password Change Has Failed', '', '2016-04-15 10:30:57', 1, '2016-05-18 03:01:17', 1, 0, 1, 1),
(26, 'PASSWORD_RESET_FAILED_EMAIL_BODY', '<table><tr><td>Dear, !@FIRSTNAME@! !@LASTNAME@!</td></tr><tr><td>Your last attempts to change password is failed.</td></tr><tr><td><a href="!@WEBSITEURL@!/login">Click here to Login</a></td></tr></table>', '', '2016-04-15 11:00:32', 1, '2016-04-15 18:20:48', 1, 0, 1, 1),
(27, 'PASSWORD_RESET_EMAIL_FROM_NAME', 'Triviajoint', '', '2016-04-18 12:10:05', 1, '2016-04-18 12:10:18', 1, 0, 1, 1),
(28, 'HOT_SALSA_VENUE_REGISTRATION_ENABLED', 'true', '', '2016-04-06 02:50:57', 1, '2016-04-27 13:41:38', 1, 0, 1, 0),
(29, 'HOT_SALSA_VENUE_REGISTRATION_URL', 'https://svcdev.hotsalsainteractive.com/location/registerLocation', '', '2016-04-06 02:50:57', 1, '2016-04-27 18:59:06', 1, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `trv_teams`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `home_venue_id` int(11) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL,
  `current_game_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_teams`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_team_members`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_team_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `added_by` int(11) UNSIGNED NOT NULL,
  `joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_team_members`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_tokens_auth`
--
-- Creation: Jun 03, 2016 at 05:13 PM
--

CREATE TABLE `trv_tokens_auth` (
  `id` int(11) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires` datetime NOT NULL,
  `ip_address` varchar(25) DEFAULT NULL,
  `user_agent` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_tokens_auth`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_tokens_player_invites`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_tokens_player_invites` (
  `id` int(11) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `response` enum('accepted','declined') DEFAULT NULL,
  `name_first` varchar(100) DEFAULT NULL,
  `name_last` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `expires` datetime NOT NULL,
  `last_visited` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_tokens_player_invites`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_users`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name_first` varchar(100) NOT NULL,
  `name_last` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified` datetime DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `accepted_terms` tinyint(1) NOT NULL DEFAULT '0',
  `facebook_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL,
  `disabled` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_users`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_user_additional_info`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_user_additional_info` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_user_additional_info`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_venues`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_venues` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `address_b` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(2) NOT NULL DEFAULT '',
  `zip` varchar(5) NOT NULL DEFAULT '',
  `phone_extension` varchar(5) NOT NULL,
  `phone` varchar(17) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `facebook_url` varchar(255) NOT NULL DEFAULT '',
  `logo` longblob,
  `referral` varchar(1000) DEFAULT NULL,
  `salsa_location_id` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL,
  `disabled` timestamp NULL DEFAULT NULL,
  `created_by_user_type` enum('h','o') NOT NULL DEFAULT 'o' COMMENT 'h=host,o=owner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_venues`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_venues_trivia_schedules`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_venues_trivia_schedules` (
  `id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `trivia_day` varchar(255) NOT NULL,
  `trivia_time` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) UNSIGNED NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_updated_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_venues_trivia_schedules`:
--

-- --------------------------------------------------------

--
-- Table structure for table `trv_venue_roles`
--
-- Creation: Jun 03, 2016 at 05:14 PM
--

CREATE TABLE `trv_venue_roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `venue_id` int(11) UNSIGNED NOT NULL,
  `role` enum('owner','manager','employee','guest') NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `trv_venue_roles`:
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trv_auth_fields`
--
ALTER TABLE `trv_auth_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `identifier_2` (`identifier`),
  ADD KEY `type` (`type`),
  ADD KEY `initialized` (`initialized`),
  ADD KEY `disabled` (`disabled`);

--
-- Indexes for table `trv_auth_groups`
--
ALTER TABLE `trv_auth_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group` (`group`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `group_2` (`group`),
  ADD KEY `slug_2` (`slug`);

--
-- Indexes for table `trv_auth_lookup_group_role`
--
ALTER TABLE `trv_auth_lookup_group_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_group_id` (`auth_group_id`),
  ADD KEY `auth_role_id` (`auth_role_id`),
  ADD KEY `auth_group_id_2` (`auth_group_id`),
  ADD KEY `auth_role_id_2` (`auth_role_id`);

--
-- Indexes for table `trv_auth_lookup_role_field`
--
ALTER TABLE `trv_auth_lookup_role_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_role_id` (`auth_role_id`),
  ADD KEY `auth_element_id` (`auth_field_id`),
  ADD KEY `auth_role_id_2` (`auth_role_id`),
  ADD KEY `auth_field_id` (`auth_field_id`);

--
-- Indexes for table `trv_auth_lookup_user_group`
--
ALTER TABLE `trv_auth_lookup_user_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `auth_group_id` (`auth_group_id`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `auth_group_id_2` (`auth_group_id`);

--
-- Indexes for table `trv_auth_roles`
--
ALTER TABLE `trv_auth_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role` (`role`),
  ADD UNIQUE KEY `slug_2` (`slug`),
  ADD KEY `role_2` (`role`),
  ADD KEY `slug` (`slug`);

--
-- Indexes for table `trv_email_templates`
--
ALTER TABLE `trv_email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `trv_games`
--
ALTER TABLE `trv_games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `host_user_id` (`host_user_id`);

--
-- Indexes for table `trv_game_rounds`
--
ALTER TABLE `trv_game_rounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `trv_game_round_questions`
--
ALTER TABLE `trv_game_round_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `round_id` (`round_id`);

--
-- Indexes for table `trv_game_score_questions`
--
ALTER TABLE `trv_game_score_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_question_score` (`game_id`,`round_id`,`question_id`,`team_id`);

--
-- Indexes for table `trv_game_score_rounds`
--
ALTER TABLE `trv_game_score_rounds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_round_score` (`game_id`,`round_id`,`team_id`);

--
-- Indexes for table `trv_game_score_teams`
--
ALTER TABLE `trv_game_score_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_game_score` (`game_id`,`team_id`);

--
-- Indexes for table `trv_hosts`
--
ALTER TABLE `trv_hosts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trv_users_id` (`trv_users_id`);

--
-- Indexes for table `trv_hosts_trivia_nights`
--
ALTER TABLE `trv_hosts_trivia_nights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_id` (`host_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `trv_hosts_venues`
--
ALTER TABLE `trv_hosts_venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_id` (`host_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `trv_logs_game_checkins`
--
ALTER TABLE `trv_logs_game_checkins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `trv_logs_hot_salsa_signup`
--
ALTER TABLE `trv_logs_hot_salsa_signup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trv_logs_hot_salsa_venue_signup`
--
ALTER TABLE `trv_logs_hot_salsa_venue_signup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `trv_logs_login_location`
--
ALTER TABLE `trv_logs_login_location`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trv_system_config`
--
ALTER TABLE `trv_system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`),
  ADD KEY `name` (`name`),
  ADD KEY `name_2` (`name`);

--
-- Indexes for table `trv_teams`
--
ALTER TABLE `trv_teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trv_team_members`
--
ALTER TABLE `trv_team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trv_tokens_auth`
--
ALTER TABLE `trv_tokens_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`);

--
-- Indexes for table `trv_tokens_player_invites`
--
ALTER TABLE `trv_tokens_player_invites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trv_users`
--
ALTER TABLE `trv_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`),
  ADD KEY `facebook_id` (`facebook_id`);

--
-- Indexes for table `trv_user_additional_info`
--
ALTER TABLE `trv_user_additional_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trv_venues`
--
ALTER TABLE `trv_venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zip` (`zip`);

--
-- Indexes for table `trv_venues_trivia_schedules`
--
ALTER TABLE `trv_venues_trivia_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `trv_venue_roles`
--
ALTER TABLE `trv_venue_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trv_auth_fields`
--
ALTER TABLE `trv_auth_fields`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `trv_auth_groups`
--
ALTER TABLE `trv_auth_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `trv_auth_lookup_group_role`
--
ALTER TABLE `trv_auth_lookup_group_role`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `trv_auth_lookup_role_field`
--
ALTER TABLE `trv_auth_lookup_role_field`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `trv_auth_lookup_user_group`
--
ALTER TABLE `trv_auth_lookup_user_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;
--
-- AUTO_INCREMENT for table `trv_auth_roles`
--
ALTER TABLE `trv_auth_roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `trv_email_templates`
--
ALTER TABLE `trv_email_templates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `trv_games`
--
ALTER TABLE `trv_games`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `trv_game_rounds`
--
ALTER TABLE `trv_game_rounds`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `trv_game_round_questions`
--
ALTER TABLE `trv_game_round_questions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `trv_game_score_questions`
--
ALTER TABLE `trv_game_score_questions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_game_score_rounds`
--
ALTER TABLE `trv_game_score_rounds`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `trv_game_score_teams`
--
ALTER TABLE `trv_game_score_teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `trv_hosts`
--
ALTER TABLE `trv_hosts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_hosts_trivia_nights`
--
ALTER TABLE `trv_hosts_trivia_nights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_hosts_venues`
--
ALTER TABLE `trv_hosts_venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_logs_game_checkins`
--
ALTER TABLE `trv_logs_game_checkins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_logs_hot_salsa_signup`
--
ALTER TABLE `trv_logs_hot_salsa_signup`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;
--
-- AUTO_INCREMENT for table `trv_logs_hot_salsa_venue_signup`
--
ALTER TABLE `trv_logs_hot_salsa_venue_signup`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_logs_login_location`
--
ALTER TABLE `trv_logs_login_location`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=282;
--
-- AUTO_INCREMENT for table `trv_system_config`
--
ALTER TABLE `trv_system_config`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `trv_teams`
--
ALTER TABLE `trv_teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `trv_team_members`
--
ALTER TABLE `trv_team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `trv_tokens_auth`
--
ALTER TABLE `trv_tokens_auth`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;
--
-- AUTO_INCREMENT for table `trv_tokens_player_invites`
--
ALTER TABLE `trv_tokens_player_invites`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_users`
--
ALTER TABLE `trv_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;
--
-- AUTO_INCREMENT for table `trv_user_additional_info`
--
ALTER TABLE `trv_user_additional_info`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=474;
--
-- AUTO_INCREMENT for table `trv_venues`
--
ALTER TABLE `trv_venues`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `trv_venues_trivia_schedules`
--
ALTER TABLE `trv_venues_trivia_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `trv_venue_roles`
--
ALTER TABLE `trv_venue_roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
