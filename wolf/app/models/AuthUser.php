<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008,2009,2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS.
 *
 * Wolf CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Wolf CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wolf CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Wolf CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package wolf
 * @subpackage models
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 * @copyright Martijn van der Kleijn, 2008, 2009, 2010
 */

/**
 * Used to keep track of login status.
 *
 * Tracks information of the logged in user, has login/logout method and
 * permission related functionality.
 *
 */
class AuthUser {
    const SESSION_KEY               = 'wolf_auth_user';
    const COOKIE_KEY                = 'wolf_auth_user';
    const COOKIE_LIFE               = 1209600; // two weeks
    const ALLOW_LOGIN_WITH_EMAIL    = false;
    const DELAY_ON_INVALID_LOGIN    = true;

    static protected $is_logged_in  = false;
    static protected $user_id       = false;
    static protected $is_admin      = false;
    static protected $record        = false;
    static protected $permissions   = array();

    /**
     * Attempts to load information about the current user.
     *
     * @return boolean Returns true when logged in user was found, otherwise false.
     */
    static public final function load() {
        if (isset($_SESSION[self::SESSION_KEY]) && isset($_SESSION[self::SESSION_KEY]['username']))
            $user = User::findBy('username', $_SESSION[self::SESSION_KEY]['username']);
        else if (isset($_COOKIE[self::COOKIE_KEY]))
                $user = self::challengeCookie($_COOKIE[self::COOKIE_KEY]);
            else
                return false;

        if ( ! $user)
            return self::logout();

        self::setInfos($user);

        return true;
    }

    /**
     * Sets the various bits of information pertaining to a user when logged in.
     *
     * @param Record $user User object instance.
     */
    static private final function setInfos(Record $user) {
        $_SESSION[self::SESSION_KEY] = array('username' => $user->username);

        self::$record = $user;
        self::$is_logged_in = true;
        self::$permissions = $user->getPermissions();
        self::$is_admin = self::hasPermission('administrator');
    }

    /**
     * Is a user logged in or not?
     *
     * @return boolean True when logged in, otherwise false.
     */
    static public final function isLoggedIn() {
        return self::$is_logged_in;
    }

    /**
     * Returns a full User object instance.
     *
     * @return User An object instance of type User.
     */
    static public final function getRecord() {
        return self::$record ? self::$record: false;
    }

    /**
     * Returns the user's id.
     *
     * @return string User id.
     */
    static public final function getId() {
        return self::$record ? self::$record->id: false;
    }

    /**
     * Returns the user's username.
     *
     * @return string Username.
     */
    static public final function getUserName() {
        return self::$record ? self::$record->username: false;
    }

    /**
     * Returns all permissions associated with the user.
     *
     * @return array Array of permission names.
     */
    static public final function getPermissions() {
        return self::$permissions;
    }

    /**
     * Checks if user has (one of) the required permissions.
     *
     * @param string $permission    Single permission or comma seperated list.
     * @return boolean              Returns true is user has one or more permissions.
     */
    static public final function hasPermission($permissions) {
        if ($permissions == null || $permissions == '')
            return true;

        foreach (explode(',', $permissions) as $permission) {
            if (in_array(strtolower($permission), self::$permissions))
                return true;
        }

        return false;
    }

    /**
     * 
     *
     * @param <type> $username
     * @return <type> 
     */
    static public final function forceLogin($username, $set_cookie=false) {
        return self::login($username, null, $set_cookie, false);
    }

