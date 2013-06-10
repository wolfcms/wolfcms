<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages, settings and uploaded files to an XML file.
 *
 * @package Plugins
 * @subpackage backup-restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Frank Edelhaeuser <mrpace2@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2011
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

// Check if the plugin's settings already exist and create them if not.
if (Plugin::getSetting('zip', 'backup_restore') === false) {
    // Store settings new style
    $settings = array('zip' => '1',
                      'pwd' => '1',
                      'backupfiles' => '1',
                      'erasefiles' => '0',
                      'restorefiles' => '0',
                      'default_pwd' => 'pswpsw123',
                      'stamp' => 'Ymd',
                      'extension' => 'xml',
                      'wolfversion' => '0.6.0'
                     );

    Plugin::setAllSettings($settings, 'backup_restore');
}
