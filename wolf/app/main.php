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
 * @package Wolf_CMS
 */
if (!defined('HELPER_PATH'))
    define('HELPER_PATH', CORE_ROOT.DS.'helpers');
if (!defined('URL_SUFFIX'))
    define('URL_SUFFIX', '');

ini_set('date.timezone', DEFAULT_TIMEZONE);
if (function_exists('date_default_timezone_set'))
    date_default_timezone_set(DEFAULT_TIMEZONE);
else
    putenv('TZ='.DEFAULT_TIMEZONE);


/**
 * Turns a path into an array of slugs
 * 
 * @param  string $path     A path (for instance path/to/page)
 * @return array            Array of slugs
 */
function explode_path($path) {
    return preg_split('/\//', $path, -1, PREG_SPLIT_NO_EMPTY);
}


/**
 * Alias for explode_path, this function should no longer be used.
 * 
 * @deprecated
 * @see  explode_path()
 */
function explode_uri($uri) {
    return explode_path($uri);
}


/**
 * This function should no longer be used.
 * 
 * @deprecated
 * @see Page::findBySlug()
 */
function find_page_by_slug($slug, &$parent, $all = false) {
    return Page::findBySlug($slug, $parent, $all);
}


function url_match($url) {
    $url = trim($url, '/');

    if (CURRENT_PATH == $url)
        return true;

    return false;
}


function url_start_with($url) {
    $url = trim($url, '/');

    if (CURRENT_PATH == $url)
        return true;

    if (strpos(CURRENT_PATH, $url) === 0)
        return true;

    return false;
}


function main() {
    // get the uri string from the query
    $path = $_SERVER['QUERY_STRING'];

    // Make sure special characters are decoded (support non-western glyphs like japanese)
    $path = urldecode($path);

    // START processing $_GET variables
    // If we're NOT using mod_rewrite, we check for GET variables we need to integrate
    if (!USE_MOD_REWRITE && strpos($path, '?') !== false) {
        $_GET = array(); // empty $_GET array since we're going to rebuild it
        list($path, $get_var) = explode('?', $path);
        $exploded_get = explode('&', $get_var);

        if (count($exploded_get)) {
            foreach ($exploded_get as $get) {
                list($key, $value) = explode('=', $get);
                $_GET[$key] = $value;
            }
        }
    }
    // We're NOT using mod_rewrite, and there's no question mark wich points to GET variables in combination with site root.
    else if (!USE_MOD_REWRITE && (strpos($path, '&') !== false || strpos($path, '=') !== false)) {
        $path = '/';
    }

    // If we're using mod_rewrite, we should have a WOLFPAGE entry.
    if (USE_MOD_REWRITE && array_key_exists('WOLFPAGE', $_GET)) {
        $path = $_GET['WOLFPAGE'];
        unset($_GET['WOLFPAGE']);
    }
    else if (USE_MOD_REWRITE)   // We're using mod_rewrite but don't have a WOLFPAGE entry, assume site root.
        $path = '/';

    // Needed to allow for ajax calls to backend
    if (array_key_exists('WOLFAJAX', $_GET)) {
        $path = '/'.ADMIN_DIR.$_GET['WOLFAJAX'];
        unset($_GET['WOLFAJAX']);
    }
    // END processing $_GET variables
    // remove suffix page if founded
    if (URL_SUFFIX !== '' and URL_SUFFIX !== '/')
        $path = preg_replace('#^(.*)('.URL_SUFFIX.')$#i', "$1", $path);

    define('CURRENT_PATH', trim($path, '/'));

    // Alias for backward compatibility, this constant should no longer be used.
    define('CURRENT_URI', CURRENT_PATH);

    if ($path != null && $path[0] != '/')
        $path = '/'.$path;

    // Check if there's a custom route defined for this URI,
    // otherwise continue and assume page was requested.
    if (Dispatcher::hasRoute($path)) {
        Observer::notify('dispatch_route_found', $path);
        Dispatcher::dispatch($path);
        exit;
    }

    foreach (Observer::getObserverList('page_requested') as $callback) {
        $path = call_user_func_array($callback, array(&$path));
    }

    // this is where 80% of the things is done
    $page = Page::findByPath($path, true);

    // if we found it, display it!
    if (is_object($page)) {
        // If a page is in preview status, only display to logged in users
        if (Page::STATUS_PREVIEW == $page->status_id) {
            AuthUser::load();
            if (!AuthUser::isLoggedIn() || !AuthUser::hasPermission('page_view'))
                pageNotFound($path);
        }

        // If page needs login, redirect to login
        if ($page->getLoginNeeded() == Page::LOGIN_REQUIRED) {
            AuthUser::load();
            if (!AuthUser::isLoggedIn()) {
                Flash::set('redirect', $page->url());
                redirect(URL_PUBLIC.(USE_MOD_REWRITE ? '' : '?/').ADMIN_DIR.'/login');
            }
        }

        Observer::notify('page_found', $page);
        $page->_executeLayout();
    }
    else {
        pageNotFound($path);
    }
}

// main
// ok come on! let's go! (movie: Hacker's)
ob_start();
main();
ob_end_flush();