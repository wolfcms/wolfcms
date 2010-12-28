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
 * UserRole
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since Wolf version 0.7.0
 */
class UserRole extends Record {
    const TABLE_NAME = 'user_role';

    public $user_id = false;
    public $role_id = false;

    public static function setPermissionsFor($user_id, $roles) {
        $tablename = self::tableNameFromClassName('UserRole');

        $sql = 'DELETE FROM '.$tablename.' WHERE user_id='.(int)$user_id;
        self::$__CONN__->exec($sql);

        foreach ($roles as $role => $role_id) {
            $sql = 'INSERT INTO '.$tablename.' (user_id, role_id) VALUES ('.(int)$user_id.','.(int)$role_id.')';
            self::$__CONN__->exec($sql);
        }
    }

}
