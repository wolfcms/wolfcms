<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
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

/**
 * @package wolf
 */

//  Constants  ---------------------------------------------------------------
define('IN_CMS', true);

define('CMS_VERSION', '0.6.0');
define('CMS_ROOT', dirname(__FILE__));
define('CORE_ROOT', CMS_ROOT.'/wolf');
define('PLUGINS_ROOT', CORE_ROOT.'/plugins');
define('APP_PATH', CORE_ROOT.'/app');

require_once(CORE_ROOT.'/utils.php');

$config_file = CMS_ROOT.'/config.php';
require_once($config_file);

// if you have installed wolf and see this line, you can comment it or delete it :)
if ( ! defined('DEBUG')) { header('Location: wolf/install/'); exit(); }

$url = URL_PUBLIC;

// Figure out what the public URI is based on URL_PUBLIC.
// @todo improve
$changedurl = str_replace('//','|',URL_PUBLIC);
$lastslash = strpos($changedurl, '/');
if (false === $lastslash) {
    define('URI_PUBLIC', '/');
}
else {
    define('URI_PUBLIC', substr($changedurl, $lastslash));
}

// Determine URI for backend check
if (USE_MOD_REWRITE && isset($_GET['WOLFPAGE'])) {
    $admin_check = $_GET['WOLFPAGE'];
}
else {
    $admin_check = urldecode($_SERVER['QUERY_STRING']);
}

// Are we in frontend or backend?
if (startsWith($admin_check, 'admin') || startsWith($admin_check, '/admin') || isset($_GET['WOLFAJAX'])) {
    define('CMS_BACKEND', true);
    if (defined('USE_HTTPS') && USE_HTTPS) {
        $url = str_replace('http://', 'https://', $url);
    }
    define('BASE_URL', $url . (endsWith($url, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?/') . ADMIN_DIR . (endsWith(ADMIN_DIR, '/') ? '': '/'));
    define('BASE_URI', URI_PUBLIC . (endsWith($url, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?/') . ADMIN_DIR . (endsWith(ADMIN_DIR, '/') ? '': '/'));
}
else {
    define('BASE_URL', URL_PUBLIC . (endsWith(URL_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));
    define('BASE_URI', URI_PUBLIC . (endsWith(URI_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));
}

define('PLUGINS_URI', URI_PUBLIC.'wolf/plugins/');
if (!defined('THEMES_ROOT')) { define('THEMES_ROOT', CMS_ROOT.'/public/themes/'); }
if (!defined('THEMES_URI')) { define('THEMES_URI', URI_PUBLIC.'public/themes/'); }


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

//  Init  --------------------------------------------------------------------

define('SESSION_LIFETIME', 3600);
define('REMEMBER_LOGIN_LIFETIME', 1209600); // two weeks

define('DEFAULT_CONTROLLER', 'page');
define('DEFAULT_ACTION', 'index');

require CORE_ROOT.'/Framework.php';

try {
    $__CMS_CONN__ = new PDO(DB_DSN, DB_USER, DB_PASS);
}
catch (PDOException $error) {
    die('DB Connection failed: '.$error->getMessage());
}

$driver = $__CMS_CONN__->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($driver === 'mysql') {
    $__CMS_CONN__->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
}

if ($driver === 'sqlite') {
    // Adding date_format function to SQLite 3 'mysql date_format function'
    if (! function_exists('mysql_date_format_function')) {
        function mysql_function_date_format($date, $format) {
            return strftime($format, strtotime($date));
        }
    }
    $__CMS_CONN__->sqliteCreateFunction('date_format', 'mysql_function_date_format', 2);
}

// DEFINED ONLY FOR BACKWARDS SUPPORT - to be taken out before 0.9.0
$__FROG_CONN__ = $__CMS_CONN__;

Record::connection($__CMS_CONN__);
Record::getConnection()->exec("set names 'utf8'");

Setting::init();

use_helper('I18n');
AuthUser::load();
if (AuthUser::isLoggedIn()) {
    I18n::setLocale(AuthUser::getRecord()->language);
}
else {
    I18n::setLocale(Setting::get('language'));
}

// Only add the cron web bug when necessary
if (defined('USE_POORMANSCRON') && USE_POORMANSCRON && defined('POORMANSCRON_INTERVAL')) {
    Observer::observe('page_before_execute_layout', 'run_cron');

    function run_cron() {
        $cron = Cron::findByIdFrom('Cron', '1');
        $now = time();
        $last = $cron->getLastRunTime();

        if ($now - $last > POORMANSCRON_INTERVAL) {
            echo $cron->generateWebBug();
        }
    }
}

Plugin::init();

// Setup admin routes
$admin_routes = array (
    '/'.ADMIN_DIR          => Setting::get('default_tab'),
    '/'.ADMIN_DIR.'/'      => Setting::get('default_tab'),
    '/'.ADMIN_DIR.'/:any'  => '$1'
);

Dispatcher::addRoute($admin_routes);

// run everything!
require APP_PATH.'/main.php';
