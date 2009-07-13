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
 * Light PDO adpater for PostgreSQL.
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
 * Lite PDO adpater for PostgreSQL
 *
 * For more specification on methods of this class check the PDO class
 * can be find on the same directory at DoLite.php
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class DoPgsql
{
    public $errorCode = '';
    public $errorInfo = array();
    
    private $_connection;
    private $_dbinfo;
    private $_persistent = false;
    
    /**
     *  Checks connection and database selection
     *
     * @param   string  Database connection params
     */
    public function __construct($dsn)
    {
        if ( ! $this->_connection = &pg_connect($dsn))
            $this->_setErrors('DBCON');
        else
            $this->_dbinfo = $dsn;
    }
    
    // -----------------------------------------------------------------------
    
    public function exec($query)
    {
        if ($result = pg_query($this->_connection, $query))
            return pg_affected_rows($result);
        
        // else
        $this->_setErrors('SQLER');
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function lastInsertId()
    {
        return $this->_result ? pg_last_oid($this->_result): 0;
    }
    
    // -----------------------------------------------------------------------
    
    public function prepare($query)
    {
        return new DoLiteStatementPgsql($query, $this->_connection, $this->_dbinfo);
    }
    
    // -----------------------------------------------------------------------
    
    public function query($query)
    {
        $result_set = pg_query($this->_connection, $query);
        
        if ($result_set) {
            $result = array();
            while ($row = pg_fetch_assoc($result_set))
                array_push($result, $row);
        } else {
            $result = false;
            $this->_setErrors('SQLER');
        }
        return $result;
    }
    
    // -----------------------------------------------------------------------
    
    public function quote($string) {
        return "'".pg_escape_string($string)."'";
    }
    
    // -----------------------------------------------------------------------
    
    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case DoLite::ATTR_SERVER_INFO:
                return pg_parameter_status($this->_connection, 'server_encoding');
                break;
            case DoLite::ATTR_SERVER_VERSION:
                return pg_parameter_status($this->_connection, 'server_version');
                break;
            case DoLite::ATTR_CLIENT_VERSION:
                return pg_parameter_status($this->_connection, 'server_version') . ' '
                     . pg_parameter_status($this->_connection, 'client_encoding');
                break;
            case DoLite::ATTR_PERSISTENT:
                return $this->_persistent;
                break;
            case DoLite::ATTR_DRIVER_NAME:
                return 'pgsql';
                break;
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    public function setAttribute($attribute, $mixed)
    {
        if ($attribute === DoLite::ATTR_PERSISTENT && $mixed != $this->_persistent) {
            $this->_persistent = (bool) $mixed;
            pg_close($this->_connection);
            
            if ($this->_persistent === true)
                $this->_connection = pg_pconnect($this->_dbinfo);
            else
                $this->_connection = pg_connect($this->_dbinfo);
                
            return true;
        }
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function beginTransaction()
    {
        return (bool) pg_query($this->_connection, 'BEGIN');
    }
    
    // -----------------------------------------------------------------------
    
    public function commit()
    {
        return (bool) pg_query($this->_connection, 'COMMIT');
    }
    
    // -----------------------------------------------------------------------
    
    public function rollBack()
    {
        return (bool) pg_query($this->_connection, 'ROLLBACK');
    }
    
    //
    // private methods
    //
    
    private function _setErrors($error)
    {
        if ( ! is_resource($this->_connection)) {
            $errno = 1;
            $errst = pg_last_error();
        }
        else {
            $errno = 1;
            $errst = pg_last_error($this->_connection);
        }
        $this->_errorCode = $error;
        $this->_errorInfo = array($error, $errno, $errst);
    }

} // End DoLitePqsql class



/**
 * Lite PDOStatement adpater for PostgreSQL
 *
 * For more specification on methods of this class check the PDO class
 * can be find on the same directory at PDO.php
 *
 */
class DoLiteStatementPgsql extends DoLiteStatement
{
    private $_row = 0; // used for fetchObject
    
    public function __construct($query, $connection, $dbinfo)
    {
        $this->_query = $query;
        $this->_connection = $connection;
        $this->_dbinfo = $dbinfo;
    }
    
    // -----------------------------------------------------------------------
    
    public function bindParam($parameter, &$variable, $data_type=null, $length=null, $driver_options=null)
    {
        $escaped_var = "'".pg_escape_string($variable)."'";
        
        if (is_int($parameter)) {
            $this->_bindParams[$parameter] = $escaped_var;
        } else {
            $this->_query = str_replace($parameter, $escaped_var, $this->_query);
        }
    }
    
    // -----------------------------------------------------------------------
    
    public function closeCursor()
    {
        return pg_free_result($this->_result);
    }
    
    // -----------------------------------------------------------------------
    
    public function columnCount()
    {
        return $this->_result ? pg_num_fields($this->_result): 0;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetch($mode=null, $cursor=null, $offset=null)
    {
        if (is_null($mode)) $mode = $this->_fetchmode;

        if ($this->_result) {
            switch ($mode) {
                case DoLite::FETCH_NUM:
                    $this->_row++;
                    return pg_fetch_row($this->_result);
                    break;
                case DoLite::FETCH_ASSOC:
                    $this->_row++;
                    return pg_fetch_assoc($this->_result);
                    break;
                case DoLite::FETCH_OBJ:
                    return $this->fetchObject($this->_result);
                    break;
                case DoLite::FETCH_BOTH:
                default:
                    $this->_row++;
                    return pg_fetch_array($this->_result);
                    break;
            }
        }
        
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchObject($class_name=null , $ctor_args=null)
    {
        ++$this->_row;
        if (is_null($class_name)) {
            return pg_fetch_object($this->_result);
        } else if (is_array($ctor_args)) {
            return pg_fetch_object($this->_result, $this->_row-1, $class_name, $ctor_args);
        } else {
            return pg_fetch_object($this->_result, $this->_row-1, $class_name);
        }
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchAll($mode=null)
    {
        if (is_null($mode)) $mode = $this->_fetchmode;
        
        $result = array();
        if ($this->_result) {
            switch ($mode) {
                case DoLite::FETCH_NUM:
                    while($row = pg_fetch_row($this->_result))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_ASSOC:
                    while($row = pg_fetch_assoc($this->_result))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_OBJ:
                    while($row = $this->fetchObject($this->_result))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_BOTH:
                default:
                    while($row = pg_fetch_array($this->_result))
                        array_push($result, $row);
                    break;
            }
        }
        $this->_row = 0;
        return $result;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchColumn($column=1)
    {
        if ($column < 1) $column = 1;
        
        if ($this->_result) {
            $result = pg_fetch_row($this->_result);
            if ($result)
                return $result[$column-1];
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    public function quote($string) {
        return "'".pg_escape_string($string)."'";
    }
    
    // -----------------------------------------------------------------------
    
    public function rowCount()
    {
        return $this->_result ? pg_affected_rows($this->_result): 0;
    }
    
    // -----------------------------------------------------------------------
    
    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case DoLite::ATTR_SERVER_INFO:
                return pg_parameter_status($this->_connection, 'server_encoding');
                break;
            case DoLite::ATTR_SERVER_VERSION:
                return pg_parameter_status($this->_connection, 'server_version');
                break;
            case DoLite::ATTR_CLIENT_VERSION:
                return pg_parameter_status($this->_connection, 'server_version') . ' '
                     . pg_parameter_status($this->_connection, 'client_encoding');
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
            pg_close($this->_connection);
            
            if ($this->_persistent === true)
                $this->_connection = pg_connect($this->_dbinfo);
            else
                $this->_connection = pg_pconnect($this->_dbinfo);
            
            return true;
        }
        return false;
    }

    //
    // private methods
    //
    
    protected function _setErrors($error)
    {
        if ( ! $this->_result) {
            $errno = 1;
            $errst = pg_last_error();
        } else {
            $errno = 1;
            $errst = pg_result_error($this->_result);
        }
        $this->_errorCode = $error;
        $this->_errorInfo = array($error, $errno, $errst);
    }
    
    // -----------------------------------------------------------------------
    
    protected function _query(&$query)
    {
        if ( ! $query = pg_query($this->_connection, $query)) {
            $this->_setErrors('SQLER');
            $query = null;
        }
        return $query;
    }
    
} // End DoLiteStatementPqsql class
