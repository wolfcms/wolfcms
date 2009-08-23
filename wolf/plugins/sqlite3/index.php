<?php
/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS.
 *
 * Wolf CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Wolf CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wolf CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Wolf CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * Provides function to run Wolf CMS with SQLite 3 database.
 *
 * @package wolf
 * @subpackage plugin.sqlite3
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 1.1.0
 * @since Wolf version 0.5.5
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Martijn van der Kleijn, 2009
 * @copyright Philippe Archambault, 2008
 */

/**
 *
 */
Plugin::setInfos(array(
    'id'          => 'sqlite3',
    'title'       => __('SQLite 3'),
    'description' => __('Allows Wolf CMS to use the SQLite 3 database.'),
    'version'     => '1.1.0',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
    ));

// Adding date_format function to SQLite 3 'mysql date_format function'
if (! function_exists('mysql_date_format_function')) {
    function mysql_function_date_format($date, $format) {
        return strftime($format, strtotime($date));
    }
}

$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));

if ($driver === 'sqlite') {
    $PDO->sqliteCreateFunction('date_format', 'mysql_function_date_format', 2);
}