<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Markdown plugin allows users edit pages using the markdown syntax.
 *
 * @package Plugins
 * @subpackage markdown
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

Plugin::setInfos(array(
    'id'          => 'markdown',
    'title'       => __('Markdown filter'),
    'description' => __('Allows you to use the Markdown text filter (with MarkdownExtra and Smartypants).'),
    'version'     => '2.0.2',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
));

Filter::add('markdown', 'markdown/filter_markdown.php');
Plugin::addController('markdown', null, 'admin_view', false);
Plugin::addJavascript('markdown', 'markdown.php');