    /**
     * Attempts to log in a user based on the username and password they provided.
     *
     * @param string  $username     User provided username.
     * @param string  $password     User provided password.
     * @param boolean $set_cookie   Set a "remember me" cookie? Defaults to false.
     * @return boolean              Returns true when successful, otherwise false.
     */
    static public final function login($username, $password, $set_cookie=false, $validate_password=true) {
        self::logout();

        $user = User::findBy('username', $username);

        if ( ! $user instanceof User && self::ALLOW_LOGIN_WITH_EMAIL)
            $user = User::findBy('email', $username);

        if ($user instanceof User && (false === $validate_password || self::validatePassword($user, $password))) {
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();

            if ($set_cookie) {
                $time = $_SERVER['REQUEST_TIME'] + self::COOKIE_LIFE;
                setcookie(self::COOKIE_KEY, self::bakeUserCookie($time, $user), $time, '/', null, (isset($_ENV['SERVER_PROTOCOL']) && ((strpos($_ENV['SERVER_PROTOCOL'],'https') || strpos($_ENV['SERVER_PROTOCOL'],'HTTPS')))));
            }

            // Regenerate Session ID upon login
            session_regenerate_id(true);

            self::setInfos($user);
            return true;
        }
        else {
            if ($user instanceof User) {
                $user->last_failure = date('Y-m-d H:i:s');
                $user->failure_count = ++$user->failure_count;
                $user->save();
            }

            if (self::DELAY_ON_INVALID_LOGIN) {
                if ( ! isset($_SESSION[self::SESSION_KEY.'_invalid_logins']))
                    $_SESSION[self::SESSION_KEY.'_invalid_logins'] = 1;
                else
                    ++$_SESSION[self::SESSION_KEY.'_invalid_logins'];

                sleep(max(0, min($_SESSION[self::SESSION_KEY.'_invalid_logins'], (ini_get('max_execution_time') - 1))));
            }
            return false;
        }
    }

    /**
     * Logs out a user.
     */
    static public final function logout() {
        unset($_SESSION[self::SESSION_KEY]);

        self::eatCookie();
        self::$record = false;
        self::$user_id = false;
        self::$is_admin = false;
        self::$permissions = array();
    }

    /**
     * Checks if the cookie is still valid.
     *
     * @param string $cookie    Cookie's content.
     * @return boolean          True if cookie is valid, otherwise false.
     */
    static private final function challengeCookie($cookie) {
        $params = self::explodeCookie($cookie);
        if (isset($params['exp'], $params['id'], $params['digest'])) {
            if ( ! $user = Record::findByIdFrom('User', $params['id']))
                return false;

            if (self::bakeUserCookie($params['exp'], $user) == $cookie && $params['exp'] > $_SERVER['REQUEST_TIME'])
                return $user;

        }
        return false;
    }

    /**
     * Explodes a cookie's content for easy manipulation.
     * 
     * @param string $cookie    Cookie's content.
     * @return array            Exploded cookie.
     */
    static private final function explodeCookie($cookie) {
        $pieces = explode('&', $cookie);

        if (count($pieces) < 2)
            return array();

        foreach ($pieces as $piece) {
            list($key, $value) = explode('=', $piece);
            $params[$key] = $value;
        }
        return $params;
    }

    /**
     * Eats (destroys) a cookie.
     */
    static private final function eatCookie() {
        setcookie(self::COOKIE_KEY, false, $_SERVER['REQUEST_TIME']-self::COOKIE_LIFE, '/', null, (isset($_ENV['SERVER_PROTOCOL']) && (strpos($_ENV['SERVER_PROTOCOL'],'https') || strpos($_ENV['SERVER_PROTOCOL'],'HTTPS'))));
    }

    /**
     * Creates content for a cookie. (enjoy...)
     *
     * @param string    $time   The time.
     * @param User      $user   A User object.
     * @return string           The actual cookie content.
     */
    static private final function bakeUserCookie($time, User $user) {
        return 'exp='.$time.'&id='.$user->id.'&digest='.sha1($user->username.$user->salt);
    }

    /**
     * Generates an alpha-numerical salt with a default of 32 characters.
     *
     * @param   int     $max  The maximum number of characters in the salt.
     * @return  string        The salt.
     */
    static public final function generateSalt($max = 32) {
        $base = rand(0, 1000000) . microtime(true) . rand(0, 1000000) . rand(0, microtime(true));
        $salt = sha1($base);

        if($max < 32){
            $salt = substr($salt, 0, $max);
        }

        return $salt;
    }

    /**
     * Validates a given password for a given user.
     *
     * @param User      $user   User to validate password against.
     * @param string    $pwd    Password to validate.
     * @return boolean          True when valid, otherwise false.
     */
    static public final function validatePassword(User $user, $pwd) {
        return $user->password == sha1($pwd.$user->salt);
    }

} // end AuthUser class
