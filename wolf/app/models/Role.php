<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package wolf
 * @subpackage models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2010
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
            $this->permissions = RolePermission::findPermissionsFor($this->id);
        }

        return $this->permissions;
    }

    /**
     * Find a Role by id
     *
     * @param int $id
     * @return mixed A Role or false on failure.
     */
    public static function findById(int $id) {
        return self::findByIdFrom('Role', $id);
    }

    /**
     * Find a Role by its name.
     *
     * @param string $name
     * @return mixed A Role or false on failure.
     */
    public static function findByName(string $name) {
        $where = 'name=?';
        $values = array($name);

        return self::findOneFrom('Role', $where, $values);
    }

    public static function findByUserId(int $id) {

        $where = 'name=?';
        $values = array($name);

        return self::findOneFrom('Role', $where, $values);
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