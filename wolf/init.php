<?php

defined('IN_CMS') or exit;
defined('CMS_ROOT') or define('CMS_ROOT', dirname(__FILE__));

//  Constants  ---------------------------------------------------------------
define('CMS_VERSION', '0.8.0-dev');
define('DS', DIRECTORY_SEPARATOR);
define('CORE_ROOT', CMS_ROOT . DS . 'wolf');
define('PLUGINS_ROOT', CORE_ROOT . DS . 'plugins');
define('APP_PATH', CORE_ROOT . DS . 'app');

require_once CORE_ROOT . DS . 'utils.php';

$config_file = CMS_ROOT . DS . 'config.php';
require_once $config_file;

// if you have installed wolf and see this line, you can comment it or delete it :)
if ( ! defined('DEBUG')) { header('Location: wolf/install/'); exit(); }

CmsInit::defineUrlConstants();
DEBUG or CmsInit::securityCheckConfigFile($config_file);

//  Init  --------------------------------------------------------------------

define('SESSION_LIFETIME', 3600);
define('REMEMBER_LOGIN_LIFETIME', 1209600); // two weeks

define('DEFAULT_CONTROLLER', 'page');
define('DEFAULT_ACTION', 'index');

require CORE_ROOT . DS . 'Framework.php';

CmsInit::dbConnection();

Setting::init();

use_helper('I18n');
AuthUser::load();
I18n::setLocale(AuthUser::isLoggedIn() ? AuthUser::getRecord()->language : Setting::get('language'));

CmsInit::poorMansCron();

Plugin::init();
Flash::init();

CmsInit::adminRoutes();

//  End of Init  -------------------------------------------------------------

class CmsInit {
    public static function defineUrlConstants() {
        $url = URL_PUBLIC;
        
        // Figure out what the public URI is based on URL_PUBLIC.
        // @todo improve
        $changedurl = str_replace('//','|',URL_PUBLIC);
        $lastslash = strpos($changedurl, '/');
        define('URI_PUBLIC', false === $lastslash ? '/' : substr($changedurl, $lastslash));
        
        // Determine URI for backend check
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
            define('BASE_URL', rtrim($url, '/') . '/' . (USE_MOD_REWRITE ? '': '?/') . rtrim(ADMIN_DIR, '/') . '/');
            # TODO next line seems to be wrong. really URI_PUBLIC followed by a $url check?  
            #define('BASE_URI', URI_PUBLIC . (endsWith($url, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?/') . rtrim(ADMIN_DIR, '/') . '/');
            define('BASE_URI', rtrim(URI_PUBLIC, '/') . '/' . (USE_MOD_REWRITE ? '': '?/') . rtrim(ADMIN_DIR, '/') . '/');
        }
        else {
            define('BASE_URL', rtrim(URL_PUBLIC, '/') . '/' . (USE_MOD_REWRITE ? '': '?'));
            define('BASE_URI', rtrim(URI_PUBLIC, '/') . '/' . (USE_MOD_REWRITE ? '': '?'));
        }
        
        define('PLUGINS_URI', URI_PUBLIC.'wolf/plugins/');
        defined('THEMES_ROOT') or define('THEMES_ROOT', CMS_ROOT.DS.'public'.DS.'themes'.DS);
        defined('THEMES_URI') or define('THEMES_URI', URI_PUBLIC.'public/themes/');
        defined('ICONS_URI') or define('ICONS_URI', URI_PUBLIC.'wolf/icons/');
    }
    
    public static function securityCheckConfigFile($config_file) {
        if (isWritable($config_file)) {
            $lock = false;
            // Windows systems always have writable config files... skip those.
            if (function_exists('posix_getuid') && function_exists('fileowner') &&
            		function_exists('fileperms') && substr(PHP_OS, 0, 3) != 'WIN') {
            
                $perms = fileperms($config_file);
                
                // Is file owned by http server and does it have write permissions?
                if (fileowner($config_file) == posix_getuid() && ($perms & 0200)) {
                    $lock = true;
                }
                
                // Does the group or the world have write permissions?
                if ($perms & 0022) {
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
    }
    
    public static function dbConnection() {
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
        $GLOBALS['__FROG_CONN__'] = $__FROG_CONN__; 
        $GLOBALS['__CMS_CONN__'] = $__CMS_CONN__;
        
        Record::connection($__CMS_CONN__);
        Record::getConnection()->exec("set names 'utf8'");
    }
    
    public static function poorMansCron() {
        // Only add the cron web bug when necessary
        if (defined('USE_POORMANSCRON') && USE_POORMANSCRON && defined('POORMANSCRON_INTERVAL')) {
            Observer::observe('page_before_execute_layout', 'run_cron');
            
            function run_cron() {
                $cron = Cron::findByIdFrom('Cron', '1');
                
                if (time() - $cron->getLastRunTime() > POORMANSCRON_INTERVAL) {
                    echo $cron->generateWebBug();
                }
            }
        }
    }
    
    public static function adminRoutes() {
        // Setup admin routes
        $admin_routes = array (
        		'/'.ADMIN_DIR         => Setting::get('default_tab'),
        		'/'.ADMIN_DIR.'/'     => Setting::get('default_tab'),
        		'/'.ADMIN_DIR.'/:all' => '$1',
        );
    
        Dispatcher::addRoute($admin_routes);
    }
}