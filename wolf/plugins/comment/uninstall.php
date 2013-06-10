<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage comment
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2009
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

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

if ($driver == 'mysql' || $driver == 'pgsql') {
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