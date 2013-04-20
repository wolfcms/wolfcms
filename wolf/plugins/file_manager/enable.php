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

//	check for settings
$settings = Plugin::getAllSettings('file_manager');

//	merge settings
$settings = array(
	'umask'		=> isset($settings['umask'])        ? $settings['umask']	: '0022',
	'dirmode'	=> isset($settings['dirmode'])      ? $settings['dirmode']	: '0755',
	'filemode'	=> isset($settings['filemode'])     ? $settings['filemode']	: '0644',
        'show_hidden'   => isset($settings['show_hidden'])  ? $settings['show_hidden']	: '0',
        'show_backups'  => isset($settings['show_backups']) ? $settings['show_backups']	: '1'
);

//	flash message
if (Plugin::setAllSettings($settings, 'file_manager')) {
	Flash::set('success', 'File Manager - '.__('plugin settings initialized.'));
}
else {
	Flash::set('error', 'File Manager - '.__('unable to store plugin settings!'));
}

?>