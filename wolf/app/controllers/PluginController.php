<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008,2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * @package wolf
 * @subpackage controllers
 *
 * @since 0.9
 */
class PluginController extends Controller {

    // Provides compatibility with Page class.
    // TODO - cleaner way of doing multiple inheritance?
    public function __call($function, $args) {
        if (!defined('CMS_VERSION')) {
            $args = implode(', ', $args);
            return Page::$function($args);
            //print "Forum plugin: Call to $function() with args '$args' failed!<br/>\n";
        }
        else
            return false;
    }

    // Provides compatibility with Page class.
    // TODO - cleaner way of doing multiple inheritance?
    public function __get($variable) {
        if (!defined('CMS_VERSION')) {
            if (isset(Page::$$variable))
                return Page::$$variable;
            else
                return false;
        }
    }

    // Provides compatibility with Page class.
    // TODO - cleaner way of doing multiple inheritance?
    public $url;

    // Normal class stuff continues here.
    public $plugin;

    function __construct() {
        if (defined('CMS_BACKEND')) {
            AuthUser::load();
            if ( ! AuthUser::isLoggedIn())
                redirect(get_url('login'));
        }
    }

    public function display($view, $vars=array(), $exit=true) {
        if (defined('CMS_BACKEND')) {
            return parent::display($view, $vars, $exit);
        }
        else {
            $this->content = $this->render($view, $vars);
            $this->executeFrontendLayout();
            if ($exit) exit;
        }
    }

    public function executeFrontendLayout() {
        global $__CMS_CONN__;

        $sql = 'SELECT content_type, content FROM '.TABLE_PREFIX.'layout WHERE name = '."'$this->frontend_layout'";

        $stmt = $__CMS_CONN__->prepare($sql);
        $stmt->execute();

        if ($layout = $stmt->fetchObject()) {
        // if content-type not set, we set html as default
            if ($layout->content_type == '')
                $layout->content_type = 'text/html';

            // set content-type and charset of the page
            header('Content-Type: '.$layout->content_type.'; charset=UTF-8');

            // Provides compatibility with the Page class.
            // TODO - cleaner way of doing multiple inheritance?
            $this->url = CURRENT_URI;

            // execute the layout code
            eval('?>'.$layout->content);
        }
    }

    public function setLayout($layout) {
        if (defined('CMS_BACKEND'))
            parent::setLayout($layout);
        else
            $this->frontend_layout = $layout;
    }

    public function render($view, $vars=array()) {
        if (defined('CMS_BACKEND')) {
            if ($this->layout) {
                $this->layout_vars['content_for_layout'] = new View('../../plugins/'.$view, $vars);
                return new View('../layouts/'.$this->layout, $this->layout_vars);
            }
            else return new View('../../plugins/'.$view, $vars);
        }
        else {
            return parent::render($view, $vars);
        }
    }

    public function execute($action, $params) {
        if (isset(Plugin::$controllers[$action])) {
            $plugin = Plugin::$controllers[$action];
            if (file_exists($plugin->file)) {
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
