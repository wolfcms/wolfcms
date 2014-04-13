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
 * @package Models
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * class User
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class User extends Record {
    const TABLE_NAME = 'user';

    public $name = '';
    public $email = '';
    public $username = '';

    // User preferences
    public $language = '';

    public $created_on;
    public $updated_on;
    public $created_by_id;
    public $updated_by_id;
    public $last_login;
    public $last_failure;
    public $failure_count;


    public function roles() {
        if ( !isset($this->id)) {
            return array();
        }

        $roles = Role::findByUserId($this->id);

        if (!$roles)
            return array();
        else
            return $roles;
    }

    public static function findBy($column, $value) {
        return self::findOne(array(
            'where' => $column . ' = :value',
            'values' => array(':value' => $value)
        ));
    }

    public function getColumns() {
        return array(
            'id', 'name', 'email', 'username', 'password', 'salt',
            'language', 'last_login', 'last_failure', 'failure_count',
            'created_on', 'updated_on', 'created_by_id', 'updated_by_id'
        );
    }

    public function beforeInsert() {
        $this->created_by_id = AuthUser::getId();
        $this->created_on = date('Y-m-d H:i:s');
        $this->last_login = date('Y-m-d H:i:s', 0);
        $this->last_failure = date('Y-m-d H:i:s', 0);
        $this->failure_count = 0;
        return true;
    }

    public function beforeUpdate() {
        $this->updated_by_id = AuthUser::getId();
        $this->updated_on = date('Y-m-d H:i:s');
        return true;
    }

    public static function findAll($args = null) {
        return self::find($args);
    }

    public static function findById($id) {
        $tablename = self::tableNameFromClassName('User');
        
        return self::findOne(array(
            'select' => "$tablename.*, creator.name AS created_by_name, updater.name AS updated_by_name",
            'joins' => "LEFT JOIN $tablename AS creator ON $tablename.created_by_id = creator.id ".
                       "LEFT JOIN $tablename AS updater ON $tablename.updated_by_id = updater.id",
            'where' => $tablename . '.id = :id',
            'values' => array(':id' => $id)
        ));
    }

} // end User class
