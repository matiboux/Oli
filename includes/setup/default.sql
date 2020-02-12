/*\
|*|  ----------------------------------
|*|  --- [  Oli Default SQL file  ] ---
|*|  ----------------------------------
|*|  / Built for Oli Beta 1.8.0
|*|  
|*|  This is the default SQL file for Oli, an open source PHP Framework.
|*|  You can use this SQL template to setup a MySQL database for use with the framework.
|*|  Created and developed by Matiboux (Mathieu Guérin).
|*|  
|*|  Oli Github repository: https://github.com/matiboux/Oli/
|*|   — see more infos in the README.md file on the repository
|*|  
|*|  --- --- ---
|*|  
|*|  MIT License
|*|  
|*|  Copyright (c) 2017 Matiboux (Mathieu Guérin)
|*|  
|*|    Permission is hereby granted, free of charge, to any person obtaining a copy
|*|    of this software and associated documentation files (the "Software"), to deal
|*|    in the Software without restriction, including without limitation the rights
|*|    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
|*|    copies of the Software, and to permit persons to whom the Software is
|*|    furnished to do so, subject to the following conditions:
|*|    
|*|    The above copyright notice and this permission notice shall be included in all
|*|    copies or substantial portions of the Software.
|*|    
|*|    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
|*|    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
|*|    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
|*|    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
|*|    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
|*|    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
|*|    SOFTWARE.
|*|  
|*|  --- --- ---
|*|  
|*|  Summary:
|*|  
|*|  I. Oli General Tables
|*|    I. 1. Table `settings`
|*|    I. 2. Table `shortcut_links`
|*|    I. 3. Table `translations`
|*|  II. Oli Accounts Tables
|*|    II. 1. Table `accounts`
|*|    II. 2. Table `accounts_infos`
|*|    II. 3. Table `accounts_log_limits`
|*|    II. 4. Table `accounts_sessions`
|*|    II. 5. Table `accounts_requests`
|*|    II. 6. Table `accounts_rights`
\*/

-- phpMyAdmin SQL Dump
-- version 4.7.0-rc1
-- https://www.phpmyadmin.net/
--
-- PHP Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- *** *** *** --

-- --------------------------- --
-- [  I. Oli General Tables  ] --
-- --------------------------- --

