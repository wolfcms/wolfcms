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
 * Provides Page not found page types.
 *
 * @package Plugins
 * @subpackage page-not-found
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) {
    exit();
}

Plugin::setInfos(array(
    'id' => 'page_not_found',
    'title' => __('Page not found'),
    'description' => __('Provides Page not found page types.'),
    'version' => '1.0.0',
    'website' => 'http://www.wolfcms.org/',
    'update_url' => 'http://www.wolfcms.org/plugin-versions.xml'
));

Behavior::add('page_not_found', '');
Observer::observe('page_not_found', 'behavior_page_not_found');


/**
 * Presents browser with a custom 404 page.
 */
function behavior_page_not_found($url) {
    $page = Page::findByBehaviour('page_not_found');

    if (is_a($page, 'Page')) {
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");

        $page->_executeLayout();
        exit(); // need to exit otherwise true error page will be sent
    }
}