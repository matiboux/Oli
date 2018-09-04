/*\
|*|  --------------------------------
|*|  --- [  Oli Basic SQL file  ] ---
|*|  --------------------------------
\*/

-- I. 1. Table `settings`

	-- I. 1. A. Create the table

	CREATE TABLE `settings` (
	  `id` int(11) NOT NULL,
	  `name` varchar(64) NOT NULL,
	  `value` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- I. 1. B. Insert the data

	INSERT INTO `settings` (`id`, `name`, `value`) VALUES
	(1, 'url', 'urwebs.it/'),
	(2, 'name', 'Your own Oli website!'),
	(3, 'description', 'Is that your website?'),
	(4, 'version', '1.0'),
	(5, 'creation_date', '2014-11-15'),
	(6, 'status', ''),
	(7, 'owner', '');

	-- I. 1. C. Extras

	ALTER TABLE `settings`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `settings`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

-- I. 2. Table `shortcut_links`

	-- I. 2. A. Create the table
	CREATE TABLE `shortcut_links` (
	  `id` int(11) NOT NULL,
	  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	  `url` varchar(256) COLLATE utf8_unicode_ci NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	-- III. 2. B. Insert the data

	INSERT INTO `shortcut_links` (`id`, `name`, `url`) VALUES
	(1, 'Oli', 'https://github.com/matiboux/Oli/');

	-- III. 2. C. Extras

	ALTER TABLE `shortcut_links`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `shortcut_links`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;