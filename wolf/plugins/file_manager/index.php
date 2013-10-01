<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * @package Plugins
 * @subpackage file-manager
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * Root location where Files plugin lives.
 */
define('FILES_ROOT', PATH_PUBLIC.'wolf/plugins/file_manager');

/**
 * Root location where files get uploaded to as an absolute path.
 */
define('FILES_DIR', CMS_ROOT.DS.'public');

/**
 * Root location where files get uploaded to as a URL.
 */
define('BASE_FILES_DIR', URL_PUBLIC . 'public'); 

// DO NOT EDIT AFTER THIS LINE -----------------------------------------------

Plugin::setInfos(array(
    'id'          => 'file_manager',
    'title'       => __('File Manager'),
    'description' => __('Provides interface to manage files from the administration.'),
    'version'     => '1.0.0', 
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
));

Plugin::addController('file_manager', __('Files'), 'file_manager_view');

// Make sure possible hack attempts get registered if the statistics API is available.
if (Plugin::isEnabled('statistics_api')) {
    Observer::observe('stats_file_manager_hack_attempt', 'StatisticsEvent::registerEvent');
}
