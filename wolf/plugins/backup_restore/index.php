<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages and settings to an XML file.
 *
 * @package Plugins
 * @subpackage backup_restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2011
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */


if (!defined('BR_VERSION')) { define('BR_VERSION', '0.7.3'); }
/**
 *
 * Root location where Comment plugin lives.
 */
define('BACKUPRESTORE_ROOT', URI_PUBLIC.'wolf/plugins/backup_restore');

Plugin::setInfos(array(
    'id'          => 'backup_restore',
    'title'       => __('Backup Restore'),
    'description' => __('Provides administrators with the option of backing up their pages and settings to an XML file.'),
    'version'     => BR_VERSION,
    'license'     => 'GPLv3',
    'author'      => 'Martijn van der Kleijn',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml',
    'require_wolf_version' => '0.6.0'
));

Plugin::addController('backup_restore', __('Backup Restore'), 'backup_restore_view', true);