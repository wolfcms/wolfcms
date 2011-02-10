<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
final class SecureToken extends Record {
    const TABLE_NAME = 'secure_token';
    const EXPIRES    = 900; // token expires after 15 min.

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
     * - a partial sha256 result of the user's password,
     * - the url for which the token is valid,
     * - a random salt generated during user creation
     *
     * The token is the sha256 of: <username>.<time>.<url>.<salt>.<partial_pwd>
     *
     * The validateToken() method should always be used to check a token's validity.
     *
     * @see Hash Helper
     *
     * @param string    $url
     * @return mixed    Returns a valid token or false upon error.
     */
    public static final function generateToken($url) {
        use_helper('Hash');
        $hash = new Crypt_Hash('sha256');
        AuthUser::load();
        
        if (AuthUser::isLoggedIn()) {
            $user = AuthUser::getRecord();
            $time = microtime(true);
            $target_url = str_replace('&amp;', '&', $url);
            $pwd = substr(bin2hex($hash->hash($user->password)), 5, 20);

            $oldtoken = SecureToken::getToken($user->username, $target_url);

            if (false === $oldtoken) {
                $oldtoken = new SecureToken();
                
                $oldtoken->username = $user->username;
                $oldtoken->url = bin2hex($hash->hash($target_url));
                $oldtoken->time = $time;

                $oldtoken->save();
            }
            else {
                $oldtoken->username = $user->username;
                $oldtoken->url = bin2hex($hash->hash($target_url));
                $oldtoken->time = $time;

                $oldtoken->save();
            }

            return bin2hex($hash->hash($user->username.$time.$target_url.$pwd.$user->salt));
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
        use_helper('Hash');
        $hash = new Crypt_Hash('sha256');
        AuthUser::load();
        
        if (AuthUser::isLoggedIn()) {
            $user = AuthUser::getRecord();
            $target_url = str_replace('&amp;', '&', $url);
            $pwd = substr(bin2hex($hash->hash($user->password)), 5, 20);

            $time = SecureToken::getTokenTime($user->username, $target_url);

            if ((microtime(true) - $time) > self::EXPIRES) {
                return false;
            }

            if (!isset($user->salt)) {
                return (bin2hex($hash->hash($user->username.$time.$target_url.$pwd)) === $token);
            }
            else {
                return (bin2hex($hash->hash($user->username.$time.$target_url.$pwd.$user->salt)) === $token);
            }
        }

        return false;
    }

    private static final function getToken($username, $url) {
        use_helper('Hash');
        $hash = new Crypt_Hash('sha256');

        $token = false;
        $token = Record::findOneFrom('SecureToken',"username = ? AND url = ?", array($username, bin2hex($hash->hash($url))));

        if ($token !== null && $token !== false && $token instanceof SecureToken) {
            return $token;
        }

        return false;
    }

    private static final function getTokenTime($username, $url) {
        use_helper('Hash');
        $hash = new Crypt_Hash('sha256');
        $time = 0;

        if ($token = Record::findOneFrom('SecureToken',"username = ? AND url = ?", array($username, bin2hex($hash->hash($url))))) {
            $time = $token->time;
        }

        return $time;
    }
}