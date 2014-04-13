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
 * Permission
 *
 * @todo finish phpdoc
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since Wolf version 0.7.0
 */
class Permission extends Record {
    const TABLE_NAME = 'permission';

    public $id;
    public $name;

    // Caching / Lazy init
    // Array of Permission objects
    static private $permissions = false;

    // Getter functions.
    public function id() { return $this->id; }
    public function name() { return $this->name; }

    /**
     * This returns the name of the Permission object.
     *
     * @return string Name of the object.
     */
    public function  __toString() {
        return $this->name;
    }

    /**
     * Returns all Permissions.
     *
     * The Permissions are cached in a static class variable and only re-read
     * from the database if something is changed in the DB.
     *
     * @return array An array of Permission objects.
     */
    public static function getPermissions() {
        if (!self::$permissions) {
            $perms = self::find();

            foreach ($perms as $perm) {
                self::$permissions[$perm->id()] = $perm;
            }
        }

        return array_values(self::$permissions);
    }

    /**
     * Makes sure the Permissions are re-read from the DB.
     *
     * @return boolean Always returns true.
     */
    public function beforeSave() {
        self::$permissions = false;
        return true;
    }


    /**
     * Find a Permission object by id
     *
     * @param int $id
     * @return mixed A Permission object or false on failure.
     */
    public static function findById($id) {
        if (!self::$permissions) {
            self::getPermissions();
        }

        if (!array_key_exists((int) $id, self::$permissions))
            return false;

        return self::$permissions[$id];
    }

    /**
     * Find a Permission by its name.
     *
     * @param string $name
     * @return mixed A Permission object or false on failure.
     */
    public static function findByName($name) {
        return self::findOne(array(
            'where'  => 'name = :name',
            'values' => array(':name' => $name)
        ));
    }
}