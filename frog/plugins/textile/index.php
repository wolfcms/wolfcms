<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * The Textile plugin allows users to edit pages using the textile syntax.
 *
 * @package frog
 * @subpackage plugin.textile
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 1.0.0
 * @since Frog version 0.9.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

Plugin::setInfos(array(
    'id'          => 'textile',
    'title'       => 'Textile filter', 
    'description' => 'Allows you to compose page parts or snippets using the Textile text filter.', 
    'version'     => '1.0.0', 
    'website'     => 'http://www.madebyfrog.com/',
    'update_url'  => 'http://www.madebyfrog.com/plugin-versions.xml'
));

Filter::add('textile', 'textile/filter_textile.php');
