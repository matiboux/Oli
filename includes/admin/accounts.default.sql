/*\
|*|  -----------------------------------
|*|  --- [  Oli Accounts SQL file  ] ---
|*|  -----------------------------------
\*/

-- II. 1. Table `accounts`

	-- II. 1. A. Create the table

	CREATE TABLE `accounts` (
	  `id` int(11) NOT NULL,
	  `username` varchar(64) NOT NULL,
	  `password` varchar(128) NOT NULL,
	  `email` varchar(128) NOT NULL,
	  `birthday` date DEFAULT NULL,
	  `register_date` datetime NOT NULL,
	  `user_right` varchar(32) NOT NULL,
	  `language` varchar(32) NOT NULL,
	  `admin_note` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 1. B. Insert the data

	-- No data to insert

	-- II. 1. C. Extras

	ALTER TABLE `accounts`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `accounts`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

	ALTER TABLE `accounts`
	  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_right`) REFERENCES `accounts_rights` (`user_right`) ON DELETE SET NULL ON UPDATE CASCADE;

-- II. 2. Table `accounts_infos`

	-- II. 2. A. Create the table

	CREATE TABLE `accounts_infos` (
	  `id` int(11) NOT NULL,
	  `username` varchar(64) NOT NULL,
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

	-- No data to insert

	-- II. 2. C. Extras

	ALTER TABLE `accounts_infos`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `accounts_infos`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

	ALTER TABLE `accounts_infos`
	  ADD CONSTRAINT `accounts_infos_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 3. Table `accounts_log_limits`

	-- II. 3. A. Create the table

	CREATE TABLE `accounts_log_limits` (
	  `id` bigint(20) NOT NULL,
	  `username` varchar(64) DEFAULT NULL,
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
	  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

	ALTER TABLE `accounts_log_limits`
	  ADD CONSTRAINT `accounts_log_limits_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 4. Table `accounts_sessions`

	-- II. 4. A. Create the table

	CREATE TABLE `accounts_sessions` (
	  `id` bigint(20) NOT NULL,
	  `username` varchar(64) NOT NULL,
	  `auth_key` varchar(256) NOT NULL,
	  `user_ip` varchar(64) NOT NULL,
	  `port` varchar(32) NOT NULL,
	  `login_date` datetime NOT NULL,
	  `expire_date` datetime NOT NULL,
	  `update_date` datetime NOT NULL,
	  `last_seen_page` varchar(256) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 4. B. Insert the data

	-- No data to insert

	-- II. 4. C. Extras

	ALTER TABLE `accounts_sessions`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `accounts_sessions`
	  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

	ALTER TABLE `accounts_sessions`
	  ADD CONSTRAINT `accounts_sessions_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 5. Table `accounts_requests`

	-- II. 5. A. Create the table

	CREATE TABLE `accounts_requests` (
	  `id` bigint(20) NOT NULL,
	  `username` varchar(64) NOT NULL,
	  `pseudonym` varchar(64) NOT NULL,
	  `nickname` varchar(64) NOT NULL,
	  `firstname` varchar(64) NOT NULL,
	  `lastname` varchar(64) NOT NULL,
	  `displayed_name` varchar(32) NOT NULL,
	  `add_pseudonym` tinyint(1) NOT NULL,
	  `biography` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 5. B. Insert the data

	-- No data to insert

	-- II. 5. C. Extras

	ALTER TABLE `accounts_requests`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `accounts_requests`
	  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

	ALTER TABLE `accounts_requests`
	  ADD CONSTRAINT `accounts_requests_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accounts` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

-- II. 6. Table `accounts_rights`

	-- II. 6. A. Create the table

	CREATE TABLE `accounts_rights` (
	  `id` int(11) NOT NULL,
	  `user_right` varchar(64) NOT NULL,
	  `acronym` varchar(64) NOT NULL,
	  `name` varchar(64) NOT NULL,
	  `permissions` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- II. 6. B. Insert the data

	INSERT INTO `accounts_rights` (`id`, `user_right`, `acronym`, `name`, `permissions`) VALUES
	(1, 'NEW-USER', 'NEW', 'New user', '{"0":"no","1":"permissions","2":"yet"}'),
	(2, 'BANNED', 'BAN', 'Banned user', '{"0":"no","1":"permissions","2":"yet"}'),
	(3, 'USER', '', 'Regular user', '{"0":"no","1":"permissions","2":"yet"}'),
	(4, 'VIP', '', 'VIP user', '{"0":"no","1":"permissions","2":"yet"}'),
	(5, 'MODERATOR', 'MOD', 'Moderator', '{"0":"no","1":"permissions","2":"yet"}'),
	(6, 'ADMINISTRATOR', 'ADMIN', 'Administrator', '{"0":"no","1":"permissions","2":"yet"}'),
	(7, 'OWNER', '', 'Owner', '*');

	-- II. 6. C. Extras

	ALTER TABLE `accounts_rights`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `accounts_rights`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;