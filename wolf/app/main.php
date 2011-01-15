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

//require APP_PATH . '/models/Plugin.php';
//require APP_PATH . '/models/Page.php';

if ( ! defined('HELPER_PATH')) define('HELPER_PATH', CORE_ROOT.DS.'helpers');
if ( ! defined('URL_SUFFIX')) define('URL_SUFFIX', '');

ini_set('date.timezone', DEFAULT_TIMEZONE);
if(function_exists('date_default_timezone_set'))
    date_default_timezone_set(DEFAULT_TIMEZONE);
else
    putenv('TZ='.DEFAULT_TIMEZONE);

// Intialize Setting and Plugin
//Setting::init();
//Plugin::init();

/**
 * Explode an URI and make a array of params
 */
function explode_uri($uri) {
    return preg_split('/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
}

/**
 * This function should no longer be used.
 * 
 * @deprecated
 * @see Page::findBySlug()
 *
 * @param string $slug
 * @param type $parent
 * @param type $all
 * @return page_class 
 */
function find_page_by_slug($slug, &$parent, $all = false) {
    return Page::findBySlug($slug, $parent, $all);
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

    // Make sure special characters are decoded (support non-western glyphs like japanese)
    $uri = urldecode($uri);
    
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
            $uri='/';
        }

    // If we're using mod_rewrite, we should have a WOLFPAGE entry.
    if (USE_MOD_REWRITE && array_key_exists('WOLFPAGE', $_GET)) {
        $uri = $_GET['WOLFPAGE'];
        unset($_GET['WOLFPAGE']);
    }
    else if (USE_MOD_REWRITE)   // We're using mod_rewrite but don't have a WOLFPAGE entry, assume site root.
            $uri = '/';

    // Needed to allow for ajax calls to backend
    if (array_key_exists('WOLFAJAX', $_GET)) {
        $uri = '/'.ADMIN_DIR.$_GET['WOLFAJAX'];
        unset($_GET['WOLFAJAX']);
    }
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
        exit;
    }

    foreach(Observer::getObserverList('page_requested') as $callback) {
        $uri = call_user_func_array($callback, array(&$uri));
    }

    // this is where 80% of the things is done
    $page = Page::findByUri($uri, true);

    // if we found it, display it!
    if (is_object($page)) {
        // If a page is in preview status, only display to logged in users
        if (Page::STATUS_PREVIEW == $page->status_id) {
            AuthUser::load();
            if (!AuthUser::isLoggedIn() || !AuthUser::hasPermission('page_view'))
                page_not_found();
        }

        // If page needs login, redirect to login
        if ($page->getLoginNeeded() == Page::LOGIN_REQUIRED) {
            AuthUser::load();
            if (!AuthUser::isLoggedIn()) {
                Flash::set('redirect', $page->url());
                redirect(URL_PUBLIC.(USE_MOD_REWRITE ? '': '?/').ADMIN_DIR.'/login');
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