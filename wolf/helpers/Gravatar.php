<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2012 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * A convenience helper to simplify Gravatar usage.
 *
 * @package Helpers
 *
 * @author     Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright  Martijn van der Kleijn, 2012
 * @license    GPLv3 License <http://www.gnu.org/copyleft/gpl.txt>
 */

/**
 * Gravatar helper.
 * 
 * The Gravatar helper simplifies getting Gravatar images and profiles by
 * providing some convenience functions.
 * 
 * It defaults to the following values:
 * 
 * Image size    - 32px
 * Rating        - G
 * Default image - mm (mystery-man) a simple, cartoon-style silhouetted outline
 *                 of a person which does not vary by email hash.
 */
class Gravatar {

    // Defaults
    private static $baseurl = 'http://www.gravatar.com/';
    private static $basesurl = 'https://secure.gravatar.com/';
    private static $avatar = 'avatar/';
    private static $size = '32';
    private static $rating = 'g';
    private static $default = 'mm';
    private static $format = 'json';

    /**
     * Generates a full HTML 5 compliant img tag to a Gravatar image.
     * 
     * @param type $email
     * @param type $attr
     * @param type $size
     * @param type $default
     * @param type $rating
     * @param type $secure
     * @return string
     */
    public static function img($email, $attr = array(), $size = false, $default = false, $rating = false, $secure = false) {
        $opt = array();
        if ($size !== false)
            $opt['size'] = $size;
        if ($default !== false)
            $opt['default'] = $default;
        if ($rating !== false)
            $opt['rating'] = $rating;
        if ($secure !== false)
            $opt['secure'] = $secure;

        $url = self::url($email, 'image', $opt);

        $img = '<img src="' . $url . '"';
        foreach ($attr as $key => $val)
            $img .= ' ' . $key . '="' . $val . '"';
        $img .= ' />';

        return $img;
    }

    /**
     * Generates a url to a Gravatar.com profile.
     * 
     * Defaults to a url requesting a JSON response.
     * 
     * @param type $email
     * @param type $format
     * @param type $callback
     * @return type
     */
    public static function profile($email, $format = false, $callback = false) {
        $opt = array();
        if ($format !== false)
            $opt['format'] = $format;
        if ($callback !== false)
            $opt['callback'] = $callback;

        return self::url($email, 'profile', $opt);
    }

    /**
     * Generates a Gravatar.com suited hash.
     * 
     * @param type $email
     * @return type
     */
    public static function hash($email) {
        return md5(strtolower(trim($email)));
    }

    /**
     * Generates a URL to a Gravatar avatar or profile data.
     * 
     * @param type $email
     * @param type $type
     * @param type $opt
     * @return null
     */
    public static function url($email, $type = 'image', $opt = array()) {
        $id = self::hash($email);

        if ($type === 'image') {
            if (empty($opt)) {
                $size = self::$size;
                $default = self::$default;
                $rating = self::$rating;
                $secure = false;
            } else if (is_array($opt)) {
                if (isset($opt['default'])) {
                    $default = urlencode($opt['default']);
                }
                if (isset($opt['size'])) {
                    $size = $opt['size'];
                }
                if (isset($opt['rating'])) {
                    $rating = $opt['rating'];
                }
                if (isset($opt['secure'])) {
                    $secure = $opt['secure'];
                }
            }

            if ($secure === true) {
                $url = self::$basesurl;
            } else {
                $url = self::$baseurl;
            }

            return $url . self::$avatar . $id . '.jpg?s=' . $size . '&d=' . $default . '&r=' . $rating;
        }

        if ($type === 'profile') {
            if (empty($opt)) {
                $format = self::$format;
                $callback = '';
            } else if (is_array($opt)) {
                if (isset($opt['format'])) {
                    $format = $opt['format'];
                } else {
                    $format = self::$format;
                }
                if (isset($opt['callback'])) {
                    $callback = '?callback=' . $opt['callback'];
                } else {
                    $callback = '';
                }
            }

            return $url . $id . '.' . $format . $callback;
        }

        return NULL;
    }

}