-- I. 1. Table `settings`

	-- I. 1. A. Create the table

	CREATE TABLE `settings` (
	  `name` varchar(64) NOT NULL,
	  `value` text
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- I. 1. B. Insert the data

	INSERT INTO `settings` (`name`, `value`) VALUES
	('url', 'urwebs.it/'),
	('name', 'Your own Oli website!'),
	('description', 'Is that your website?'),
	('creation_date', '2014-11-15'),
	('owner', ''),
	('version', ''),
	('status', ''),
	('github', '');

	-- I. 1. C. Extras

	ALTER TABLE `settings`
	  ADD PRIMARY KEY (`name`);

-- I. 2. Table `shortcut_links`

	-- I. 2. A. Create the table

	CREATE TABLE `shortcut_links` (
	  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	  `url` varchar(256) COLLATE utf8_unicode_ci NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	-- III. 2. B. Insert the data

	INSERT INTO `shortcut_links` (`name`, `url`) VALUES
	('Oli', 'https://github.com/matiboux/Oli/');

	-- III. 2. C. Extras

	ALTER TABLE `shortcut_links`
	  ADD PRIMARY KEY (`name`);


-- ----------------------------- --
-- [  II. Oli Accounts Tables  ] --
-- ----------------------------- --

-- II. 1. Table `accounts`

	-- II. 1. A. Create the table

	CREATE TABLE `accounts` (
	  `uid` varchar(36) NOT NULL,
	  `username` varchar(64) DEFAULT NULL,
	  `password` varchar(128) NOT NULL,
	  `email` varchar(128) NOT NULL,
	  `birthday` date DEFAULT NULL,
	  `register_date` datetime NOT NULL,
	  `user_right` varchar(32) NOT NULL,
	  `avatar_method` varchar(64) DEFAULT NULL,
	  `avatar_filetype` varchar(32) DEFAULT NULL,
	  `admin_note` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 1. B. Insert the data

	-- [ Note: ]
	-- This is the default admin account.
	-- As an example, the user "admin" below use "admin" as its password.
	-- You should change the password as soon as possible.
	-- You can use the official Oli Login page for that.

	-- INSERT INTO `accounts` (`uid`, `username`, `password`, `email`, `birthday`, `register_date`, `user_right`, `avatar_method`, `avatar_filetype`, `admin_note`) VALUES

	-- II. 1. C. Extras

	ALTER TABLE `accounts`
	  ADD PRIMARY KEY (`uid`);

	-- ALTER TABLE `accounts`
	  -- ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_right`) REFERENCES `accounts_rights` (`user_right`) ON DELETE SET NULL ON UPDATE CASCADE;

-- II. 2. Table `accounts_infos`

	-- II. 2. A. Create the table

	CREATE TABLE `accounts_infos` (
	  `uid` varchar(36) NOT NULL,
	  `pseudonym` varchar(64) NOT NULL,
	  `nickname` varchar(64) NOT NULL,
	  `firstname` varchar(64) NOT NULL,
	  `lastname` varchar(64) NOT NULL,
	  `displayed_name` varchar(64) NOT NULL,
	  `add_pseudonym` tinyint(1) NOT NULL,
	  `gender` varchar(64) NOT NULL,
	  `sexuality` varchar(64) NOT NULL,
	  `biography` text NOT NULL,
	  `theme` varchar(64) NOT NULL,
	  `address` varchar(256) NOT NULL,
	  `country` varchar(64) NOT NULL,
	  `postal_code` varchar(32) NOT NULL,
	  `city` varchar(64) NOT NULL,
	  `phone` varchar(64) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 2. B. Insert the data

	-- INSERT INTO `accounts_infos` (`uid`, `pseudonym`, `nickname`, `firstname`, `lastname`, `displayed_name`, `add_pseudonym`, `gender`, `sexuality`, `biography`, `theme`, `address`, `country`, `postal_code`, `city`, `phone`) VALUES

	-- II. 2. C. Extras

	ALTER TABLE `accounts_infos`
	  ADD PRIMARY KEY (`uid`);

	-- ALTER TABLE `accounts_infos`
	  -- ADD CONSTRAINT `accounts_infos_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 3. Table `accounts_log_limits`

	-- II. 3. A. Create the table

	CREATE TABLE `accounts_log_limits` (
	  `id` int(11) NOT NULL,
	  `uid` varchar(36) DEFAULT NULL,
	  `user_id` varchar(128) DEFAULT NULL,
	  `ip_address` varchar(64) NOT NULL,
	  `action` varchar(64) NOT NULL,
	  `last_trigger` datetime NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 3. B. Insert the data

	-- No data to insert

	-- II. 3. C. Extras

	ALTER TABLE `accounts_log_limits`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `accounts_log_limits`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

	-- ALTER TABLE `accounts_log_limits`
	  -- ADD CONSTRAINT `accounts_log_limits_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 4. Table `accounts_sessions`

	-- II. 4. A. Create the table

	CREATE TABLE `accounts_sessions` (
	  `uid` varchar(36) DEFAULT NULL,
	  `auth_key` varchar(128) NOT NULL DEFAULT '',
	  `ip_address` varchar(64) DEFAULT NULL,
	  `user_agent` text,
	  `creation_date` datetime NOT NULL,
	  `login_date` datetime DEFAULT NULL,
	  `expire_date` datetime DEFAULT NULL,
	  `update_date` datetime NOT NULL,
	  `last_seen_page` varchar(256) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 4. B. Insert the data

	-- No data to insert
	-- INSERT INTO `accounts_sessions` (`uid`, `auth_key`, `ip_address`, `user_agent`, `creation_date`, `login_date`, `expire_date`, `update_date`, `last_seen_page`) VALUES

	-- II. 4. C. Extras

	ALTER TABLE `accounts_sessions`
	  ADD PRIMARY KEY (`auth_key`);

	-- ALTER TABLE `accounts_sessions`
	  -- ADD CONSTRAINT `accounts_sessions_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 5. Table `accounts_requests`

	-- II. 5. A. Create the table

	CREATE TABLE `accounts_requests` (
	  `activate_key` varchar(255) NOT NULL,
	  `uid` varchar(36) NOT NULL,
	  `action` varchar(64) NOT NULL,
	  `request_date` datetime NOT NULL,
	  `expire_date` datetime NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 5. B. Insert the data

	-- No data to insert

	-- II. 5. C. Extras

	ALTER TABLE `accounts_requests`
	  ADD PRIMARY KEY (`activate_key`);

	-- ALTER TABLE `accounts_requests`
	  -- ADD CONSTRAINT `accounts_requests_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 6. Table `accounts_rights`

	-- II. 6. A. Create the table

	CREATE TABLE `accounts_rights` (
	  `level` int(11) NOT NULL,
	  `user_right` varchar(64) NOT NULL,
	  `name` varchar(64) NOT NULL,
	  `permissions` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 6. B. Insert the data

	INSERT INTO `accounts_rights` (`level`, `user_right`, `name`, `permissions`) VALUES
	(1, 'NEW-USER', 'New user', '{\"0\":\"no\",\"1\":\"permissions\",\"2\":\"yet\"}'),
	(2, 'BANNED', 'Banned user', '{\"0\":\"no\",\"1\":\"permissions\",\"2\":\"yet\"}'),
	(3, 'USER', 'Regular user', '{\"0\":\"no\",\"1\":\"permissions\",\"2\":\"yet\"}'),
	(4, 'VIP', 'VIP user', '{\"0\":\"no\",\"1\":\"permissions\",\"2\":\"yet\"}'),
	(5, 'MOD', 'Moderator', '{\"0\":\"no\",\"1\":\"permissions\",\"2\":\"yet\"}'),
	(6, 'ADMIN', 'Administrator', '{\"0\":\"no\",\"1\":\"permissions\",\"2\":\"yet\"}'),
	(7, 'ROOT', 'Root', '*');

	-- II. 6. C. Extras

	ALTER TABLE `accounts_rights`
	  ADD PRIMARY KEY (`level`);

	ALTER TABLE `accounts_rights`
	  MODIFY `level` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

-- II. 6. Table `accounts_permissions`

	-- II. 6. A. Create the table

	CREATE TABLE `accounts_permissions` (
	  `uid` varchar(36) NOT NULL,
	  `permissions` mediumtext NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 6. B. Insert the data

	-- INSERT INTO `accounts_permissions` (`uid`, `permissions`) VALUES

-- *** *** *** --

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;