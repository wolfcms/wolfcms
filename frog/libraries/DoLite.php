<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * Light Rewrite of PDO class in php (thanks to Andrea Giammarchi)
 *
 * @package frog
 * @subpackage libraries.dolite
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.0.1
 * @since Frog version 0.9.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * Lite Rewrite of PDO class in php (thanks to Andrea Giammarchi)
 *
 * method implemented:
 *  - beginTransaction  ()
 *  - commit            ()
 *  - errorCode         ()
 *  - errorInfo         ()
 *  - exec              ($query)
 *  - getAttribute      ($attribute)
 *  - lastInsertId      ()
 *  - prepare           ($query, $array=null)
 *  - query             ($query)
 *  - quote             ($string)
 *  - rollBack          ()
 *  - setAttribute      ($attribute, $mixed)
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class DoLite
{
    // only include const I that have been implemented
    const FETCH_ASSOC = 2;
    const FETCH_NUM = 3;
    const FETCH_BOTH = 4;
    const FETCH_OBJ = 5;
    
    const ATTR_SERVER_VERSION = 4;
    const ATTR_CLIENT_VERSION = 5;
    const ATTR_SERVER_INFO = 6;
    const ATTR_PERSISTENT = 12;
    const ATTR_DRIVER_NAME = 16;
    
    const MYSQL_ATTR_USE_BUFFERED_QUERY = 1;
    
    private $_adapter = null;
    
    /**
     * Creates a PDO instance representing a connection to a database
     *
     * @param string $dsn The Data Source Name
     * @param string $username The user name of the connexion
     * @param string $password The password of the connexion
     * @param array $driver_options The drivers options (not implemented)
     */
    public function __construct($dsn , $username=null, $password=null, $driver_options=null)
    {
        $conn = $this->_getDSN($dsn);
        
        // MySQL connexion
        if ($conn['dbtype'] == 'mysql') {
            require_once CORE_ROOT . '/libraries/do_lite_drivers/DoMysql.php';
            if (isset($conn['port'])) {
                $conn['host'] .= ':'.$conn['port'];
            }
            $this->_adapter = new DoMysql($conn['host'], $conn['dbname'], $username, $password);
            
        // SQLite connexion
        } else if ($conn['dbtype'] == 'sqlite2' || $conn['dbtype'] == 'sqlite') {
            require_once CORE_ROOT . '/libraries/do_lite_drivers/DoSqlite.php';
            $this->_adapter = new DoSqlite($conn['dbname']);
            
        // postgreSQL connexion
        } else if ($conn['dbtype'] == 'pgsql') {
            require_once CORE_ROOT . '/libraries/do_lite_drivers/DoPgsql.php';
            $dsn = "host={$conn['host']} dbname={$conn['dbname']} user={$username} password={$password}";
            
            if (isset($conn['port'])) {
                $dsn .= " port={$conn['port']}";
            }
            $this->_adapter = new DoPgsql($dsn);
        }
    }
    
    public function __call($method, $args)
    {
        if (method_exists($this->_adapter, $method))
            return call_user_func_array( array(&$this->_adapter, $method), $args );
    }
    
    //
    // Private methodes
    //
    
    private function _getDSN($dsn)
    {
        $result = array();
        $pos = strpos($dsn, ':');
        $params = explode(';', substr($dsn, ($pos + 1)));
        $result['dbtype'] = strtolower(substr($dsn, 0, $pos));
        $nb_params = count($params);
        for ($a = 0, $b = $nb_params; $a < $b; $a++) {
            $tmp = explode('=', $params[$a]);
            if (count($tmp) == 2)
                $result[$tmp[0]] = $tmp[1];
            else
                $result['dbname'] = $params[$a];
        }
        return $result;
    }
    
} // End DoLite class

/**
 * Lite Rewrite of PDOStatement class in php
 *
 * method implemented:
 *  - bindParam     ($mixed, &$variable, [ not implemented: $type=null, $lenght=null ])
 *  - bindValue     ()
 *  - columnCount   ()
 *  - errorCode     ()
 *  - errorInfo     ()
 *  - execute       ($array=null)
 *  - fetch         ($mode=null, [ not implemented: $cursor=null, $offset=null ])
 *  - fetchAll      ($mode=null)
 *  - fetchColomn   ($colomn=1)
 *  - fetchObject   ($class_name=null , $ctor_args=null)
 *  - getAttribute  ($attribute)
 *  - rowCount      ()
 *  - setAttribute  ($attribute, $mixed)
 *  - setFetchMode  ($mode)
 */


abstract class DoLiteStatement
{
    // Last error string code
    protected $_errorCode = '';
    
    // Last error informations, array(code, number, details)
    protected $_errorInfo = array();
    
    // Database connection
    protected $_connection;
    
    // Database connection infos
    protected $_dbinfo;
    
    // Connection mode, is true on persistent, false on normal (default) connection
    protected $_persistent = false;
    
    // Last query used
    protected $_query = '';
    
    // Last result from last query
    protected $_result = null;
    
    // constant PDO::FETCH_* result mode
    protected $_fetchmode = DoLite::FETCH_BOTH;
    
    // Stored bindParam
    protected $_bindParams = array();

