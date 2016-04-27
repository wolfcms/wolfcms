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
 * @package Wolf_CMS
 */

//  Constants  ---------------------------------------------------------------
define('IN_CMS', true);

define('CMS_VERSION', '0.8.3.1');
define('CMS_ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
define('CORE_ROOT', CMS_ROOT.DS.'wolf');
define('PLUGINS_ROOT', CORE_ROOT.DS.'plugins');
define('APP_PATH', CORE_ROOT.DS.'app');

require_once(CORE_ROOT.DS.'utils.php');

$config_file = CMS_ROOT.DS.'config.php';
require_once($config_file);

// if you have installed wolf and see this line, you can comment it or delete it :)
if ( ! defined('DEBUG')) { header('Location: wolf/install/'); exit(); }

$url = URL_PUBLIC;

// Figure out what the public path is based on URL_PUBLIC.
// @todo improve
$changedurl = str_replace('//','|',URL_PUBLIC);
$lastslash = strpos($changedurl, '/');
if (false === $lastslash) {
    define('PATH_PUBLIC', '/');
}
else {
    define('PATH_PUBLIC', substr($changedurl, $lastslash));
}

// Alias for backward compatibility, this constant should no longer be used.
define('URI_PUBLIC', PATH_PUBLIC);

// Determine path for backend check
if (USE_MOD_REWRITE && isset($_GET['WOLFPAGE'])) {
    $admin_check = $_GET['WOLFPAGE'];
}
else {
    $admin_check = !empty($_SERVER['QUERY_STRING']) ? urldecode($_SERVER['QUERY_STRING']) : '';
}

// Are we in frontend or backend?
if (startsWith($admin_check, ADMIN_DIR) || startsWith($admin_check, '/'.ADMIN_DIR) || isset($_GET['WOLFAJAX'])) {
    define('CMS_BACKEND', true);
    if (defined('USE_HTTPS') && USE_HTTPS) {
        $url = str_replace('http://', 'https://', $url);
    }
    define('BASE_URL', $url . (endsWith($url, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?/') . ADMIN_DIR . (endsWith(ADMIN_DIR, '/') ? '': '/'));
    define('BASE_PATH', PATH_PUBLIC . (endsWith($url, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?/') . ADMIN_DIR . (endsWith(ADMIN_DIR, '/') ? '': '/'));
}
else {
    define('BASE_URL', URL_PUBLIC . (endsWith(URL_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));
    define('BASE_PATH', PATH_PUBLIC . (endsWith(PATH_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));
}

// Alias for backward compatibility, this constant should no longer be used.
define('BASE_URI', BASE_PATH);

define('PLUGINS_PATH', PATH_PUBLIC.'wolf/plugins/');
if (!defined('THEMES_ROOT')) { define('THEMES_ROOT', CMS_ROOT.DS.'public'.DS.'themes'.DS); }
if (!defined('THEMES_PATH')) { define('THEMES_PATH', PATH_PUBLIC.'public/themes/'); }
if (!defined('ICONS_PATH')) { define('ICONS_PATH', PATH_PUBLIC.'wolf/icons/'); }

// Aliases for backward compatibility, these constants should no longer be used.
define('THEMES_URI', THEMES_PATH);
define('PLUGINS_URI', PLUGINS_PATH);
define('ICONS_URI', ICONS_PATH);

// Security checks -----------------------------------------------------------
if (DEBUG == false && isWritable($config_file)) {
    $lock = false;
    // Windows systems always have writable config files... skip those.
    if (function_exists('posix_getuid') && function_exists('fileowner') && function_exists('fileperms') && substr(PHP_OS, 0, 3) != 'WIN') {
        $fileowner = fileowner($config_file);
        $processowner = posix_getuid();
        $perms = fileperms($config_file);

        // Is file owned by http server and does it have write permissions?
        if ($fileowner == $processowner && ($perms & 0x0080)) {
            $lock = true;
        }

        // Does the group have write permissions?
        if (($perms & 0x0010)) {
            $lock = true;
        }

        // Does the world have write permissions?
        if (($perms & 0x0002)) {
            $lock = true;
        }
    }

    if ($lock) {
        echo '<html><head><title>Wolf CMS automatically disabled!</title></head><body>';
        echo '<h1>Wolf CMS automatically disabled!</h1>';
        echo '<p>Wolf CMS has been disabled as a security precaution.</p>';
        echo '<p><strong>Reason:</strong> the configuration file was found to be writable.</p>';
        echo '<p>The broadest rights Wolf CMS allows for config.php are: -rwxr-xr-x</p>';
        echo '</body></html>';
        exit();
    }
}

//  Init  --------------------------------------------------------------------

define('SESSION_LIFETIME', 3600);
define('REMEMBER_LOGIN_LIFETIME', 1209600); // two weeks

define('DEFAULT_CONTROLLER', 'page');
define('DEFAULT_ACTION', 'index');

require CORE_ROOT.DS.'Framework.php';

AutoLoader::register();

AutoLoader::addFolder(array(
    APP_PATH.DIRECTORY_SEPARATOR.'models',
    APP_PATH.DIRECTORY_SEPARATOR.'controllers',
));

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
Flash::init();

// Setup admin routes
$admin_routes = array (
    '/'.ADMIN_DIR          => Setting::get('default_tab'),
    '/'.ADMIN_DIR.'/'      => Setting::get('default_tab'),
    '/'.ADMIN_DIR.'/:all'  => '$1',
);

Dispatcher::addRoute($admin_routes);

// run everything!
require APP_PATH.DS.'main.php';
