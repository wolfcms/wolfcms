<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * @subpackage controllers
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * Class PluginController
 *
 * Plugin controller to dispatch to all plugins controllers.
 *
 * @package frog
 * @subpackage controllers
 *
 * @since 0.9
 */
class PluginController extends Controller
{
    public $plugin;
    
    function __construct()
    {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn())
            redirect(get_url('login'));
    }
    
    public function render($view, $vars=array())
    {
        if ($this->layout)
        {
            $this->layout_vars['content_for_layout'] = new View('../../plugins/'.$view, $vars);
            return new View('../layouts/'.$this->layout, $this->layout_vars);
        }
        else return new View('../../plugins/'.$view, $vars);
    }
    
    public function execute($action, $params)
    {
        if (isset(Plugin::$controllers[$action]))
        {
            $plugin = Plugin::$controllers[$action];
            if (file_exists($plugin->file))
            {
                include_once $plugin->file;
                
                $plugin_controller = new $plugin->class_name;
                
                $action = count($params) ? array_shift($params): 'index';
                
                call_user_func_array(
                    array($plugin_controller, $action),
                    $params
                );
            }
            else throw new Exception("Plugin controller file '{$plugin->file}' was not found!");
        }
        else throw new Exception("Action '{$action}' is not valid!");
    }
    
} // end PluginController class
