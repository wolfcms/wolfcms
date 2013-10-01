<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Textile plugin provides a Filter that uses the Textile parser.
 *
 * @package Plugins
 * @subpackage textile
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

Plugin::setInfos(array(
    'id'          => 'textile',
    'title'       => __('Textile filter'),
    'description' => __('Allows you to use the Textile text filter.'),
    'version'     => '2.0.0',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
));

Filter::add('textile', 'textile/filter_textile.php');
Plugin::addController('textile', null, 'admin_view', false);
Plugin::addJavascript('textile', 'textile.php');