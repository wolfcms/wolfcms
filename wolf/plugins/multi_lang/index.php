<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The multi lang plugin redirects users to a page with content in their language.
 *
 * The redirect only occurs when a user's indicated preferred language is
 * available. There are multiple methods to determine the desired language.
 * These are:
 *
 * - HTTP_ACCEPT_LANG header
 * - URI based language hint (for example: http://www.example.com/en/page.html
 * - Preferred language setting of logged in users
 *
 * @package Plugins
 * @subpackage multi-lang
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

$urilang = false;

Plugin::setInfos(array(
    'id'          => 'multi_lang',
    'title'       => __('Multiple Languages'),
    'description' => __('Provides language specific content when available based on user preferences.'),
    'version'     => '1.0.0',
   	'license'     => 'GPL',
	'author'      => 'Martijn van der Kleijn',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml',
    'require_wolf_version' => '0.7.0'
));

Plugin::addController('multi_lang', __('Multiple Languages'), 'administrator', false);

// Observe the necessary events.
$style = Plugin::getSetting('style', 'multi_lang');
$source = Plugin::getSetting('langsource', 'multi_lang');
if (false !== $style && $style == 'tab') {

    if ($source == 'uri') {
        Observer::observe('page_requested', 'replaceUri');
        Observer::observe('page_found', 'replaceContentByUri');
    }
    else {
        Observer::observe('page_found', 'replaceContent');
    }
}
else if (false !== $style && $style == 'page') {
    if ($source == 'header' || $source == 'preferences') {
        Observer::observe('page_found', 'replaceContent');
    }
}

/**
 * Replaces the content of the 'body' part if a language specific part exists.
 *
 * @param Page $page Page object.
 */
function replaceContent($page) {
    $source = Plugin::getSetting('langsource', 'multi_lang');
    $style = Plugin::getSetting('style', 'multi_lang');
    if (!$source || !$style) return;

    if ($source == 'header' && $style == 'tab') {
        use_helper('I18n');
        $found = false;

        foreach (I18n::getPreferredLanguages() as $lang) {
            if ( Setting::get('language') == $lang) { break; }

            if ( isset($page->part->$lang) && !empty($page->part->$lang->content_html) && $page->part->$lang->content_html != '' ) {
                $page->part->body->content_html = $page->part->$lang->content_html;
                $found = true;
            }

            if ($found) break;
        }
    }
    else if ($source == 'preferences' && $style == 'tab') {
        AuthUser::load();
        if (AuthUser::isLoggedIn()) {
            $lang = AuthUser::getRecord()->language;

            if ( isset($page->part->$lang) && !empty($page->part->$lang->content_html) && $page->part->$lang->content_html != '' ) {
                $page->part->body->content_html = $page->part->$lang->content_html;
            }
        }
    }
    else if ($source == 'header' && $style == 'page') {
        use_helper('I18n');

        foreach (I18n::getPreferredLanguages() as $lang) {
            if ( Setting::get('language') == $lang) { break; }

            $uri = $lang.'/'.CURRENT_PATH;
            $page = Page::findByPath($uri);

            if ( false !== $page ) {
                redirect(BASE_URL.$uri);
            }
        }
    }
    else if ($source == 'preferences' && $style == 'page') {
        AuthUser::load();
        if (AuthUser::isLoggedIn()) {
            $lang = AuthUser::getRecord()->language;

            $uri = $lang.'/'.CURRENT_PATH;
            $page = Page::findByPath($uri);

            if ( false !== $page ) {
                redirect(BASE_URL.$uri);
            }
        }
    }
}

function replaceUri($uri) {    
    if (startsWith($uri, '/')) {
        $uri = substr($uri, 1);
    }

    global $urilang;
    $tmp = explode('/', $uri, 2);

    if (array_key_exists($tmp[0], SettingController::$ietf)) {
        $urilang = $tmp[0];
        $uri = substr($uri, 2);
    }
    else $urilang = false;

    return $uri;
}

function replaceContentByUri($page) {
    $source = Plugin::getSetting('langsource', 'multi_lang');
    if (!$source) return;

    global $urilang;

    if ($source == 'uri' && $urilang !== false) {
        if ( isset($page->part->$urilang) && !empty($page->part->$urilang->content_html) && $page->part->$urilang->content_html != '' ) {
            $page->part->body->content_html = $page->part->$urilang->content_html;
        }
    }
}