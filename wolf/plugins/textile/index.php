<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010 Martijn van der Kleijn <martijn.niji@gmail.com>
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

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The Textile plugin allows users to edit pages using the textile syntax.
 *
 * @package plugins
 * @subpackage textile
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 2.0.0
 * @since Wolf version 0.7.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2010
 */

Plugin::setInfos(array(
    'id'          => 'textile',
    'title'       => __('Textile filter'),
    'description' => __('Allows you to compose page parts or snippets using the Textile text filter.'),
    'version'     => '2.0.0',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
));

Filter::add('textile', 'textile/filter_textile.php');
Plugin::addController('textile', __('Textile'), 'administrator', false);