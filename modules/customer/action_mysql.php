<?php

/**
 * @Project NUKEVIET 4.x
 * @Author DANGDINHTU (dlinhvan@gmail.com)
 * @Copyright (C) 2013 Webdep24.com. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate  07, 23, 2013 4:41
 */

if( ! defined( 'NV_IS_FILE_MODULES' ) ) die( 'Stop!!!' );

$sql_drop_module = array();

$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_photo";

$sql_create_module = $sql_drop_module;

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_photo (
	photo_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	title varchar(255) NOT NULL default '',
	links varchar(255) NOT NULL,
	image varchar(255) NOT NULL,
	thumb varchar(255) NOT NULL,
	status tinyint(1) NOT NULL default '1',
	weight smallint(4) NOT NULL DEFAULT '0',
	date_added int(11) unsigned NOT NULL default '0',
	PRIMARY KEY (photo_id),
	UNIQUE KEY title (title)
) ENGINE=MyISAM";
