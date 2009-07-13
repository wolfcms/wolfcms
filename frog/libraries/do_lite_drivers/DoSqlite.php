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
 * Light PDO adpater for SQLite.
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
 * Lite PDO adpater for Sqlite
 *
 * For more specification on methods of this class check the PDO class
 * can be find on the same directory at PDO.php
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class DoSqlite
{
    public $errorCode = '';
    public $errorInfo = array();
    
    private $_connection;
    private $_dbinfo;
    private $_persistent = false;
    
    /**
     * Checks connection and database selection
     *
     * @param string $dsn The Data Source Name
     *
     * @return void
     */
    public function __construct($dsn)
    {
        if ( ! $this->_connection = sqlite_open($dsn))
            $this->_setErrors('DBCON');
        else
            $this->_dbinfo = $dsn;
    }
    
    // -----------------------------------------------------------------------
    
    public function exec($query)
    {
        if (sqlite_query($query, $this->_connection))
            return sqlite_changes($this->_connection);
        
        // else
        $this->_setErrors('SQLER');
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function lastInsertId()
    {
        return sqlite_last_insert_rowid($this->_connection);
    }
    
    // -----------------------------------------------------------------------
    
    public function prepare($query)
    {
        return new DoLiteStatementSqlite($query, $this->_connection, $this->_dbinfo);
    }
    
    // -----------------------------------------------------------------------
    
    public function query($query)
    {
        $result_set = sqlite_query($query, $this->_connection, $this->_dbinfo); // before was unbuffered
        
        if ($result_set) {
            $result = array();
            while ($row = sqlite_fetch_array($result_set, SQLITE_ASSOC))
                array_push($result, $row);
        } else {
            $this->_setErrors('SQLER');
            $result = false;
        }
        return $result;
    }
    
    // -----------------------------------------------------------------------
    
    public function quote($string)
    {
        return "'".sqlite_escape_string($string)."'";
    }
    
    // -----------------------------------------------------------------------
    
    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case DoLite::ATTR_SERVER_INFO:
                return sqlite_libencoding();
                break;
            case DoLite::ATTR_SERVER_VERSION:
            case DoLite::ATTR_CLIENT_VERSION:
                return sqlite_libversion();
                break;
            case DoLite::ATTR_PERSISTENT:
                return $this->_persistent;
                break;
            case DoLite::ATTR_DRIVER_NAME:
                return 'sqlite';
                break;
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    public function setAttribute($attribute, $mixed)
    {
        if ($attribute === DoLite::ATTR_PERSISTENT && $mixed != $this->_persistent) {
            $this->_persistent = (boolean) $mixed;
            sqlite_close($this->_connection);
            
            if ($this->_persistent === true)
                $this->_connection = sqlite_popen($this->_dbinfo);
            else
                $this->_connection = sqlite_open($this->_dbinfo);
                
            return true;
        }
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function beginTransaction()
    {
        return (bool) sqlite_query('BEGIN TRANSACTION', $this->_connection);
    }
    
    // -----------------------------------------------------------------------
    
    public function commit()
    {
        return (bool) sqlite_query('COMMIT TRANSACTION', $this->_connection);
    }
    
    // -----------------------------------------------------------------------
    
    public function rollBack()
    {
        return (bool) sqlite_query('ROLLBACK TRANSACTION', $this->_connection);
    }
    
    public function sqliteCreateFunction($function_name, $callback, $num_args=false)
    {
        if ($num_args === false)
            sqlite_create_function($this->_connection, $function_name, $callback);
        else
            sqlite_create_function($this->_connection, $function_name, $callback, $num_args);
    }
    
    //
    // private methods
    //
    
    private function _setErrors($error)
    {
        if (!is_resource($this->_connection)) {
            $errno = 1;
            $errst = 'Unable to open or find database.';
        } else {
            $errno = sqlite_last_error($this->_connection);
            $errst = sqlite_error_string($errno);
        }
        $this->_errorCode = $error;
        $this->_errorInfo = array($error, $errno, $errst);
    }

} // End DoLiteSqlite class



/**
 * Lite PDOStatement adpater for Sqlite
 *
 * For more specification on methods of this class check the PDO class
 * can be find on the same directory at PDO.php
 *
 */
class DoLiteStatementSqlite extends DoLiteStatement
{
    
    function __construct($query, $connection, $dbinfo)
    {
        $this->_query = $query;
        $this->_connection = $connection;
        $this->_dbinfo = $dbinfo;
    }
    
    // -----------------------------------------------------------------------
    
    public function bindParam($parameter, &$variable, $data_type=null, $length=null, $driver_options=null)
    {
        $escaped_var = "'".sqlite_escape_string($variable)."'";
        
        if (is_int($parameter)) {
            $this->_bindParams[$parameter] = $escaped_var;
        } else {
            $this->_query = str_replace($parameter, $escaped_var, $this->_query);
        }
    }
    
    // -----------------------------------------------------------------------
    
    public function closeCursor()
    {
        return true;
    }
    
    // -----------------------------------------------------------------------
    
    public function columnCount()
    {
        return $this->_result ? sqlite_num_fields($this->_result): 0;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetch($mode=null, $cursor=null, $offset=null)
    {
        if (is_null($mode)) $mode = &$this->_fetchmode;
        
        $result = false;
        if ($this->_result) {
            switch($mode) {
                case DoLite::FETCH_NUM:
                    return sqlite_fetch_array($this->_result, SQLITE_NUM);
                    break;
                case DoLite::FETCH_ASSOC:
                    return sqlite_fetch_array($this->_result, SQLITE_ASSOC);
                    break;
                case DoLite::FETCH_OBJ:
                    return $this->fetchObject($this->_result);
                    break;
                case DoLite::FETCH_BOTH:
                default:
                    return sqlite_fetch_array($this->_result, SQLITE_BOTH);
                    break;
            }
        }
        
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchObject($class_name=null , $ctor_args=null)
    {
        if (is_null($class_name)) {
            return sqlite_fetch_object($this->_result);
        } else if (is_array($ctor_args)) {
            return sqlite_fetch_object($this->_result, $class_name, $ctor_args);
        } else {
            return sqlite_fetch_object($this->_result, $class_name);
        }
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchAll($mode=null)
    {
        if (is_null($mode)) $mode = &$this->_fetchmode;
        
        $result = array();
        if ($this->_result) {
            switch($mode) {
                case DoLite::FETCH_NUM:
                    while($row = sqlite_fetch_array($this->_result, SQLITE_NUM))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_ASSOC:
                    while($row = sqlite_fetch_array($this->_result, SQLITE_ASSOC))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_OBJ:
                    while($row = $this->fetchObject($this->_result))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_BOTH:
                default:
                    while($row = sqlite_fetch_array($this->_result, SQLITE_BOTH))
                        array_push($result, $row);
                    break;
            }
        }
        
        return $result;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchColumn($column=1)
    {
        if ($column < 1) $column = 1;

        if ($this->_result) {
            $result = sqlite_fetch_array($this->_result, SQLITE_NUM);
            if ($result)
                return $result[$column-1];
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    public function quote($string)
    {
        return "'".sqlite_escape_string($string)."'";
    }
    
    // -----------------------------------------------------------------------
    
    public function rowCount()
    {
        return $this->_result ? sqlite_changes($this->_connection): 0;
    }
    
    // -----------------------------------------------------------------------
    
    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case DoLite::ATTR_SERVER_INFO:
                return sqlite_libencoding();
                break;
            case DoLite::ATTR_SERVER_VERSION:
            case DoLite::ATTR_CLIENT_VERSION:
                return sqlite_libversion();
                break;
            case DoLite::ATTR_PERSISTENT:
                return $this->_persistent;
                break;
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    public function setAttribute($attribute, $mixed)
    {
        if ($attribute === DoLite::ATTR_PERSISTENT && $mixed != $this->_persistent) {
            $this->_persistent = (bool) $mixed;
            sqlite_close($this->_connection);
            
            if ($this->_persistent === true)
                $this->_connection = sqlite_popen($this->_dbinfo);
            else
                $this->_connection = sqlite_open($this->_dbinfo);
                
            return true;
        }
        return false;
    }

    //
    // private methods
    //
    
    protected function _setErrors($error)
    {
        if ( ! is_resource($this->_connection)) {
            $errno = 1;
            $errst = 'Unable to open database.';
        } else {
            $errno = sqlite_last_error($this->_connection);
            $errst = sqlite_error_string($errno);
        }
        $this->_errorCode = $error;
        $this->_errorInfo = array($error, $errno, $errst);
    }
    
    // -----------------------------------------------------------------------
    
    protected function _query(&$query)
    {
        if ( ! $query = sqlite_query($query, $this->_connection)) {
            $this->_setErrors('SQLER');
            $query = null;
        }
        return $query;
    }
    
} // End DoLiteStatementSqlite class
