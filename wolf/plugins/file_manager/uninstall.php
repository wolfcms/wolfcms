<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * Note - Mostly rewritten since Wolf CMS 0.6.0
 *
 * @package Plugins
 * @subpackage file-manager
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008-2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 *
 * @todo Starting from PHP 5.3, use FileInfo
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

if (Plugin::deleteAllSettings('file_manager')) {
	Flash::set('success', 'File Manager - '.__('uninstalled.'));
}
else {
	Flash::set('error', 'File Manager - '.__('unable to remove stored settings!'));
}

?>