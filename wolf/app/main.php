<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008,2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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

require APP_PATH . '/models/Plugin.php';
require APP_PATH . '/models/Page.php';

if ( ! defined('HELPER_PATH')) define('HELPER_PATH', CORE_ROOT.'/helpers');
if ( ! defined('URL_SUFFIX')) define('URL_SUFFIX', '');

ini_set('date.timezone', DEFAULT_TIMEZONE);
if(function_exists('date_default_timezone_set'))
    date_default_timezone_set(DEFAULT_TIMEZONE);
else
    putenv('TZ='.DEFAULT_TIMEZONE);

// Intialize Setting and Plugin
Setting::init();
Plugin::init();

/**
 * Explode an URI and make a array of params
 */
function explode_uri($uri) {
    return preg_split('/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
}


function find_page_by_slug($slug, &$parent, $all = false) {
    global $__CMS_CONN__;

    $page_class = 'Page';

    $parent_id = $parent ? $parent->id: 0;

    $sql = 'SELECT page.*, author.name AS author, updater.name AS updater '
        . 'FROM '.TABLE_PREFIX.'page AS page '
        . 'LEFT JOIN '.TABLE_PREFIX.'user AS author ON author.id = page.created_by_id '
        . 'LEFT JOIN '.TABLE_PREFIX.'user AS updater ON updater.id = page.updated_by_id ';

    if ($all) {
        $sql .= 'WHERE slug = ? AND parent_id = ? AND (status_id='.Page::STATUS_PREVIEW.' OR status_id='.Page::STATUS_PUBLISHED.' OR status_id='.Page::STATUS_HIDDEN.')';
    }
    else {
        $sql .= 'WHERE slug = ? AND parent_id = ? AND (status_id='.Page::STATUS_PUBLISHED.' OR status_id='.Page::STATUS_HIDDEN.')';
    }

    $stmt = $__CMS_CONN__->prepare($sql);

    $stmt->execute(array($slug, $parent_id));

    if ($page = $stmt->fetchObject()) {
    // hook to be able to redefine the page class with behavior
        if ( ! empty($parent->behavior_id)) {
        // will return Page by default (if not found!)
            $page_class = Behavior::loadPageHack($parent->behavior_id);
        }

        // create the object page
        $page = new $page_class($page, $parent);

        // assign all is parts
        $page->part = get_parts($page->id);

        return $page;
    }
    else return false;
}

function get_parts($page_id) {
    global $__CMS_CONN__;

    $objPart = new stdClass;

    $sql = 'SELECT name, content_html FROM '.TABLE_PREFIX.'page_part WHERE page_id=?';

    if ($stmt = $__CMS_CONN__->prepare($sql)) {
        $stmt->execute(array($page_id));

        while ($part = $stmt->fetchObject())
            $objPart->{$part->name} = $part;
    }

    return $objPart;
}

function url_match($url) {
    $url = trim($url, '/');

    if (CURRENT_URI == $url)
        return true;

    return false;
}

function url_start_with($url) {
    $url = trim($url, '/');

    if (CURRENT_URI == $url)
        return true;

    if (strpos(CURRENT_URI, $url) === 0)
        return true;

    return false;
}

function main() {
    // get the uri string from the query
    $uri = $_SERVER['QUERY_STRING'];

    // START processing $_GET variables
    // If we're NOT using mod_rewrite, we check for GET variables we need to integrate
    if (!USE_MOD_REWRITE && strpos($uri, '?') !== false) {
        $_GET = array(); // empty $_GET array since we're going to rebuild it
        list($uri, $get_var) = explode('?', $uri);
        $exploded_get = explode('&', $get_var);

        if (count($exploded_get)) {
            foreach ($exploded_get as $get) {
                list($key, $value) = explode('=', $get);
                $_GET[$key] = $value;
            }
        }
    }
    // We're NOT using mod_rewrite, and there's no question mark wich points to GET variables in combination with site root.
    else if (!USE_MOD_REWRITE && (strpos($uri, '&') !== false || strpos($uri, '=') !== false)) {
            $uri='';
        }

    // If we're using mod_rewrite, we should have a WOLFPAGE entry.
    if (USE_MOD_REWRITE && array_key_exists('WOLFPAGE', $_GET)) {
        $uri = $_GET['WOLFPAGE'];
        unset($_GET['WOLFPAGE']);
    }
    else if (USE_MOD_REWRITE)   // We're using mod_rewrite but don't have a WOLFPAGE entry, assume site root.
            $uri = '';

    // END processing $_GET variables

    // remove suffix page if founded
    if (URL_SUFFIX !== '' and URL_SUFFIX !== '/')
        $uri = preg_replace('#^(.*)('.URL_SUFFIX.')$#i', "$1", $uri);

    define('CURRENT_URI', trim($uri, '/'));

    if ($uri != null && $uri[0] != '/') $uri = '/'.$uri;

    // Check if there's a custom route defined for this URI,
    // otherwise continue and assume page was requested.
    if (Dispatcher::hasRoute($uri)) {
        Observer::notify('dispatch_route_found', $uri);
        Dispatcher::dispatch($uri);
    }

    Observer::notify('page_requested', $uri);

    // this is where 80% of the things is done
    $page = Page::findByUri($uri, true);

    // if we fund it, display it!
    if (is_object($page)) {
        // If a page is in preview status, only display to logged in users
        if (Page::STATUS_PREVIEW == $page->status_id) {
            AuthUser::load();
            if (!AuthUser::isLoggedIn() || !AuthUser::hasPermission('administrator, developer, editor'))
                page_not_found();
        }

        // If page needs login, redirect to login
        if ($page->getLoginNeeded() == Page::LOGIN_REQUIRED) {
            AuthUser::load();
            if (!AuthUser::isLoggedIn()) {
                Flash::set('redirect', $page->url());
                redirect(URL_PUBLIC.ADMIN_DIR.(USE_MOD_REWRITE ? '/': '/?/').'login');
            }
        }

        Observer::notify('page_found', $page);
        $page->_executeLayout();
    }
    else {
        page_not_found();
    }

} // main

// ok come on! let's go! (movie: Hacker's)
ob_start();
main();
ob_end_flush();