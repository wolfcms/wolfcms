<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2012 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2007-2008 Philippe Archambault <philippe.archambault@gmail.com>
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
 * Simple internationalisation library
 *
 * @package Helpers
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2012
 * 
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2007-2008
 * 
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 *
 */
defined('I18N_PATH') or define('I18N_PATH', APP_PATH.DIRECTORY_SEPARATOR.'i18n');
defined('DEFAULT_LOCALE') or define('DEFAULT_LOCALE', 'en');

/**
 * This function is as flexible as possible, you can choose your own pattern for variables in
 * the string.
 *
 * Examples of variables are: ':var_name', '#var_name', '{varname}',
 * '%varname', '%varname%', 'VARNAME', etc...
 *
 * <code>
 * return = array('hello world!' => 'bonjour le monde!',
 *                'user ":user" is logged in' => 'l\'utilisateur ":user" est connecté',
 *                'Posted by %user% on %month% %day% %year% at %time%' => 'Publié par %user% le %day% %month% %year% à %time%'
 *               );
 *
 * __('hello world!'); // bonjour le monde!
 * __('user ":user" is logged in', array(':user' => $user)); // l'utilisateur "demo" est connecté
 * __('Posted by %user% on %month% %day% %year% at %time%', array(
 *      '%user%' => $user,
 *      '%month%' => __($month),
 *      '%day%' => $day,
 *      '%year%' => $year,
 *      '%time%' => $time)); // Publié par demo le 3 janvier 2006 à 19:30
 * </code>
 */
function __($string, $args=null) {
    
    $string = I18n::getText($string);
    if ($args === null) return $string;

    return strtr($string, $args);
}

/**
 * I18n : Internationalisation function and class
 *
 */
class I18n {
    private static $locale = DEFAULT_LOCALE;
    private static $array = array();
    private static $default = array();

    public static function setLocale($locale) {
        self::$locale = $locale;
        self::loadArray();
    }

    public static function getLocale() {
        return self::$locale;
    }

    public static function getText($string) {
        if (isset(self::$array[$string])) {
            return self::$array[$string];
        }
        else {
            if (isset(self::$default[$string])) {
                return self::$default[$string];
            }
            else {
                return 'MISSING_DEFAULT_STRING';
            }
        }
    }

    public static function loadArray() {
        $catalog_file = I18N_PATH.DIRECTORY_SEPARATOR.self::$locale.'-message.php';
        $default_catalog_file = I18N_PATH.DIRECTORY_SEPARATOR.DEFAULT_LOCALE.'-message.php';

        // Add the preferred locale as main messages file.
        if (file_exists($catalog_file)) {
            $array = include $catalog_file;
            self::add($array);
        }
        
        // Add the default locale as a fall-back for missing translations.
        // Throw Exception if the default language file wasn't found.
        if (file_exists($default_catalog_file)) {
            $default = include $default_catalog_file;
            self::addDefault($default);
        }
        else {
            throw new Exception('Unable to find default language ('.DEFAULT_LOCALE.'-message.php) file for core system.');
        }
    }

    public static function add($array) {
        if (!empty($array))
            self::$array = array_merge(self::$array, $array);
    }
    
    public static function addDefault($default) {
        if (!empty($default))
            self::$default = array_merge(self::$default, $default);
    }

    /**
     * Determines preferred languages set by the user in the browser.
     *
     * Returns empty array when unable to determine language preferences.
     *
     * @return array Array of ietf language-region codes.
     */
    public static function getPreferredLanguages() {
        $languages = array();

        if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ) {
            $list = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
            $list = str_replace( ' ', '', $list );
            $list = explode( ",", $list );

            foreach ( $list as $language ) {
                $languages[] = substr( $language, 0, 2 );
            }
        }

        return $languages;
    }

} // end I18n class
