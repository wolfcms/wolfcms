<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2014 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The main framework belonging to Wolf CMS.
 *
 * The Framework and its associated framework.php file is a customized version
 * of an early pre-release version of the so-called "Green Framework".
 *
 * However, a lot of changes have been made and the two are no longer on a
 * similar development path.
 *
 * LICENSE: see license.txt and exception.txt for the full license texts.
 *
 * @package    org.wolfcms.core
 * @subpackage framework
 *
 * @author     Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author     Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright  2009-2014 Martijn van der Kleijn
 * @copyright  2008 Philippe Archambault
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

define('FRAMEWORK_STARTING_MICROTIME', get_microtime());

// All constants that can be defined before customizing your framework
if (!defined('DEBUG'))              define('DEBUG', false);

if (!defined('CORE_ROOT'))          define('CORE_ROOT',   dirname(__FILE__));

// Turn on experimental XSS filtering?
if (defined('GLOBAL_XSS_FILTERING') && GLOBAL_XSS_FILTERING) {
    cleanXSS();
}

if (!defined('APP_PATH'))           define('APP_PATH',    CORE_ROOT.DIRECTORY_SEPARATOR.'app');
if (!defined('HELPER_PATH'))        define('HELPER_PATH', CORE_ROOT.DIRECTORY_SEPARATOR.'helpers');

