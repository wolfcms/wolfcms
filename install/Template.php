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

class Template
{

    public $template;         // String of template file
    private $_vars = array(); // Array of template variables

    /**
     * Assign the template path
     *
     * @param string $template Template path (absolute path or path relative to the templates dir)
     * @return void
     */
    public function __construct($template_path)
    {
        $this->template = $template_path;
    }

    /**
     * Assign specific variable to the template
     *
     * @param mixed $name Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public function assign($name, $value=null)
    {
        if (is_array($name)) {
            foreach($name as $n => $v) {
                $this->_vars[$n] = $v;
            }
        } else {
            $this->_vars[$name] = $value;
        }
    }

    /**
     * Display template and return output as string
     *
     * @return string content of compiled template
     */
    public function fetch()
    {
        ob_start();
        if ($this->_includeTemplate()) {
            return ob_get_clean();
        }
        ob_end_clean();
    }

    /**
     * Display template
     *
     * @return boolean
     */
    public function display()
    {
        return $this->_includeTemplate();
    }

    /**
     * Include specific template
     *
     * @return boolean
     */
    private function _includeTemplate()
    {
        if (file_exists($this->template)) {
            extract($this->_vars, EXTR_SKIP);
            include $this->template;
            return true;
        }
        return false;
    }

} // End Template class
