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
 * RolePermission
 *
 * @todo finish phpdoc
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since Wolf version 0.7.0
 */
class RolePermission extends Record {
    const TABLE_NAME = 'role_permission';

    public $role_id = false;
    public $permission_id = false;

    public static function savePermissionsFor($role_id, $permissions) {
        $tablename = self::tableNameFromClassName('RolePermission');

        $sql = 'DELETE FROM '.$tablename.' WHERE role_id='.(int)$role_id;
        self::$__CONN__->exec($sql);

        foreach ($permissions as $permission) {
            $sql = 'INSERT INTO '.$tablename.' (role_id, permission_id) VALUES ('.(int)$role_id.','.(int)$permission->id().')';
            self::$__CONN__->exec($sql);
        }
    }

    public static function findPermissionsFor(int $role_id) {
        $roleperms = self::findAllFrom('RolePermission', 'role_id='.(int)$role_id);
        $perms = array();

        foreach($roleperms as $role => $perm) {
            $perms[] = Permission::findById($perm);
        }

        return $perms;
    }

}