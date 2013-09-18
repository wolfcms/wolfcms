<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008,2009,2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * class Setting
 *
 * Provide a administration interface of some configuration
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since Wolf version 0.8.7
 */
class Setting extends Record {
    const TABLE_NAME = 'setting';

    public $name;
    public $value;

    public static $settings = array();
    public static $is_loaded = false;

    public static function init() {
        if (! self::$is_loaded) {
            $settings = self::find();
            foreach($settings as $setting)
                self::$settings[$setting->name] = $setting->value;

            self::$is_loaded = true;
        }
    }

    /**
     * Get the value of a setting
     *
     * @param name  string  The setting name
     * @return string the value of the setting name
     */
    public static function get($name) {
        return isset(self::$settings[$name]) ? self::$settings[$name]: false;
    }

    public static function saveFromData($data) {

        foreach ($data as $name => $value) {
            Record::update('Setting', array('value' => $value), 'name = :name', array(':name' => $name));
        }

    }

    public static function getLanguages() {
        $ietf = SettingController::$ietf;

        if ($handle = opendir(APP_PATH.'/i18n')) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') !== 0) {
                    $code = substr($file, 0, strpos($file, '-message.php'));
                    $languages[$code] = isset($ietf[$code]) ? $ietf[$code]: __('unknown');
                }
            }
            closedir($handle);
        }
        asort($languages);

        return $languages;
    }

    public static function getThemes() {
        $themes = array();
        $dir = CORE_ROOT.'/admin/themes/';
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') !== 0 && is_dir($dir.$file)) {
                    $themes[$file] = Inflector::humanize($file);
                }
            }
            closedir($handle);
        }
        asort($themes);

        return $themes;
    }

} // end Setting class
