<?php
/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package wolf
 * @subpackage plugin.comment
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.2.0
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Martijn van der Kleijn, 2009
 */

if (Plugin::deleteAllSettings('comment') === false) {
    Flash::set('error', __('Unable to delete plugin settings.'));
    redirect(get_url('setting'));
}

$PDO = Record::getConnection();

if ($PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'comment') === false) {
    Flash::set('error', __('Unable to drop table :tablename', array(':tablename' => TABLE_PREFIX.'comment')));
    redirect(get_url('setting'));
}

$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));
$ret = true;

if ($driver == 'mysql') {
    $ret = $PDO->exec('ALTER TABLE '.TABLE_PREFIX.'page DROP comment_status');
}
else if ($driver == 'sqlite') {
    // Removing the indexes
    $ret = $PDO->exec('DROP INDEX IF EXISTS '.TABLE_PREFIX.'comment.comment_page_id');
    if ($ret === false) break;
    $ret = $PDO->exec('DROP INDEX IF EXISTS '.TABLE_PREFIX.'comment.comment_created_on');
    if ($ret === false) break;

    /*
     * Unfortunately, SQLite does not support removing colums from a table.
     * http://sqlite.org/lang_altertable.html
     *
    $ret = $PDO->exec('ALTER TABLE '.TABLE_PREFIX.'page DROP comment_status');
    if ($ret === false) break;
     *
     */

    // Lastly, clean up database space by issueing the VACUUM command
    $ret = $PDO->exec('VACUUM');
}

if ($ret === false) {
    Flash::set('error', __('Unable to clean up table alterations.'));
    redirect(get_url('setting'));
}
else {
    Flash::set('success', __('Successfully uninstalled plugin.'));
    redirect(get_url('setting'));
}