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
 * Light PDO adpater for MySQL.
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
 * PDO adpater for Mysql
 *
 * For more specification on methods of this class check the PDO class
 * can be find on the same directory at DoLite.php
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class DoMysql
{
    public $errorCode = '';
    public $errorInfo = array();
    
    private $_connection;
    private $_dbinfo;
    private $_persistent = false;
    
    /**
     * Checks connection and database selection
     *
     * @param string $host  host with or without port info
     * @param string $db    database name
     * @param string $user  database user
     * @param string $pass  database password
     */
    public function __construct($host, $db, $user, $pass)
    {
        if ( ! $this->_connection = mysql_connect($host, $user, $pass)) {
            $this->_setErrors('DBCON');
        } else {
            if ( ! mysql_select_db($db, $this->_connection)) {
                $this->_setErrors('DBER');
            } else {
                $this->_dbinfo = array(
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass,
                    'name' => $db
                );
            }
        }
    }
    
    // -----------------------------------------------------------------------
    
    public function exec($query)
    {
        if (mysql_query($query, $this->_connection)) {
            return mysql_affected_rows($this->_connection);
        }
        
        // else
        $this->_setErrors('SQLER');
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function lastInsertId()
    {
        return mysql_insert_id($this->_connection);
    }

    public function prepare($query)
    {
        return new DoLiteStatementMysql($query, $this->_connection, $this->_dbinfo);
    }
    
    // -----------------------------------------------------------------------
    
    public function query($query)
    {
        $result_set = mysql_query($query, $this->_connection); // before was unbuffered
        if ($result_set) {
            $result = array();
            while ($row = mysql_fetch_assoc($result_set))
                array_push($result, $row);
        } else {
            $result = false;
            $this->_setErrors('SQLER');
        }
        return $result;
    }
    
    // -----------------------------------------------------------------------
    
    public function quote($string)
    {
        return "'".mysql_real_escape_string($string)."'";
    }
    
    // -----------------------------------------------------------------------
    
    public function getAttribute($attribute)
    {
        switch($attribute) {
            case DoLite::ATTR_SERVER_INFO:
                return mysql_get_host_info($this->_connection);
                break;
            case DoLite::ATTR_SERVER_VERSION:
                return mysql_get_server_info($this->_connection);
                break;
            case DoLite::ATTR_CLIENT_VERSION:
                return mysql_get_client_info();
                break;
            case DoLite::ATTR_PERSISTENT:
                return $this->_persistent;
                break;
            case DoLite::ATTR_DRIVER_NAME:
                return 'mysql';
                break;
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    function setAttribute($attribute, $mixed)
    {
        if ($attribute === DoLite::ATTR_PERSISTENT && $mixed != $this->_persistent) {
            $this->_persistent = (bool) $mixed;
            mysql_close($this->_connection);
            
            if ($this->_persistent === true)
                $this->_connection = mysql_pconnect($this->_dbinfo['host'], $this->_dbinfo['user'], $this->_dbinfo['pass']);
            else
                $this->_connection = mysql_connect($this->_dbinfo['host'], $this->_dbinfo['user'], $this->_dbinfo['pass']);
            
            mysql_select_db($this->_dbinfo['name'], $this->_connection);
            
            return true;
        }
        return false;
    }
    
    // -----------------------------------------------------------------------
    
    function beginTransaction()
    {
        return (bool) mysql_query('BEGIN WORK');
    }
    
    // -----------------------------------------------------------------------
    
    function commit()
    {
        return (bool) mysql_query('COMMIT');
    }
    
    // -----------------------------------------------------------------------
    
    function rollBack()
    {
        return (bool) mysql_query('ROLLBACK');
    }

    // -----------------------------------------------------------------------
    //
    // private methods
    //
    // -----------------------------------------------------------------------
    
    private function _setErrors($error)
    {
        if (!is_resource($this->_connection)) {
            $errno = mysql_errno();
            $errst = mysql_error();
        } else {
            $errno = mysql_errno($this->_connection);
            $errst = mysql_error($this->_connection);
        }
        $this->errorCode = $error;
        $this->errorInfo = array($error, $errno, $errst);
    }

} // End DoMysql class



/**
 * Lite PDOStatement adpater for MySQL
 *
 * For more specification on methods of this class check the PDO class
 * can be find on the same directory at PDO.php
 *
 */
class DoLiteStatementMysql extends DoLiteStatement
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
        $escaped_var = "'".mysql_real_escape_string($variable)."'";
        
        if (is_int($parameter)) {
            $this->_bindParams[$parameter] = $escaped_var;
        } else {
            $this->_query = str_replace($parameter, $escaped_var, $this->_query);
        }
    }
    
    // -----------------------------------------------------------------------
    
    public function closeCursor()
    {
        return mysql_free_result($this->_result);
    }
    
    // -----------------------------------------------------------------------
    
    public function columnCount()
    {
        return $this->_result ? mysql_num_fields($this->_result): 0;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetch($mode=null, $cursor=null, $offset=null)
    {
        if (is_null($mode)) $mode = &$this->_fetchmode;
        
        if ($this->_result) {
            switch($mode) {
                case DoLite::FETCH_NUM:
                    return mysql_fetch_row($this->_result);
                    break;
                case DoLite::FETCH_ASSOC:
                    return mysql_fetch_assoc($this->_result);
                    break;
                case DoLite::FETCH_OBJ:
                    return $this->fetchObject();
                    break;
                case DoLite::FETCH_BOTH:
                default:
                    return mysql_fetch_array($this->_result);
            }
        }

        return false;
    }
    
    // -----------------------------------------------------------------------
    
    public function fetchObject($class_name=null , $ctor_args=null)
    {
        if (is_null($class_name)) {
            return mysql_fetch_object($this->_result);
        } else if (is_array($ctor_args)) {
            return mysql_fetch_object($this->_result, $class_name, $ctor_args);
        } else {
            return mysql_fetch_object($this->_result, $class_name);
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
                    while ($row = mysql_fetch_row($this->_result))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_ASSOC:
                    while ($row = mysql_fetch_assoc($this->_result))
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_OBJ:
                    while ($row = $this->fetchObject())
                        array_push($result, $row);
                    break;
                case DoLite::FETCH_BOTH:
                default:
                    while ($row = mysql_fetch_array($this->_result))
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
            $result = mysql_fetch_row($this->_result);
            
            if ($result)
                return $result[$column-1];
        }
        return null;
    }
    
    // -----------------------------------------------------------------------
    
    public function quote($string)
    {
        return "'".mysql_real_escape_string($string)."'";
    }
    
    // -----------------------------------------------------------------------
    
    public function rowCount()
    {
        return mysql_affected_rows($this->_connection);
    }
    
    // -----------------------------------------------------------------------
    
    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case DoLite::ATTR_SERVER_INFO:
                return mysql_get_host_info($this->_connection);
                break;
            case DoLite::ATTR_SERVER_VERSION:
                return mysql_get_server_info($this->_connection);
                break;
            case DoLite::ATTR_CLIENT_VERSION:
                return mysql_get_client_info();
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
            mysql_close($this->_connection);
            
            if ($this->_persistent === true)
                $this->_connection = mysql_pconnect($this->_dbinfo['host'], $this->_dbinfo['user'], $this->_dbinfo['pass']);
            else
                $this->_connection = mysql_connect($this->_dbinfo['host'], $this->_dbinfo['user'], $this->_dbinfo['pass']);
            
            mysql_select_db($this->_dbinfo['name'], $this->_connection);
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
            $errno = mysql_errno();
            $errst = mysql_error();
        }
        else {
            $errno = mysql_errno($this->_connection);
            $errst = mysql_error($this->_connection);
        }
        $this->_errorCode = $error;
        $this->_errorInfo = array($error, $errno, $errst);
    }
    
    protected function _query($query)
    {
        if ( ! $query = mysql_query($query, $this->_connection)) {
            $this->_setErrors('SQLER');
            $query = null;
        }
        return $query;
    }
    
} // End DoLiteStatementMysql class
