<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 * @copyright Martijn van der Kleijn, 2010
 */

final class SecureToken extends Record {
    const TABLE_NAME = 'secure_token';
    const EXPIRES    = 3600;

    public $id;
    public $username;
    public $url;
    public $time;

    /**
     * Generates a security token for use in forms.
     *
     * The token is generated to be as secure as possible. It consists of:
     * - the username,
     * - the time at which the token was generated,
     * - a partial sha1() result of the user's password,
     * - the url for which the token is valid,
     * - a random salt generated during user creation
     *
     * The token is the sha1 of: <username>.<time>.<url>.<salt>.<partial_pwd>
     *
     * The validateToken() method should always be used to check a token's validity.
     *
     * @param string    $url
     * @return mixed    Returns a valid token or false upon error.
     */
    public static final function generateToken($url) {
        AuthUser::load();
        if (AuthUser::isLoggedIn()) {
            $user = AuthUser::getRecord();
            $time = microtime(true);
            $target_url = str_replace('&amp;', '&', $url);
            $pwdsha1 = substr(sha1($user->password), 5, 20);

            $oldtoken = SecureToken::getToken($user->username, $target_url);

            if (false === $oldtoken) {
                $oldtoken = new SecureToken();
                
                $oldtoken->username = $user->username;
                $oldtoken->url = sha1($target_url);
                $oldtoken->time = $time;

                $oldtoken->save();
            }
            else {
                $oldtoken->username = $user->username;
                $oldtoken->url = sha1($target_url);
                $oldtoken->time = $time;

                $oldtoken->save();
            }

            return sha1($user->username.$time.$target_url.$pwdsha1.$user->salt);
        }
        
        return false;
    }

    /**
     * Validates whether a given secure token is still valid.
     *
     * The validateToken() method validates the token is valid by checking:
     * - that the token is not expired (through the time),
     * - the token is valid for this user,
     * - the token is valid for this url
     *
     * It does so by reconstructing the token. If at any time during the valid
     * period of the token, the username, user password or the url changed, the
     * token is considered invalid.
     *
     * The token is also considered invalid if more than SecureToken::EXPIRES seconds
     * have passed.
     *
     * @param string $token The token.
     * @param string $url   The url for which the token was generated.
     * @return boolean      True if the token is valid, otherwise false.
     */
    public static final function validateToken($token, $url) {
        AuthUser::load();
        if (AuthUser::isLoggedIn()) {
            $user = AuthUser::getRecord();
            $target_url = str_replace('&amp;', '&', $url);
            $pwdsha1 = substr(sha1($user->password), 5, 20);

            $time = SecureToken::getTokenTime($user->username, $target_url);

            if ((microtime(true) - $time) > self::EXPIRES) {
                return false;
            }

            return (sha1($user->username.$time.$target_url.$pwdsha1.$user->salt) === $token);
        }

        return false;
    }

    private static final function getToken($username, $url) {
        $token = false;
        $token = Record::findOneFrom('SecureToken',"username = ? AND url = ?", array($username, sha1($url)));

        if ($token !== null && $token !== false && $token instanceof SecureToken) {
            return $token;
        }

        return false;
    }

    private static final function getTokenTime($username, $url) {
        $time = 0;

        if ($token = Record::findOneFrom('SecureToken',"username = ? AND url = ?", array($username, sha1($url)))) {
            $time = $token->time;
        }

        return $time;
    }

    /*
    public function getColumns() {
        return array('username', 'url', 'time');
    }
     * 
     */
}


