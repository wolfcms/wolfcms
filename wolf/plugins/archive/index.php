<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Archive plugin provides an Archive pagetype behaving similar to a blog or news archive.
 *
 * @package Plugins
 * @subpackage archive
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

Plugin::setInfos(array(
    'id'          => 'archive',
    'title'       => __('Archive'),
    'description' => __('Provides an Archive pagetype behaving similar to a blog or news archive.'),
    'version'     => '1.1.0',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
));

// Add the plugin's tab and controller
Plugin::addController('archive', '', 'admin_view', false);

Behavior::add('archive', 'archive/archive.php');
Behavior::add('archive_day_index', 'archive/archive.php');
Behavior::add('archive_month_index', 'archive/archive.php');
Behavior::add('archive_year_index', 'archive/archive.php');