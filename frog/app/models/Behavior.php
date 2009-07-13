<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package frog
 * @subpackage models
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * Class Behavior
 *
 * This is a part of the Plugin API of Frog CMS. It provide a "interface" to
 * add and remove behavior "page type" to Frog CMS.
 *
 * @since Frog version 0.5
 */
class Behavior
{
    private static $loaded_files = array();
    private static $behaviors = array();
    
    /**
     * Add a new behavior to Frog CMS
     *
     * @param behavior_id string  The Behavior plugin folder name
     * @param file      string  The file where the Behavior class is
     */
    public static function add($behavior_id, $file)
    {
        self::$behaviors[$behavior_id] = $file;
    }
    
    /**
     * Remove a behavior to Frog CMS
     *
     * @param behavior_id string  The Behavior plugin folder name
     */
    public static function remove($behavior_id)
    {
        if (isset(self::$behaviors[$behavior_id]))
            unset(self::$behaviors[$behavior_id]);
    }

    /**
     * Load a behavior and return it
     *
     * @param behavior_id string  The Behavior plugin folder name
     * @param page        object  Will be pass to the behavior
     * @param params      array   Params that fallow the page with this behavior (passed to the behavior too)
     *
     * @return object
     */
    public static function load($behavior_id, &$page, $params)
    {
        if ( ! empty(self::$behaviors[$behavior_id]))
        {
            $file = CORE_ROOT.'/plugins/'.self::$behaviors[$behavior_id];

            if (isset(self::$loaded_files[$file]))
                return new $behavior_id($page, $params);

            if (file_exists($file))
            {
                include $file;
                self::$loaded_files[$file] = true;
                return new $behavior_id($page, $params);
            }
            else
            {
                exit ("Behavior $behavior_id not found!");
            }
        }
    }

    /**
     * Load a behavior and return it
     *
     * @param behavior_id string  The Behavior plugin folder name
     *
     * @return string   class name of the page
     */
    public static function loadPageHack($behavior_id)
    {
        $behavior_page_class = 'Page'.str_replace(' ','',ucwords(str_replace('_',' ', $behavior_id)));

        if (class_exists($behavior_page_class, false))
            return $behavior_page_class;
        else
            return 'Page';
    }

    
    /**
     *
       Find all active Behaviors id

       return array
     */
    public static function findAll()
    {
        return array_keys(self::$behaviors);
    }

} // end Behavior class