    /**
     * Replace ? or :named values to execute prepared query
     *
     * @param   mixed   Integer or String to replace prepared value
     * @param   mixed   variable to replace
     * @param   int     this variable is not used but respects PDO original accepted parameters
     * @param   int     this variable is not used but respects PDO original accepted parameters
     */
    abstract public function bindParam($mixed, &$variable, $type=null, $lenght=null);
    
    // alias of bindParam()
    public function bindValue($parameter, &$variable, $data_type=null, $length=null, $driver_options=null)
    {
        $this->bindParam($parameter, $variable);
    }
    
    /**
     *  Checks if query was valid and returns how may fields returns
     *
     * @return int
     */
    abstract public function columnCount();
    
    /**
     * Returns a code rappresentation of the last error
     * 
     * @return  string  string code of the last error
     */
    public function errorCode()
    {
        return $this->_errorCode;
    }
    
    /**
     * Returns an array with error informations of the last error
     *
     * @return  array array(0 => error code, 1 => error number, 2 => error string)                          
     */
    public function errorInfo()
    {
        return $this->_errorInfo;
    }
    
    /**
     * Excecutes a query and returns true on success or false.
     *
     * @param   array   If present, it should contain all replacements for prepared query
     *
     * @return  bool    true if query has been done without errors, false otherwise
     */
    public function execute($array=null)
    {
        // check if we have an assoc array (only need to verify the first key)
        if (is_array($array)) {
            if (is_string(key($array))) {
                array_walk($array, array(&$this, 'bindParam'));
            } else {
                $this->_bindParams = $array;
            }
        }
        
        $query = $this->_query;
        
        if (count($this->_bindParams) > 0 && $pos = strpos($query, '?') !== false) {
            $i = 0;
            while ($pos !== false) {
                $query = preg_replace("/(\?)/", $this->quote($this->_bindParams[$i]), $query, 1);
                $pos = strpos($query, '?');
                ++$i;
            }
            $this->_bindParams = array();
        }
        
        if ($this->_result = $this->_query($query))
            return true;

        return false;
    }
    
    /**
     * Returns, if present, next row of executed query or false.
     *
     * @param   int     PDO::FETCH_* constant to know how to read next row, default PDO::FETCH_BOTH
     *                  NOTE: if $mode is omitted is used default setted mode, PDO::FETCH_BOTH
     * @param   int     this variable is not used but respects PDO original accepted parameters
     * @param   int     this variable is not used but respects PDO original accepted parameters
     *
     * @return  mixed   Next row of executed query or false if there is nomore.
     */
    abstract public function fetch($mode=null, $cursor=null, $offset=null);
    
    /**
     * Returns an array with all rows of executed query.
     *
     * @param   int     PDO::FETCH_* constant to know how to read all rows, default PDO::FETCH_BOTH
     *                  NOTE: this doesn't work as fetch method, then it will use always PDO::FETCH_BOTH
     *                  if this param is omitted
     * @return  array   An array with all fetched rows
     */
    abstract public function fetchAll($mode=null);
    
    /**
     * Returns, if present, first column of next row of executed query
     *
     * @param int $colomn   No of the colomn to get (starting by 1)
     *
     * @return mixed        Null or next row's  column
     */
    abstract public function fetchColumn($colomn=1);
    
    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class_name Name of the created class, defaults to stdClass.
     * @param array $ctor_args   Elements of this array are passed to the constructor.
     *
     * @return object
     */
    abstract public function fetchObject($class_name=null , $ctor_args=null);
    
    /**
     * Returns number of last affected database rows
     *
     * @return  Integer     number of last affected rows
     */
    abstract public function rowCount();
    
    /**
     * Quotes correctly a string for this database
     *
     * @param   int     a constant [ PDO::ATTR_SERVER_INFO,
     *                  PDO::ATTR_SERVER_VERSION,
     *                  PDO::ATTR_CLIENT_VERSION,
     *                  PDO::ATTR_PERSISTENT ]
     * @return  mixed   correct information or false
     */
    abstract public function getAttribute($attribute);
    
    /**
     * Sets database attributes, in this version only connection mode.
     *
     * @param   Integer     PDO_* constant, in this case only PDO::ATTR_PERSISTENT
     * @param   Mixed       value for PDO_* constant, in this case a Boolean value
     *                      true for permanent connection, false for default not permament connection
     * @return  Boolean     true on change, false otherwise
     */
    abstract public function setAttribute($attribute, $mixed);
    
    /**
     * Sets default fetch mode to use with this->fetch() method.
     *
     * @param   int     PDO::FETCH_* constant to use while reading an execute query with fetch() method.
     *                  NOTE: PDO::FETCH_LAZY and PDO::FETCH_BOUND are not supported
     * @return  bool    true on change, false otherwise
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case DoLite::FETCH_NUM:
            case DoLite::FETCH_ASSOC:
            case DoLite::FETCH_OBJ:
            case DoLite::FETCH_BOTH:
                $this->_fetchmode = $mode;
                return true;
                break;
        }
        return false;
    }
    
} // End DoLiteStatement class

class DoLiteException extends Exception
{
    public $errorInfo = null;    // corresponds to PDO::errorInfo() or PDOStatement::errorInfo()
    protected $message;          // textual error message use Exception::getMessage() to access it
    protected $code;             // SQLSTATE error code use Exception::getCode() to access it

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
} // End DoLiteException class
