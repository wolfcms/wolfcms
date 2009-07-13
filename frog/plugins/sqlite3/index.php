<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * Provides function to run Frog CMS with SQLite 3 database.
 *
 * @package frog
 * @subpackage plugin.sqlite3
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 1.0
 * @since Frog version 0.9.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 *
 */
if (class_exists('PDO', false))
{
	Plugin::setInfos(array(
		'id'		  => 'sqlite3',
		'title'		  => 'SQLite 3', 
		'description' => 'Provides function to run Frog CMS with SQLite 3 database.', 
		'version'	  => '1.0.0', 
		'website'	  => 'http://www.madebyfrog.com/',
        'update_url'  => 'http://www.madebyfrog.com/plugin-versions.xml'
    ));

	// adding function date_format to sqlite 3 'mysql date_format function'
	if (! function_exists('mysql_date_format_function'))
	{
		function mysql_function_date_format($date, $format)
		{
			return strftime($format, strtotime($date));
		}
	}
	
	if (isset($GLOBALS['__FROG_CONN__']))
		if ($GLOBALS['__FROG_CONN__']->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite')
			$GLOBALS['__FROG_CONN__']->sqliteCreateFunction('date_format', 'mysql_function_date_format', 2);
	else if (Record::getConnection()->getAttribute(Record::ATTR_DRIVER_NAME) == 'sqlite')
		Record::getConnection()->sqliteCreateFunction('date_format', 'mysql_function_date_format', 2);
}