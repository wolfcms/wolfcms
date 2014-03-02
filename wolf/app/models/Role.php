<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * Role
 *
 * @todo finish phpdoc
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since Wolf version 0.7.0
 */
class Role extends Record {
    const TABLE_NAME = 'role';

    public $id;
    public $name;
    public $permissions = false;

    /**
     * This returns the name of the Role if you for example do:
     *
     * <?php
     *     $role = Role::findById(1);
     *     echo $role;
     * ?>
     *
     * @return string Name of the role.
     */
    public function  __toString() {
        return $this->name;
    }

    /**
     * Returns all Permissions for this Role.
     *
     * The Permissions are only read from the DB when needed the first time.
     *
     * @return array An array of Permission objects.
     */
    public function permissions() {
        if (!$this->permissions) {
            $this->permissions = array();

            foreach (RolePermission::findPermissionsFor($this->id) as $perm) {
                $this->permissions[$perm->name] = $perm;
            }
        }

        return $this->permissions;
    }

    public function hasPermission($permissions) {
        if (!$this->permissions)
            $this->permissions();

        foreach (explode(',', $permissions) as $permission) {
            if (array_key_exists($permission, $this->permissions))
                return true;
        }

        return false;
    }

    /**
     * Find a Role by its name.
     *
     * @param string $name
     * @return mixed A Role or false on failure.
     */
    public static function findByName($name) {
        return self::findOne(array(
            'where'  => 'name = :name',
            'values' => array(':name' => $name)
        ));
    }

    public static function findByUserId($id) {

        $userroles = UserRole::find(array(
            'where'  => 'user_id = :user_id',
            'values' => array(':user_id' => (int) $id)
        ));

        if (count($userroles) <= 0)
            return false;

        $roles = array();

        foreach($userroles as $role) {
            $roles[] = Role::findById($role->role_id);
        }

        return $roles;
    }

    /**
     * Make sure we only try to save specified columns in the DB.
     *
     * @return array Array of column names.
     */
    public function getColumns() {
        return array('id', 'name');
    }
}