if (!defined('BASE_URL'))           define('BASE_URL', 'http://'.dirname($_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']) .'/?/');

if (!defined('DEFAULT_CONTROLLER')) define('DEFAULT_CONTROLLER', 'index');
if (!defined('DEFAULT_ACTION'))     define('DEFAULT_ACTION', 'index');

// Setting error display depending on debug mode or not
error_reporting((DEBUG ? (E_ALL | E_STRICT) : 0));

// No more quotes escaped with a backslash
if (PHP_VERSION < 5.3)
    set_magic_quotes_runtime(0);

if ( ! isset($_SESSION))
    session_start();

if ( ! isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

ini_set('date.timezone', DEFAULT_TIMEZONE);
if(function_exists('date_default_timezone_set'))
    date_default_timezone_set(DEFAULT_TIMEZONE);
else
    putenv('TZ='.DEFAULT_TIMEZONE);

/**
 * The Dispatcher class is responsible for mapping urls/routes to Controller methods.
 *
 * Each route that has the same number of directory components as the current
 * requested url is tried, and the first method that returns a response with a
 * non false/non null value will be returned via the Dispatcher::dispatch() method.
 *
 * For example:
 *
 * A route string can be a literal path such as '/pages/about' or can contain
 * wildcards (:any or :num) and/or regex like '/blog/:num' or '/page/:any'.
 *
 * <code>
 * Dispatcher::addRoute(array(
 *      '/' => 'page/index',
 *      '/about' => 'page/about,
 *      '/blog/:num' => 'blog/post/$1',
 *      '/blog/:num/comment/:num/delete' => 'blog/deleteComment/$1/$2'
 * ));
 * </code>
 *
 * Visiting /about/ would call PageController::about(),
 * visiting /blog/5 would call BlogController::post(5)
 * visiting /blog/5/comment/42/delete would call BlogController::deleteComment(5,42)
 *
 * The dispatcher is used by calling Dispatcher::addRoute() to setup the route(s),
 * and Dispatcher::dispatch() to handle the current request and get a response.
 */
final class Dispatcher {
    private static $routes = array();
    private static $params = array();
    private static $status = array();
    private static $requested_url = '';

    /**
     * Adds a route.
     *
     * @param string $route         A route string.
     * @param string $destination   Path that the request should be sent to.
     */
    public static function addRoute($route, $destination=null) {
        if ($destination != null && !is_array($route)) {
            $route = array($route => $destination);
        }
        self::$routes = array_merge(self::$routes, $route);
    }

    /**
     * Checks if a route exists for a specified path.
     *
     * @param string $path      A path (for instance path/to/page)
     * @return boolean          Returns true when a route was found, otherwise false.
     */
    public static function hasRoute($requested_url) {
        if (!self::$routes || count(self::$routes) == 0) {
            return false;
        }
        
        // Make sure we strip trailing slashes in the requested url
        $requested_url = rtrim($requested_url, '/');

        foreach (self::$routes as $route => $action) {
            // Convert wildcards to regex
            if (strpos($route, ':') !== false) {
                $route = str_replace(':any', '([^/]+)', str_replace(':num', '([0-9]+)', str_replace(':all', '(.+)', $route)));
            }

            // Does the regex match?
            if (preg_match('#^'.$route.'$#', $requested_url)) {
                // Do we have a back-reference?
                if (strpos($action, '$') !== false && strpos($route, '(') !== false) {
                    $action = preg_replace('#^'.$route.'$#', $action, $requested_url);
                }
                self::$params = self::splitUrl($action);
                // We found it, so we can break the loop now!
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a URL into an array of its components.
     *
     * @param string $url   A URL.
     * @return array        An array of URL components.
     */
    public static function splitUrl($url) {
        return preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Handles the request for a URL and provides a response.
     *
     * @param string $requested_url The URL that was requested.
     * @param string $default       Default URL to access if now URL was requested.
     * @return string               A response.
     */
    public static function dispatch($requested_url = null, $default = null) {
        Flash::init();

        // If no url passed, we will get the first key from the _GET array
        // that way, index.php?/controller/action/var1&email=example@example.com
        // requested_url will be equal to: /controller/action/var1
        if ($requested_url === null) {
            $pos = strpos($_SERVER['QUERY_STRING'], '&');
            if ($pos !== false) {
                $requested_url = substr($_SERVER['QUERY_STRING'], 0, $pos);
            } else {
                $requested_url = $_SERVER['QUERY_STRING'];
            }
        }

        // If no URL is requested (due to someone accessing admin section for the first time)
        // AND $default is set. Allow for a default tab.
        if ($requested_url == null && $default != null) {
            $requested_url = $default;
        }

        // Requested url MUST start with a slash (for route convention)
        if (strpos($requested_url, '/') !== 0) {
            $requested_url = '/' . $requested_url;
        }
        
        // Make sure we strip trailing slashes in the requested url
        $requested_url = rtrim($requested_url, '/');

        self::$requested_url = $requested_url;

        // This is only trace for debugging
        self::$status['requested_url'] = $requested_url;

        // Make the first split of the current requested_url
        self::$params = self::splitUrl($requested_url);

        // Do we even have any custom routing to deal with?
        if (count(self::$routes) === 0) {
            return self::executeAction(self::getController(), self::getAction(), self::getParams());
        }

        // Is there a literal match? If so we're done
        if (isset(self::$routes[$requested_url])) {
            self::$params = self::splitUrl(self::$routes[$requested_url]);
            return self::executeAction(self::getController(), self::getAction(), self::getParams());
        }

        // Loop through the route array looking for wildcards
        foreach (self::$routes as $route => $action) {
        // Convert wildcards to regex
            if (strpos($route, ':') !== false) {
                $route = str_replace(':any', '([^/]+)', str_replace(':num', '([0-9]+)', str_replace(':all', '(.+)', $route)));
            }
            // Does the regex match?
            if (preg_match('#^'.$route.'$#', $requested_url)) {
            // Do we have a back-reference?
                if (strpos($action, '$') !== false && strpos($route, '(') !== false) {
                    $action = preg_replace('#^'.$route.'$#', $action, $requested_url);
                }
                self::$params = self::splitUrl($action);
                // We found it, so we can break the loop now!
                break;
            }
        }

        return self::executeAction(self::getController(), self::getAction(), self::getParams());
    } // Dispatch

    /**
     * Returns the currently requested URL.
     *
     * @return string The currently requested URL.
     */
    public static function getCurrentUrl() {
        return self::$requested_url;
    }

    /**
     * Returns a reference to a controller class.
     *
     * @return string Reference to controller.
     */
    public static function getController() {
        // Check for settable default controller
        // if it's a plugin and not activated, revert to Wolf hardcoded default
        if (isset(self::$params[0]) && self::$params[0] == 'plugin' ) {
            $loaded_plugins = Plugin::$plugins;
            if (count(self::$params) < 2) {
                unset(self::$params[0]);
            } elseif (isset(self::$params[1]) && !isset($loaded_plugins[self::$params[1]])) {
                unset(self::$params[0]);
                unset(self::$params[1]);
            }
        }

        return isset(self::$params[0]) ? self::$params[0]: DEFAULT_CONTROLLER;
    }

    /**
     * Returns the action that was requested from a controller.
     *
     * @return string Reference to a controller's action.
     */
    public static function getAction() {
        return isset(self::$params[1]) ? self::$params[1]: DEFAULT_ACTION;
    }

    /**
     * Returns an array of parameters that should be passed to an action.
     *
     * @return array The action's parameters.
     */
    public static function getParams() {
        return array_slice(self::$params, 2);
    }

    /**
     * ???
     * 
     * @todo Finish docblock
     *
     * @param <type> $key
     * @return <type> 
     */
    public static function getStatus($key=null) {
        return ($key === null) ? self::$status: (isset(self::$status[$key]) ? self::$status[$key]: null);
    }

    /**
     * Executes a specified action for a specified controller class.
     *
     * @param string $controller
     * @param string $action
     * @param array $params 
     */
    public static function executeAction($controller, $action, $params) {
        self::$status['controller'] = $controller;
        self::$status['action'] = $action;
        self::$status['params'] = implode(', ', $params);

        $controller_class = Inflector::camelize($controller);
        $controller_class_name = $controller_class . 'Controller';

        // Get an instance of that controller
        if (class_exists($controller_class_name)) {
            $controller = new $controller_class_name();
        } else {
        }
        if ( ! $controller instanceof Controller) {
            throw new Exception("Class '{$controller_class_name}' does not extends Controller class!");
        }

        // Execute the action
        $controller->execute($action, $params);
    }

} // end Dispatcher class


/**
 * The Record class represents a single database record.
 *
 * It is used as an abstraction layer so classes don't need to implement their
 * own database functionality.
 */
class Record {
    const PARAM_BOOL = 5;
    const PARAM_NULL = 0;
    const PARAM_INT = 1;
    const PARAM_STR = 2;
    const PARAM_LOB = 3;
    const PARAM_STMT = 4;
    const PARAM_INPUT_OUTPUT = -2147483648;
    const PARAM_EVT_ALLOC = 0;
    const PARAM_EVT_FREE = 1;
    const PARAM_EVT_EXEC_PRE = 2;
    const PARAM_EVT_EXEC_POST = 3;
    const PARAM_EVT_FETCH_PRE = 4;
    const PARAM_EVT_FETCH_POST = 5;
    const PARAM_EVT_NORMALIZE = 6;

    const FETCH_LAZY = 1;
    const FETCH_ASSOC = 2;
    const FETCH_NUM = 3;
    const FETCH_BOTH = 4;
    const FETCH_OBJ = 5;
    const FETCH_BOUND = 6;
    const FETCH_COLUMN = 7;
    const FETCH_CLASS = 8;
    const FETCH_INTO = 9;
    const FETCH_FUNC = 10;
    const FETCH_GROUP = 65536;
    const FETCH_UNIQUE = 196608;
    const FETCH_CLASSTYPE = 262144;
    const FETCH_SERIALIZE = 524288;
    const FETCH_PROPS_LATE = 1048576;
    const FETCH_NAMED = 11;

    const ATTR_AUTOCOMMIT = 0;
    const ATTR_PREFETCH = 1;
    const ATTR_TIMEOUT = 2;
    const ATTR_ERRMODE = 3;
    const ATTR_SERVER_VERSION = 4;
    const ATTR_CLIENT_VERSION = 5;
    const ATTR_SERVER_INFO = 6;
    const ATTR_CONNECTION_STATUS = 7;
    const ATTR_CASE = 8;
    const ATTR_CURSOR_NAME = 9;
    const ATTR_CURSOR = 10;
    const ATTR_ORACLE_NULLS = 11;
    const ATTR_PERSISTENT = 12;
    const ATTR_STATEMENT_CLASS = 13;
    const ATTR_FETCH_TABLE_NAMES = 14;
    const ATTR_FETCH_CATALOG_NAMES = 15;
    const ATTR_DRIVER_NAME = 16;
    const ATTR_STRINGIFY_FETCHES = 17;
    const ATTR_MAX_COLUMN_LEN = 18;
    const ATTR_EMULATE_PREPARES = 20;
    const ATTR_DEFAULT_FETCH_MODE = 19;

    const ERRMODE_SILENT = 0;
    const ERRMODE_WARNING = 1;
    const ERRMODE_EXCEPTION = 2;
    const CASE_NATURAL = 0;
    const CASE_LOWER = 2;
    const CASE_UPPER = 1;
    const NULL_NATURAL = 0;
    const NULL_EMPTY_STRING = 1;
    const NULL_TO_STRING = 2;
    const ERR_NONE = '00000';
    const FETCH_ORI_NEXT = 0;
    const FETCH_ORI_PRIOR = 1;
    const FETCH_ORI_FIRST = 2;
    const FETCH_ORI_LAST = 3;
    const FETCH_ORI_ABS = 4;
    const FETCH_ORI_REL = 5;
    const CURSOR_FWDONLY = 0;
    const CURSOR_SCROLL = 1;
    const MYSQL_ATTR_USE_BUFFERED_QUERY = 1000;
    const MYSQL_ATTR_LOCAL_INFILE = 1001;
    const MYSQL_ATTR_INIT_COMMAND = 1002;
    const MYSQL_ATTR_READ_DEFAULT_FILE = 1003;
    const MYSQL_ATTR_READ_DEFAULT_GROUP = 1004;
    const MYSQL_ATTR_MAX_BUFFER_SIZE = 1005;
    const MYSQL_ATTR_DIRECT_QUERY = 1006;

    public static $__CONN__ = false;
    public static $__QUERIES__ = array();

    /**
     * Sets a static reference for the connection to the database.
     *
     * @param <type> $connection 
     */
    final public static function connection($connection) {
        self::$__CONN__ = $connection;
    }

    /**
     * Returns a reference to a database connection.
     *
     * @return <type>
     */
    final public static function getConnection() {
        return self::$__CONN__;
    }

    /**
     * Logs an SQL query.
     *
     * @param string $sql SQL query string.
     */
    final public static function logQuery($sql) {
        self::$__QUERIES__[] = $sql;
    }

    /**
     * Retrieves all logged queries.
     *
     * @return array An array of queries.
     */
    final public static function getQueryLog() {
        return self::$__QUERIES__;
    }

    /**
     * Returns the number of logged queries.
     *
     * @return int Number of logged queries.
     */
    final public static function getQueryCount() {
        return count(self::$__QUERIES__);
    }

    /**
     * Executes an SQL query.
     *
     * @param string $sql   SQL query to execute.
     * @param array $values Values belonging to the SQL query if its a prepared statement.
     * @return <type>       An array of objects, PDOStatement object or FALSE on failure.
     */
    final public static function query($sql, $values=false) {
        self::logQuery($sql);

        if (is_array($values)) {
            $stmt = self::$__CONN__->prepare($sql);
            $stmt->execute($values);
            return $stmt->fetchAll(self::FETCH_OBJ);
        } else {
            return self::$__CONN__->query($sql);
        }
    }

    /**
     * Returns a database table name.
     * 
     * The name that is returned is based on the classname or on the TABLE_NAME
     * constant in that class if that constant exists.
     *
     * @param string $class_name
     * @return string Database table name.
     */
    final public static function tableNameFromClassName($class_name) {
        try {
            if (class_exists($class_name) && defined($class_name.'::TABLE_NAME'))
                return TABLE_PREFIX.constant($class_name.'::TABLE_NAME');
        }
        catch (Exception $e) {
            return TABLE_PREFIX.Inflector::underscore($class_name);
        }
    }

    /**
     * Escapes quotes in a query string.
     *
     * @param string $value The query string to escape.
     * @return string       The escaped string.
     */
    final public static function escape($value) {
        return self::$__CONN__->quote($value);
    }

    /**
     * Retrieves the autogenerated primary key for the last inserted record.
     *
     * @return string A key.
     */
    final public static function lastInsertId() {
        // PostgreSQL does not support lastInsertId retrieval without knowing the sequence name
        if (self::$__CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql = 'SELECT lastval();';
            
            if ($result = self::$__CONN__->query($sql)) {
                return $result->fetchColumn();
            }
            else {
                return 0;
            }
        }
        
        return self::$__CONN__->lastInsertId();
    }

    /**
     * Constructor for the Record class.
     *
     * If the $data parameter is given and is an array, the constructor sets
     * the class's variables based on the key=>value pairs found in the array.
     *
     * @param array $data An array of key,value pairs.
     */
    public function __construct($data=false) {
        if (is_array($data)) {
            $this->setFromData($data);
        }
    }

    /**
     * Sets the class's variables based on the key=>value pairs in the given array.
     *
     * @param array $data An array of key,value pairs.
     */
    public function setFromData($data) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Generates an insert or update string from the supplied data and executes it
     *
     * @return boolean True when the insert or update succeeded.
     */
    public function save() {
        if ( ! $this->beforeSave()) return false;

        $value_of = array();

        if (empty($this->id)) {

            if ( ! $this->beforeInsert()) return false;

            $columns = $this->getColumns();

            // Escape and format for SQL insert query
            // @todo check if we like this new method of escaping and defaulting
            foreach ($columns as $column) {
                // Make sure we don't try to add "id" field;
                if ($column === 'id') {
                    continue;
                }
                
                if (!empty($this->$column) || is_numeric($this->$column)) { // Do include 0 as value
                    $value_of[$column] = self::$__CONN__->quote($this->$column);
                }
                elseif (isset($this->$column)) { // Properly fallback to the default column value instead of relying on an empty string
                    // SQLite can't handle the DEFAULT value
                    if (self::$__CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) != 'sqlite') {
                        $value_of[$column] = 'DEFAULT';
                    }
                }
            }

            $sql = 'INSERT INTO '.self::tableNameFromClassName(get_class($this)).' ('
                . implode(', ', array_keys($value_of)).') VALUES ('.implode(', ', array_values($value_of)).')';
            $return = self::$__CONN__->exec($sql) !== false;
            $this->id = self::lastInsertId();

            if ( ! $this->afterInsert()) return false;

        } else {

            if ( ! $this->beforeUpdate()) return false;

            $columns = $this->getColumns();

            // Escape and format for SQL update query
            foreach ($columns as $column) {
                if (!empty($this->$column) || is_numeric($this->$column)) { // Do include 0 as value
                    $value_of[$column] = $column.'='.self::$__CONN__->quote($this->$column);
                }
                elseif (isset($this->$column)) { // Properly fallback to the default column value instead of relying on an empty string
                    // SQLite can't handle the DEFAULT value
                    if (self::$__CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) != 'sqlite') {
                        $value_of[$column] = $column.'=DEFAULT';
                    }
					else{
						//Since DEFAULT values don't work in SQLite empty strings should be passed explicitly
						$value_of[$column] = $column."=''";
					}
                }
            }

            unset($value_of['id']);

            $sql = 'UPDATE '.self::tableNameFromClassName(get_class($this)).' SET '
                . implode(', ', $value_of).' WHERE id = '.$this->id;
            $return = self::$__CONN__->exec($sql) !== false;

            if ( ! $this->afterUpdate()) return false;
        }

        self::logQuery($sql);

        if ( ! $this->afterSave()) return false;

        return $return;
    }

    /**
     * Generates a delete string and executes it.
     *
     * @param string $table The table name.
     * @param string $where The query condition.
     * @return boolean      True if delete was successful.
     */
    public function delete() {
        if ( ! $this->beforeDelete()) return false;
        $sql = 'DELETE FROM '.self::tableNameFromClassName(get_class($this))
            . ' WHERE id='.self::$__CONN__->quote($this->id);

        // Run it !!...
        $return = self::$__CONN__->exec($sql) !== false;
        if ( ! $this->afterDelete()) {
            $this->save();
            return false;
        }

        self::logQuery($sql);

        return $return;
    }

    /**
     * Allows sub-classes do stuff before a Record is saved.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeSave() { return true; }

    /**
     * Allows sub-classes do stuff before a Record is inserted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeInsert() { return true; }

    /**
     * Allows sub-classes do stuff before a Record is updated.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeUpdate() { return true; }

    /**
     * Allows sub-classes do stuff before a Record is deleted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function beforeDelete() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is saved.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterSave() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is inserted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterInsert() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is updated.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterUpdate() { return true; }

    /**
     * Allows sub-classes do stuff after a Record is deleted.
     *
     * @return boolean True if the actions succeeded.
     */
    public function afterDelete() { return true; }

    /**
     * Returns an array of all columns in the table.
     *
     * It is a good idea to rewrite this method in all your model classes.
     * This function is used in save() for creating the insert and/or update
     * sql query.
     */
    public function getColumns() {
        return array_keys(get_object_vars($this));
    }

    /**
     * Inserts a record into the database.
     *
     * @param string $class_name    The classname of the record that should be inserted.
     * @param array $data           An array of key/value pairs to be inserted.
     * @return boolean              Returns true when successful.
     */
    public static function insert($class_name, $data) {
        $keys = array();
        $values = array();

        foreach ($data as $key => $value) {
            $keys[] = $key;
            $values[] = self::$__CONN__->quote($value);
        }

        $sql = 'INSERT INTO '.self::tableNameFromClassName($class_name).' ('.join(', ', $keys).') VALUES ('.join(', ', $values).')';

        self::logQuery($sql);

        // Run it !!...
        return self::$__CONN__->exec($sql) !== false;
    }

    /**
     * Updates an existing record in the database.
     *
     * @param string $class_name    The classname of the record to be updated.
     * @param array $data           An array of key/value pairs to be updated.
     * @param string $where         An SQL WHERE clause to specify a specific record.
     * @param array $values         An array of values if this is a prepared statement.
     * @return <type>
     */
    public static function update($class_name, $data, $where, $values=array()) {
        $setters = array();

        // Prepare request by binding keys
        foreach ($data as $key => $value) {
            $setters[] = $key.'='.self::$__CONN__->quote($value);
        }

        $sql = 'UPDATE '.self::tableNameFromClassName($class_name).' SET '.join(', ', $setters).' WHERE '.$where;

        self::logQuery($sql);

        $stmt = self::$__CONN__->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Deletes a specified records from the database.
     *
     * @param string $class_name    The classname for the record to be deleted.
     * @param string $where         An SQL WHERE clause to specify a specific record.
     * @param array $values         An array of values if this is a prepared statement.
     * @return boolean              True when the delete was successful.
     */
    public static function deleteWhere($class_name, $where, $values=array()) {
        $sql = 'DELETE FROM '.self::tableNameFromClassName($class_name).' WHERE '.$where;

        self::logQuery($sql);

        $stmt = self::$__CONN__->prepare($sql);
        return $stmt->execute($values);
    }


    /**
     * Returns true if a record exists in the database.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to specify a subset if desired.
     * @param array $values         An array of values if this is a prepared statement.
     * @return boolean              TRUE if record exists, FALSE if it doesn't.
     */
	public static function existsIn($class_name, $where=false, $values=array()) {
		$sql = 'SELECT EXISTS(SELECT 1 FROM '.self::tableNameFromClassName($class_name).($where ? ' WHERE '.$where:'').' LIMIT 1)';

        $stmt = self::$__CONN__->prepare($sql);
		$stmt->execute($values);

        self::logQuery($sql);

        return (bool) $stmt->fetchColumn();
	}


    /**
     * Returns a single class instance or an array of instances.
     * 
     * This method guarantees sane defaults, making the `options` argument
     * optional. It is important to note however, that when using prepared
     * statements with placeholders in for example the `WHERE` clause, the
     * `values` option is mandatory.
     * 
     * Valid options are: 'select', 'where', 'group_by', 'having', 'order_by', 'limit', 'offset', 'values'
     * 
     * Example usage:
     * <code>
     * // Note that MyClass extends Record 
     * $object = MyClass::find(array(
     *     'select'     => 'column1, column2',
     *     'where'      => 'id = ? and slug = ?',
     *     'group_by'   => 'column2',
     *     'having'     => 'column2 = ?',
     *     'order_by'   => 'column3 ASC',
     *     'limit'      => 10,
     *     'offset'     => 20,
     *     'values'     => array($id, $slug, 'some-value-for-having-clause')
     * ));
     * </code>
     * 
     * @param   array   $options    Array of options for the query.
     * @return  mixed               Single object, array of objects or false on failure.
     * 
     * @todo    Decide if we'll keep the from and joins options since they clash heavily with the one Record == one DB tuple idea.
     */
    public static function find($options = array()) {
        // @todo Replace by InvalidArgumentException if not array based on logger decision.
        $options = (is_null($options)) ? array() : $options;
        
        $class_name = get_called_class();
        $table_name = self::tableNameFromClassName($class_name);
        
        // Collect attributes
        $ses    = isset($options['select']) ? trim($options['select'])   : '';
        $frs    = isset($options['from'])   ? trim($options['from'])     : '';
        $jos    = isset($options['joins'])  ? trim($options['joins'])    : '';       
        $whs    = isset($options['where'])  ? trim($options['where'])    : '';
        $gbs    = isset($options['group'])  ? trim($options['group'])    : '';
        $has    = isset($options['having']) ? trim($options['having'])   : '';
        $obs    = isset($options['order'])  ? trim($options['order'])    : '';
        $lis    = isset($options['limit'])  ? (int) $options['limit']    : 0;
        $ofs    = isset($options['offset']) ? (int) $options['offset']   : 0;
        $values = isset($options['values']) ? (array) $options['values'] : array();

        // Asked for single Record?
        $single = ($lis === 1) ? true : false;
        
        // Prepare query parts
        $select      = empty($ses) ? 'SELECT *'         : "SELECT $ses";
        $from        = empty($frs) ? "FROM $table_name" : "FROM $frs";
        $joins       = empty($jos) ? ''                 : $jos;
        $where       = empty($whs) ? ''                 : "WHERE $whs";
        $group_by    = empty($gbs) ? ''                 : "GROUP BY $gbs";
        $having      = empty($has) ? ''                 : "HAVING $has";
        $order_by    = empty($obs) ? ''                 : "ORDER BY $obs";
        $limit       = $lis > 0    ? "LIMIT $lis"       : '';
        $offset      = $ofs > 0    ? "OFFSET $ofs"      : '';
        
        // Build the query
        $sql = "$select $from $joins $where $group_by $having $order_by $limit $offset";

        // Run query
        $objects = self::findBySql($sql, $values);
        
        return ($single) ? (!empty($objects) ? $objects[0] : false) : $objects;
    }
    
    private static function findBySql($sql, $values = null) {
        $class_name = get_called_class();
        
        self::logQuery($sql);
        
        // Prepare and execute
        $stmt = self::getConnection()->prepare($sql);
        if (!$stmt->execute($values)) {
            return false;
        }
        
        $objects = array();
        while ($object = $stmt->fetchObject($class_name)) {
            $objects[] = $object;
        }
        
        return $objects;
    }
    
    /**
     * Returns a record based on it's id.
     * 
     * Default method so that you don't have to create one for every model you write.
     * Can of course be overwritten by a custom findById() method (for instance when you want to include another model)
     * 
     * @param int $id       Object's id
     * @return              Single object
     */
    public static function findById($id) {
        return self::findOne(array(
            'where'  => 'id = :id',
            'values' => array(':id' => (int) $id)
        ));
    }
        
    //
    // Note: lazy finder or getter method. Pratical when you need something really
    //       simple no join or anything will only generate simple select * from table ...
    //

    /**
     * Returns a single Record class instance from the database based on ID.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $id            The ID of the record to be found.
     * @return Record               A record instance or false on failure.
     */
    public static function findByIdFrom($class_name, $id) {
        return $class_name::findById($id);
    }

    /**
     * Returns a single object, retrieved from the database.
     * 
     * @param array $options        Options array containing parameters for the query
     * @return                      Single object
     */
    public static function findOne($options = array()) {
        $options['limit'] = 1;
        return self::find($options);
    }

    /**
     * Returns a single Record class instance.
     *
     * The instance is retrieved from the database based on a specified field's
     * value.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to find a specific record.
     * @param array $values         An array of values if this is a prepared statement.
     * @return Record               A record instance or false on failure.
     */
    public static function findOneFrom($class_name, $where, $values=array()) {
        return $class_name::findOne(array(
            'where'  => $where,
            'values' => $values
        ));
    }

    /**
     * Returns an array of Record instances.
     * 
     * Retrieves all records, or a subset thereof if the $where parameter is
     * used, for a specific database table.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to specify a subset if desired.
     * @param array $values         An array of values if this is a prepared statement.
     * @return array                An array of Records instances.
     */
    public static function findAllFrom($class_name, $where=false, $values=array()) {
        if ($where) {
            return $class_name::find(array(
                'where'  => $where,
                'values' => $values
            ));
        } else {
            return $class_name::find();
        }
    }

    /**
     * Returns the number of records.
     * 
     * Returns a total of all records in the specified database table or a count
     * for a specified subset thereof.
     *
     * @param string $class_name    The classname to be returned.
     * @param string $where         An SQL WHERE clause to specify a subset if desired.
     * @param array $values         An array of values if this is a prepared statement.
     * @return int                  The number of records in the table or a subset thereof.
     */
    public static function countFrom($class_name, $where=false, $values=array()) {
        $sql = 'SELECT COUNT(*) AS nb_rows FROM '.self::tableNameFromClassName($class_name).($where ? ' WHERE '.$where:'');

        $stmt = self::$__CONN__->prepare($sql);
        $stmt->execute($values);

        self::logQuery($sql);

        return (int) $stmt->fetchColumn();
    }

}
/* end Record */


/**
 * Abstract class that allows models to easily implement find..By.. methods.
 * 
 * By extending the `Finder` abstract class, users of an extending model can
 * make use of simple find.. and find..By.. methods without having to implement
 * them in the actual model.
 * 
 * Example usage:
 * 
 * <code>
 * class MyModel extends Finder {
 *     // code as if extending Record
 * }
 * 
 * $object  = MyModel::findOneById(2);
 * $objects = MyModel::findAll();
 * </code>
 * 
 * Users may consider these methods as generated wrappers around Record::find().
 * 
 * Non-trivial example:
 * 
 * <code>
 * // find users with same name
 * $objects = MyModel::findIdNameEmailByNameOrderedByIdAsc('mike');
 * </code>
 * 
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright (c) 2014, Martijn van der Kleijn.
 */
abstract class Finder extends Record {
    
    // Reserved keywords that can be used to construct a find method.
    private static $reserved = array(
        'all',
        'one',
        'by',
        'and',
        'ordered'
    );
    
    /**
     * Adds `SELECT` entry and passes on control to next find_* method.
     * 
     * @param   array $commands         Array of tokens based on called virtual find.. method.
     * @param   array $options          Array of options, being built up for virtual find.. method.
     * @throws  BadMethodCallException  On failing to pass on to valid find_* method.
     */
    private static function find_all(&$commands, &$options = array()) {
        $options['select'][] = '*';

        if (is_array($commands) && !empty($commands)) {
            if (in_array($commands[0], self::$reserved)) {
                $cmd = 'find_'.$commands[0];
                self::$cmd(array_splice($commands, 1), $options);
            }
            else {
                throw new BadMethodCallException("Unknown find method including ${$commands[0]}.");
            }
        }
    }

    /**
     * Adds `SELECT` entry with a `LIMIT` of one and passes on control to next find_* method.
     * 
     * @param   array $commands         Array of tokens based on called virtual find.. method.
     * @param   array $options          Array of options, being built up for virtual find.. method.
     * @throws  BadMethodCallException  On failing to pass on to valid find_* method.
     */
    private static function find_one(&$commands, &$options = array()) {
        $options['select'][] = '*';
        $options['limit'][] = 1;
        
        if (is_array($commands) && !empty($commands)) {
            $cmd = array_shift($commands);
            
            if (in_array($cmd, self::$reserved)) {
                $cmd = 'find_'.$cmd;
                self::$cmd($commands, $options);
            }
            else {
                throw new BadMethodCallException("Unknown find method including $cmd.");
            }
        }
    }

    /**
     * Adds `ORDER BY` entry and passes on control to next find_* method.
     * 
     * @param   array $commands         Array of tokens based on called virtual find.. method.
     * @param   array $options          Array of options, being built up for virtual find.. method.
     * @throws  BadMethodCallException  On call to incorrectly named virtual find.. method.
     */
    private static function find_ordered(&$commands, &$options = array()) {
        if (is_array($commands) && !empty($commands) && array_shift($commands) == 'by') {
            $cmd = array_shift($commands);
            
            if (in_array($cmd, self::$reserved)) {
                $cmd = 'find_'.$cmd;
                self::$cmd($commands, $options);
            }
            else {
                if (count($commands) > 0 && ($commands[0] == 'asc' || $commands[0] == 'desc')) {
                    $cmd .= ' '.array_shift($commands);
                }
                $options['order'][] = $cmd;
            }
        }
        else {
            throw new BadMethodCallException();
        }
    }

    /**
     * Adds `AND` entry and passes on control to next find_* method.
     * 
     * @param   array $commands     Array of tokens based on called virtual find.. method.
     * @param   array $options      Array of options, being built up for virtual find.. method.
     */
    private static function find_and(&$commands, &$options = array()) {
        if (is_array($commands) && !empty($commands)) {
            if (in_array($commands[0], self::$reserved)) {
                $cmd = 'find_'.$commands[0];
                self::$cmd(array_splice($commands, 1), $options);
            }
            else {
                $options['where'][] = "${commands[0]}=?";
            }
        }
    }
    
    /**
     * Adds `WHERE` entry and passes on control to next find_* method.
     * 
     * @param   array $commands     Array of tokens based on called virtual find.. method.
     * @param   array $options      Array of options, being built up for virtual find.. method.
     */
    private static function find_by(&$commands, &$options = array()) {
        for ($i=0; $i<count($commands); $i++) {
            if (!in_array($commands[$i], self::$reserved)) {
                $options['where'][] = "${commands[$i]}=?";
            }
            else {
                $cmd = 'find_'.$commands[$i];
                self::$cmd(array_splice($commands, $i+1), $options);
            }
        }
    }
    
    /**
     * Implements a virtual find.. or find..By.. method.
     * 
     * Note: this is, of course, automatically called.
     * 
     * @param string    $name       Name of virtual find.. method.
     * @param array     $arguments  Array of arguments given to virtual find.. method.
     * @ignore
     */
    public static function __callStatic($name, $arguments) {
        // Options array to later pass on to Record::find()
        $options = array(
            'select' => array(),
            'where'  => array(),
            'order'  => array(),
            'limit'  => array(),
            'offset' => array()
        );
        
        // Check if this is a correct find.. or find..By.. method call.
        preg_match("/^find[A-Z][a-z]+.*/", $name, $matches);
        if ( empty($matches) ) {
            // Its not, try our parent.
            parent::__callStatic($name, $arguments);
        }

        // Match the virtual method's name and lowercase entries.
        preg_match_all("/([A-Z][a-z]+)/", $name, $matches);
        $matches = array_map('strtolower', $matches[1]);
        
        // Run through matches and try to fire subcommands.
        for($i = 0; $i < count($matches); $i++) {
            $entry = array_shift($matches);
            
            // If its not a reserved name, assume its a field name and add to SELECT.
            if (!in_array($entry, self::$reserved)) {
                $options['select'][] = $entry;
            }
            else {
                $cmd = 'find_'.$entry;
                self::$cmd($matches, $options);
            }
        }
        
        // Prep options for Record::find()
        $options['select'] = implode(','     , $options['select']);
        $options['where']  = implode(' AND ' , $options['where']);
        $options['order']  = implode(','     , $options['order']);
        $options['limit']  = (int) implode('', $options['limit']);
        $options['offset'] = (int) implode('', $options['offset']);
        $options['values'] = $arguments;

        // Run options through Record::find()
        return self::find($options);
    }
}
/* end Finder */

/**
 * The View class is used to generate output based on a template.
 *
 * The class takes a template file after which you can assign properties to the
 * template. These properties become available as local variables in the
 * template.
 * 
 * You can then call the display() method to get the output of the template,
 * or just call print on the template directly thanks to PHP 5's __toString()
 * magic method.
 *
 * Usage example:
 * 
 * <code>
 * echo new View('my_template',array(
 *               'title' => 'My Title',
 *               'body' => 'My body content'
 *              ));
 * </code>
 * 
 * Template file example (in this case my_template.php):
 * 
 * <code>
 * <html>
 * <head>
 *   <title><?php echo $title;?></title>
 * </head>
 * <body>
 *   <h1><?php echo $title;?></h1>
 *   <p><?php echo $body;?></p>
 * </body>
 * </html>
 * </code>
 * You can also use Helpers in the template by loading them as follows:
 * 
 * <code>
 * use_helper('HelperName', 'OtherHelperName');
 * </code>
 */
class View {
    private $file;           // String of template file
    private $vars = array(); // Array of template variables

    /**
     * Constructor for the View class.
     *
     * The class constructor has one mandatory parameter ($file) which is the
     * path to a template file and one optional paramater ($vars) which allows
     * you to make local variables available in the template.
     *
     * The View class automatically adds ".php" to the $file argument.
     *
     * @param string $file  Absolute path or path relative to the templates dir.
     * @param array $vars   Array of key/value pairs to be made available in the template.
     */
    public function __construct($file, $vars=false) {
        if (strpos($file, '/') === 0 || strpos($file, ':') === 1) {
            $this->file = $file.'.php';
        }
        else {
            $this->file = APP_PATH.'/views/'.ltrim($file, '/').'.php';
        }

        if ( ! file_exists($this->file)) {
            throw new Exception("View '{$this->file}' not found!");
        }

        if ($vars !== false) {
            $this->vars = $vars;
        }
    }

    /**
     * Assigns a specific variable to the template.
     *
     * @param mixed $name   Variable name.
     * @param mixed $value  Variable value.
     */
    public function assign($name, $value=null) {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }
    }

    /**
     * Returns the output of a parsed template as a string.
     *
     * @return string Content of parsed template.
     */
    public function render() {
        ob_start();

        extract($this->vars, EXTR_SKIP);
        include $this->file;

        $content = ob_get_clean();
        return $content;
    }

    /**
     * Displays the rendered template in the browser.
     */
    public function display() { echo $this->render(); }

    /**
     * Returns the parsed content of a template.
     *
     * @return string Parsed content of the view.
     */
    public function __toString() { return $this->render(); }

} // end View class


/**
 * A main controller class to be subclassed.
 *
 * The Controller class should be the parent class of all of your Controller
 * sub classes which contain the business logic of your application like:
 *      - render a blog post,
 *      - log a user in,
 *      - delete something and redirect,
 *      - etc.
 *
 * Using the Dispatcher class you can define what paths/routes map to which
 * Controllers and their methods.
 *
 * Each Controller method should either:
 *      - return a string response
 *      - redirect to another method
 */
class Controller {
    protected $layout = false;
    protected $layout_vars = array();

    /**
     * Executes a specified action/method for this Controller.
     *
     * @param string $action
     * @param array $params 
     */
    public function execute($action, $params) {
    // it's a private method of the class or action is not a method of the class
        if (substr($action, 0, 1) == '_' || ! method_exists($this, $action)) {
            throw new Exception("Action '{$action}' is not valid!");
        }
        call_user_func_array(array($this, $action), $params);
    }

    /**
     * Sets which layout to use for output.
     *
     * @param string $layout
     */
    public function setLayout($layout) {
        $this->layout = $layout;
    }

    /**
     * Assigns a set of key/values pairs to a layout.
     *
     * @param mixed $var    An array of key/value pairs or the name of a single variable.
     * @param string $value The value of the single variable.
     */
    public function assignToLayout($var, $value = null) {
        if (is_array($var)) {
            $this->layout_vars = array_merge($this->layout_vars, $var);
        } else {
            $this->layout_vars[$var] = $value;
        }
    }

    /**
     * Renders the output.
     * 
     * @todo Remove? Is this proper OO/good idea?
     *
     * @param string $view  Name of the view to render
     * @param array $vars   Array of variables
     * @return View 
     */
    public function render($view, $vars=array()) {
        if ($this->layout) {
            $this->layout_vars['content_for_layout'] = new View($view, $vars);
            return new View('../layouts/'.$this->layout, $this->layout_vars);
        } else {
            return new View($view, $vars);
        }
    }

    /**
     * Displays a rendered layout.
     *
     * @todo Remove? Is this proper OO/good idea?
     *
     * @param <type> $view
     * @param <type> $vars
     * @param <type> $exit
     */
    public function display($view, $vars=array(), $exit=true) {
        echo $this->render($view, $vars);

        if ($exit) exit;
    }

    /**
     * Renders a JSON encoded response and returns that as a string
     *
     * @param mixed $data_to_encode The data being encoded.
     * @return string               The JSON representation of $data_to_encode.
     */
    public function renderJSON($data_to_encode) {
        if (class_exists('JSON')) {
            return JSON::encode($data_to_encode);
        } else if (function_exists('json_encode')) {
                return json_encode($data_to_encode);
            } else {
                throw new Exception('No function or class found to render JSON.');
            }
    }

} // end Controller class


/**
 * The Observer class allows for a simple but powerful event system.
 * 
 * Example of watching/handling an event:
 * <code>
 *      // Connecting your event hangling function to an event.
 *      Observer::observe('page_edit_after_save', 'my_simple_observer');
 * 
 *      // The event handling function
 *      function my_simple_observer($page) {
 *          // do what you want to do
 *          var_dump($page);
 *      }
 * </code>
 * 
 * Example of generating an event:
 * 
 * <code>
 *      Observer::notify('my_plugin_event', $somevar);
 * </code>
 * 
 */
final class Observer {
    static protected $events = array();

    /**
     * Allows an event handler to watch/handle for a spefied event.
     *
     * @param string $event_name    The name of the event to watch for.
     * @param string $callback      The name of the function handling the event.
     */
    public static function observe($event_name, $callback) {
        if ( ! isset(self::$events[$event_name]))
            self::$events[$event_name] = array();

        self::$events[$event_name][$callback] = $callback;
    }

    /**
     * Allows an event handler to stop watching/handling a specific event.
     *
     * @param string $event_name    The name of the event.
     * @param string $callback      The name of the function handling the event.
     */
    public static function stopObserving($event_name, $callback) {
        if (isset(self::$events[$event_name][$callback]))
            unset(self::$events[$event_name][$callback]);
    }

    /**
     * Clears all registered event handlers for a specified event.
     *
     * @param string $event_name
     */
    public static function clearObservers($event_name) {
        self::$events[$event_name] = array();
    }

    /**
     * Returns a list of all event handlers handling a specified event.
     *
     * @param string $event_name
     * @return array An array of names for event handlers.
     */
    public static function getObserverList($event_name) {
        return (isset(self::$events[$event_name])) ? self::$events[$event_name] : array();
    }

    /**
     * Generates an event with the specified name.
     *
     * Note: if your event does not need to process the return values from any
     *       observers, use this instead of getObserverList().
     *
     * @param string $event_name
     */
    public static function notify($event_name) {
        $args = array_slice(func_get_args(), 1); // remove event name from arguments

        foreach(self::getObserverList($event_name) as $callback) {
            // XXX For some strange reason, this works... figure out later.
            // @todo FIXME Make this proper PHP 5.3 stuff.
            $Args = array();
            foreach($args as $k => &$arg){
                $Args[$k] = &$arg;
            }
            call_user_func_array($callback, $args);
        }
    }
}


/**
 * The AutoLoader class is an OO hook into PHP's __autoload functionality.
 *
 * You can add use the AutoLoader class to add singe and multiple files as well
 * entire folders.
 *
 * Examples:
 *
 * Single Files   - AutoLoader::addFile('Blog','/path/to/Blog.php');
 * Multiple Files - AutoLoader::addFile(array('Blog'=>'/path/to/Blog.php','Post'=>'/path/to/Post.php'));
 * Whole Folders  - AutoLoader::addFolder('path');
 *
 * When adding an entire folder, each file should contain one class having the
 * same name as the file without ".php" (Blog.php should contain one class Blog)
 *
 */
class AutoLoader {
    protected static $files = array();
    protected static $folders = array();

    /**
     * Register the AutoLoader on the SPL autoload stack.
     */
    public static function register()
    {
        spl_autoload_register(array('AutoLoader', 'load'), true, true);
    }

    /**
     * Adds a (set of) file(s) for autoloading.
     *
     * Examples:
     * <code>
     *      AutoLoader::addFile('Blog','/path/to/Blog.php');
     *      AutoLoader::addFile(array('Blog'=>'/path/to/Blog.php','Post'=>'/path/to/Post.php'));
     * </code>
     *
     * @param mixed $class_name Classname or array of classname/path pairs.
     * @param mixed $file       Full path to the file that contains $class_name.
     */
    public static function addFile($class_name, $file=null) {
        if ($file == null && is_array($class_name)) {
            self::$files = array_merge(self::$files, $class_name);
        } else {
            self::$files[$class_name] = $file;
        }
    }

    /**
     * Adds an entire folder or set of folders for autoloading.
     *
     * Examples:
     * <code>
     *      AutoLoader::addFolder('/path/to/classes/');
     *      AutoLoader::addFolder(array('/path/to/classes/','/more/here/'));
     * </code>
     *
     * @param mixed $folder Full path to a folder or array of paths.
     */
    public static function addFolder($folder) {
        if ( ! is_array($folder)) {
            $folder = array($folder);
        }
        self::$folders = array_merge(self::$folders, $folder);
    }

    /**
     * Loads a requested class.
     *
     * @param string $class_name
     */
    public static function load($class_name) {
        if (isset(self::$files[$class_name])) {
            if (file_exists(self::$files[$class_name])) {
                require self::$files[$class_name];
                return;
            }
        } else {
            foreach (self::$folders as $folder) {
                $folder = rtrim($folder, DIRECTORY_SEPARATOR);
                $file = $folder.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
        throw new Exception("AutoLoader could not find file for '{$class_name}'.");
    }

} // end AutoLoader class

/**
 * Flash service.
 *
 * The purpose of this service is to make some data available across pages.
 * Flash data is available on the next page but deleted when execution reaches
 * its end.
 *
 * Usual use of Flash is to make it possible for the current page to pass some
 * data to the next one (for instance success or error message before an HTTP
 * redirect).
 *
 * Example usage:
 * <code>
 *      Flash::set('errors', 'Blog not found!');
 *      Flash::set('success', 'Blog has been saved with success!');
 *      Flash::get('success');
 * </code>
 *
 * The Flash service as a concept is taken from Rails.
 */
final class Flash {
    const SESSION_KEY = 'framework_flash';

    private static $_flashstore = array(); // Data that prevous page left in the Flash

    /**
     * Returns a specific variable from the Flash service.
     * 
     * If the value is not found, NULL is returned instead.
     * @todo Return false instead?
     *
     * @param string $var   Variable name
     * @return mixed        Value of the variable stored in the Flash service.
     */
    public static function get($var) {
        return isset(self::$_flashstore[$var]) ? self::$_flashstore[$var] : null;
    }

    /**
     * Adds specific variable to the Flash service.
     * 
     * This variable will be available on the next page unless removed with the
     * removeVariable() or clear() methods.
     *
     * @param string $var   Variable name
     * @param mixed $value  Variable value
     */
    public static function set($var, $value) {
        $_SESSION[self::SESSION_KEY][$var] = $value;
    }

    /**
     * Adds specific variable to the Flash service.
     *
     * This variable will be available on the current page only.
     *
     * @param string $var   Variable name
     * @param mixed $value  Variable value
     */
    public static function setNow($var, $value) {
        self::$_flashstore[$var] = $value;
    }

    /**
     * Clears the Flash service.
     *
     * Data that previous pages stored will not be deleted, just the data that
     * this page stored itself.
     */
    public static function clear() {
        $_SESSION[self::SESSION_KEY] = array();
    }

    /**
     * Initializes the Flash service.
     *
     * This will read flash data from the $_SESSION variable and load it into
     * the $this->previous array.
     */
    public static function init() {
        // Get flash data...
        if ( ! empty($_SESSION[self::SESSION_KEY]) && is_array($_SESSION[self::SESSION_KEY])) {
            self::$_flashstore = $_SESSION[self::SESSION_KEY];
        }
        $_SESSION[self::SESSION_KEY] = array();
    }

} // end Flash class


/**
 * The Inflector class allows for strings to be reformated.
 *
 * For example:
 *
 * A string using underscore syntax ("camel_case") could be reformatted to
 * use camelcase syntax ("CamelCase").
 *
 * Example usage:
 * <code>
 *      echo Inflector::humanize($string);
 * </code>
 */
final class Inflector {

    /**
     * Returns a camelized string from a string using underscore syntax.
     * 
     * Example: "like_this_dear_reader" becomes "LikeThisDearReader"
     * 
     * @param string $string    Word to camelize.
     * @return string           Camelized word.
     */
    public static function camelize($string) {
        return str_replace(' ','',ucwords(str_replace('_',' ', $string)));
    }

    /**
     * Returns a string using underscore syntax from a camelized string.
     *
     * Example: "LikeThisDearReader" becomes "like_this_dear_reader"
     *
     * @param  string $string   CamelCased word
     * @return string           Underscored version of the $string
     */
    public static function underscore($string) {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    /**
     * Returns a humanized string from a string using underscore syntax.
     *
     * Example: "like_this_dear_reader" becomes "Like this dear reader"
     *
     * @param  string $string   String using underscore syntax.
     * @return string           Humanized version of the $string
     */
    public static function humanize($string) {
        return ucfirst(strtolower(str_replace('_', ' ', $string)));
    }
} // end Inflector class



// ----------------------------------------------------------------
//   global function
// ----------------------------------------------------------------

/**
 * Loads all functions from a speficied helper file.
 *
 * Example:
 * <code>
 *      use_helper('Cookie');
 *      use_helper('Number', 'Javascript', 'Cookie', ...);
 * </code>
 *
 * @param  string One or more helpers in CamelCase format.
 */
function use_helper() {
    static $_helpers = array();

    $helpers = func_get_args();

    foreach ($helpers as $helper) {
        if (in_array($helper, $_helpers)) continue;

        $helper_file = HELPER_PATH.DIRECTORY_SEPARATOR.$helper.'.php';

        if ( ! file_exists($helper_file)) {
            throw new Exception("Helper file '{$helper}' not found!");
        }

        include $helper_file;
        $_helpers[] = $helper;
    }
}

/**
 * Loads a model class from the model's file.
 *
 * Note: this is faster than waiting for the __autoload function and can be used
 *       for speed improvements.
 *
 * Example:
 * <code>
 *      use_model('Blog');
 *      use_model('Post', 'Category', 'Tag', ...);
 * </code>
 *
 * @param  string One or more Models in CamelCase format.
 */
function use_model() {
    static $_models = array();

    $models = func_get_args();

    foreach ($models as $model) {
        if (in_array($model, $_models)) continue;

        $model_file = APP_PATH.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$model.'.php';

        if ( ! file_exists($model_file)) {
            throw new Exception("Model file '{$model}' not found!");
        }

        include $model_file;
        $_models[] = $model;
    }
}


/**
 * Creates a url.
 *
 * Example output: http://www.example.com/controller/action/params#anchor
 *
 * You can add as many parameters as you want. If a param starts with # it is
 * considered to be an anchor.
 *
 * Example:
 * <code>
 *      get_url('controller/action/param1/param2');
 *      get_url('controller', 'action', 'param1', 'param2');
 * </code>
 *
 * @param string    controller, action, param and/or #anchor
 * @return string   A generated URL
 */
function get_url() {
    $params = func_get_args();
    if (count($params) === 1) return BASE_URL . $params[0];

    $url = '';
    foreach ($params as $param) {
        if (strlen($param)) {
            $url .= $param{0} == '#' ? $param: '/'. $param;
        }
    }
    return BASE_URL . preg_replace('/^\/(.*)$/', '$1', $url);
}

/**
 * Retrieves the request method used to access this page.
 *
 * @return string Possible values: GET, POST or AJAX
 */
function get_request_method() {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
        return 'AJAX';
    else
        return $_SERVER['REQUEST_METHOD'];
}

/**
 * Redirects this page to a specified URL.
 *
 * @param string $url
 */
function redirect($url) {
    Flash::set('HTTP_REFERER', html_encode($_SERVER['REQUEST_URI']));
    header('Location: '.$url); exit;
}

/**
 * An alias for redirect()
 *
 * @deprecated
 * @see redirect()
 */
function redirect_to($url) {
    redirect($url);
}

/**
 * Encodes HTML safely in UTF-8 format.
 *
 * You should use this instead of htmlentities.
 *
 * @param string $string    HTML to encode.
 * @return string           Encoded HTML
 */
function html_encode($string) {
    return htmlentities($string, ENT_QUOTES, 'UTF-8') ;
}

/**
 * Decodes HTML safely in UTF-8 format.
 *
 * You should use this instead of html_entity_decode.
 *
 * @param string $string    String to decode.
 * @return string           Decoded HTML
 */
function html_decode($string) {
    return html_entity_decode($string, ENT_QUOTES, 'UTF-8') ;
}

/**
 * Experimental anti XSS function.
 * 
 * @todo Improve or remove.
 *
 * @param <type> $string
 * @return <type> 
 */
function remove_xss($string) {
// Remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
// This prevents some character re-spacing such as <java\0script>
// Note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $string = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $string);

    // Straight replacements, the user should never need these since they're normal characters
    // This prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()~`";:?+/={}[]-_|\'\\';
    $search_count = count($search);
    for ($i = 0; $i < $search_count; $i++) {
    // ;? matches the ;, which is optional
    // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
    // &#x0040 @ search for the hex values
        $string = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $string); // with a ;
        // &#00064 @ 0{0,7} matches '0' zero to seven times
        $string = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $string); // with a ;
    }

    // Now the only remaining whitespace attacks are \t, \n, and \r
    $ra = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'style',
        'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound',
        'title', 'link',
        'base',
        'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
        'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint',
        'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick',
        'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged',
        'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter',
        'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate',
        'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown',
        'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown',
        'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup',
        'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange',
        'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter',
        'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange',
        'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra_count = count($ra);

    $found = true; // Keep replacing as long as the previous round replaced something
    while ($found == true) {
        $string_before = $string;
        for ($i = 0; $i < $ra_count; $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '((&#[xX]0{0,8}([9ab]);)||(&#0{0,8}([9|10|13]);))*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = '';//substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
            $string = preg_replace($pattern, $replacement, $string); // filter out the hex tags
            if ($string_before == $string) {
            // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $string;
} // remove_xss

/**
 * Prevent some basic XSS attacks, filters arrays.
 *
 * Experimental.
 *
 * @param <type> $ar
 * @return <type>
 */
function cleanArrayXSS($ar) {
    $ret = array();

    foreach ($ar as $k => $v) {
        if (is_array($k)) $k = cleanArrayXSS($k);
        else $k = remove_xss($k);

        if (is_array($v)) $v = cleanArrayXSS($v);
        else $v = remove_xss($v);

        $ret[$k] = $v;
    }

    return $ret;
}

/**
 * Prevent some basic XSS attacks
 */
function cleanXSS() {
    $in = array(&$_GET, &$_COOKIE, &$_SERVER); //, &$_POST);

    while (list($k,$v) = each($in)) {
        foreach ($v as $key => $val) {
            $oldkey = $key;

            if (!is_array($val)) {
                $val = remove_xss($val);
            }
            else {
                $val = cleanArrayXSS($val);
            }

            if (!is_array($key)) {
                $key = remove_xss($key);
            }
            else {
                $key = cleanArrayXSS($key);
            }

            unset($in[$k][$oldkey]);
            $in[$k][$key] = $val; continue;
            $in[] =& $in[$k][$key];
        }
    }
    unset($in);
    return;
}


/**
 * Escapes special characters in Javascript strings.
 *
 * @param $value string The unescaped string.
 * @return string
 */
function jsEscape($value) {
    return strtr((string) $value, array(
        "'"     => '\\\'',
        '"'     => '\"',
        '\\'    => '\\\\',
        "\n"    => '\n',
        "\r"    => '\r',
        "\t"    => '\t',
        chr(12) => '\f',
        chr(11) => '\v',
        chr(8)  => '\b',
        '</'    => '\u003c\u002F',
    ));
}


/**
 * Displays a "404 - page not found" message and exits.
 */
function pageNotFound($url=null) {
    Observer::notify('page_not_found', $url);

    header("HTTP/1.0 404 Not Found");
    echo new View('404');
    exit;
}

/**
 * @deprecated
 * @see pageNotFound()
 */
function page_not_found($url=null) {
    pageNotFound($url);
}


/**
 * Converts a disk- or filesize number into a human readable format.
 *
 * Example: "1024" become "1 kb"
 *
 * @param int $num      The number to represent.
 * @return string       Human readable representation of the disk/filesize.
 */
function convert_size($num) {
    if ($num >= 1073741824) $num = round($num / 1073741824 * 100) / 100 .' gb';
    else if ($num >= 1048576) $num = round($num / 1048576 * 100) / 100 .' mb';
        else if ($num >= 1024) $num = round($num / 1024 * 100) / 100 .' kb';
            else $num .= ' b';
    return $num;
}


// Information about time and memory

/**
 * @todo Finish doc
 *
 * @return <type>
 */
function memory_usage() {
    return convert_size(memory_get_usage());
}

/**
 * @todo Finish doc
 *
 * @return <type>
 */
function execution_time() {
    return sprintf("%01.4f", get_microtime() - FRAMEWORK_STARTING_MICROTIME);
}

/**
 * @todo Finish doc
 *
 * @return <type>
 */
function get_microtime() {
    $time = explode(' ', microtime());
    return doubleval($time[0]) + $time[1];
}

/**
 * @todo Finish doc
 *
 * @return <type>
 */
function odd_even() {
    static $odd = true;
    return ($odd = !$odd) ? 'even': 'odd';
}

/**
 * Alias for odd_even().
 */
function even_odd() {
    return odd_even();
}

/**
 * Retrieves content from a URL by any means possible.
 *
 * Intended to retrieve content from a URL by any means. Uses file_get_contents
 * by default if possible for speed reasons. Otherwise it attempts to use CURL.
 *
 * @param string $url       URL to retrieve content from.
 * @param int $flags        Optional flags to be passed onto file_get_contents.
 * @param resource $context A context resource to be passed to file_get_contents. Optional.
 * @return mixed            Either the URL's contents as string or FALSE on failure.
 */
function getContentFromUrl($url, $flags=0, $context=false) {

    if (!defined('CHECK_TIMEOUT')) define('CHECK_TIMEOUT', 5);

    // Use file_get_contents when possible... is faster.
    if (ini_get('allow_url_fopen') && function_exists('file_get_contents')) {    
        if ($context === false) $context = stream_context_create(array('http' => array('timeout' => CHECK_TIMEOUT)));

        return file_get_contents($url, $flags, $context);
    }
    else if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_HEADER, false);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, CHECK_TIMEOUT);
        curl_setopt ($ch, CURLOPT_TIMEOUT, CHECK_TIMEOUT);
        ob_start();
        curl_exec ($ch);
        curl_close ($ch);
        return ob_get_clean();
    }

    // If neither file_get_contents nor CURL are availabe, return FALSE.
    return false;
}

/**
 * Provides a nice print out of the stack trace when an exception is thrown.
 *
 * @param Exception $e Exception object.
 */
function framework_exception_handler($e) {
    if (!DEBUG) pageNotFound();

    echo '<style>h1,h2,h3,p,td {font-family:Verdana; font-weight:lighter;}</style>';
    echo '<h1>Wolf CMS - Uncaught '.get_class($e).'</h1>';
    echo '<h2>Description</h2>';
    echo '<p>'.$e->getMessage().'</p>';
    echo '<h2>Location</h2>';
    echo '<p>Exception thrown on line <code>'
    . $e->getLine() . '</code> in <code>'
    . $e->getFile() . '</code></p>';

    echo '<h2>Stack trace</h2>';
    $traces = $e->getTrace();
    if (count($traces) > 1) {
        echo '<pre style="font-family:Verdana; line-height: 20px">';

        $level = 0;
        foreach (array_reverse($traces) as $trace) {
            ++$level;

            if (isset($trace['class'])) echo $trace['class'].'&rarr;';

            $args = array();
            if ( ! empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_null($arg)) $args[] = 'null';
                    else if (is_array($arg)) $args[] = 'array['.sizeof($arg).']';
                        else if (is_object($arg)) $args[] = get_class($arg).' Object';
                            else if (is_bool($arg)) $args[] = $arg ? 'true' : 'false';
                                else if (is_int($arg)) $args[] = $arg;
                                    else {
                                        $arg = htmlspecialchars(substr($arg, 0, 64));
                                        if (strlen($arg) >= 64) $arg .= '...';
                                        $args[] = "'". $arg ."'";
                                    }
                }
            }
            echo '<strong>'.$trace['function'].'</strong>('.implode(', ',$args).')  ';
            echo 'on line <code>'.(isset($trace['line']) ? $trace['line'] : 'unknown').'</code> ';
            echo 'in <code>'.(isset($trace['file']) ? $trace['file'] : 'unknown')."</code>\n";
            echo str_repeat("   ", $level);
        }
        echo '</pre><hr/>';
    }

    $dispatcher_status = Dispatcher::getStatus();
    $dispatcher_status['request method'] = get_request_method();
    debug_table($dispatcher_status, 'Dispatcher status');
    if ( ! empty($_GET)) debug_table($_GET, 'GET');
    if ( ! empty($_POST)) debug_table($_POST, 'POST');
    if ( ! empty($_COOKIE)) debug_table($_COOKIE, 'COOKIE');
    debug_table($_SERVER, 'SERVER');
}

/**
 * Prints an HTML table with debug information.
 *
 * @param <type> $array
 * @param <type> $label
 * @param <type> $key_label
 * @param <type> $value_label 
 */
function debug_table($array, $label, $key_label='Variable', $value_label='Value') {
    echo '<table cellpadding="3" cellspacing="0" style="margin: 1em auto; border: 1px solid #000; width: 90%;">';
    echo '<thead><tr><th colspan="2" style="font-family: Verdana, Arial, sans-serif; background-color: #2a2520; color: #fff;">'.$label.'</th></tr>';
    echo '<tr><td style="border-right: 1px solid #000; border-bottom: 1px solid #000;">'.$key_label.'</td>'.
        '<td style="border-bottom: 1px solid #000;">'.$value_label.'</td></tr></thead>';

    foreach ($array as $key => $value) {
        if (is_null($value)) $value = 'null';
        else if (is_array($value)) $value = 'array['.sizeof($value).']';
            else if (is_object($value)) $value = get_class($value).' Object';
                else if (is_bool($value)) $value = $value ? 'true' : 'false';
                    else if (is_int($value)) $value = $value;
                        else {
                            $value = htmlspecialchars(substr($value, 0, 64));
                            if (strlen($value) >= 64) $value .= ' &hellip;';
                        }
        echo '<tr><td><code>'.$key.'</code></td><td><code>'.$value.'</code></td></tr>';
    }
    echo '</table>';
}

set_exception_handler('framework_exception_handler');

/**
 * This function will strip slashes if magic quotes is enabled so
 * all input data ($_GET, $_POST, $_COOKIE) is free of slashes
 */
function fix_input_quotes() {
    $in = array(&$_GET, &$_POST, &$_COOKIE);
    while (list($k,$v) = each($in)) {
        foreach ($v as $key => $val) {
            if (!is_array($val)) {
                $in[$k][$key] = stripslashes($val); continue;
            }
            $in[] =& $in[$k][$key];
        }
    }
    unset($in);
} // fix_input_quotes

if (PHP_VERSION < 6 && get_magic_quotes_gpc()) {
    fix_input_quotes();
}
