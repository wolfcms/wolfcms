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

//  Constantes  --------------------------------------------------------------

define('CMS_BACKEND', true);

define('CMS_VERSION', '0.6.0');
define('FROG_VERSION', CMS_VERSION); // DEFINED ONLY FOR BACKWARDS SUPPORT - to be taken out before 0.9.0

define('CMS_ROOT', dirname(__FILE__).'/..');
define('FROG_ROOT', CMS_ROOT); // DEFINED ONLY FOR BACKWARDS SUPPORT - to be taken out before 0.9.0
define('CORE_ROOT', CMS_ROOT.'/wolf');
define('PLUGINS_ROOT', CORE_ROOT.'/plugins');


require_once(CORE_ROOT.'/utils.php');

$config_file = CMS_ROOT.'/config.php';

require_once($config_file);

// Figure out what the public URI is based on URL_PUBLIC.
// TODO - improve
$changedurl = str_replace('//','|',URL_PUBLIC);
$lastslash = strpos($changedurl, '/');
if (false === $lastslash) {
    define('URI_PUBLIC', '/');
}
else {
    define('URI_PUBLIC', substr($changedurl, $lastslash));
}

// Security checks -----------------------------------------------------------
if (DEBUG == false && isWritable($config_file)) {
// Windows systems always have writable config files... skip those.
    if (substr(PHP_OS, 0, 3) != 'WIN') {
        echo '<html><head><title>Wolf CMS automatically disabled!</title></head><body>';
        echo '<h1>Wolf CMS automatically disabled!</h1>';
        echo '<p>Wolf CMS has been disabled as a security precaution.</p>';
        echo '<p><strong>Reason:</strong> the configuration file was found to be writable.</p>';
        echo '</body></html>';
        exit();
    }
}

define('APP_PATH',  CORE_ROOT.'/app');

define('SESSION_LIFETIME', 3600);
define('REMEMBER_LOGIN_LIFETIME', 1209600); // two weeks

define('DEFAULT_CONTROLLER', 'page');
define('DEFAULT_ACTION', 'index');

define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false);

$url = URL_PUBLIC;

if (defined('CMS_BACKEND') && CMS_BACKEND && defined('USE_HTTPS') && USE_HTTPS) {
    $url = str_replace('http://', 'https://', $url);
}

define('BASE_URL', $url . (endsWith($url, '/') ? '': '/') . ADMIN_DIR . (endsWith(ADMIN_DIR, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?/'));

require CORE_ROOT.'/Framework.php';

//  Database connection  -----------------------------------------------------
$__CMS_CONN__ = new PDO(DB_DSN, DB_USER, DB_PASS);
if ($__CMS_CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql')
    $__CMS_CONN__->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

// DEFINED ONLY FOR BACKWARDS SUPPORT - to be taken out before 0.9.0
$__FROG_CONN__ = $__CMS_CONN__;

Record::connection($__CMS_CONN__);
Record::getConnection()->exec("set names 'utf8'");


//  Initialize  --------------------------------------------------------------

Setting::init();

use_helper('I18n');

AuthUser::load();
if (AuthUser::isLoggedIn()) {
    I18n::setLocale(AuthUser::getRecord()->language);
}
else {
    I18n::setLocale(Setting::get('language'));
}

Plugin::init();

//  Get controller and action and execute  -----------------------------------

Dispatcher::dispatch(null, Setting::get('default_tab'));
