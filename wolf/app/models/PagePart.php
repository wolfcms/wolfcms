<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * class PagePart
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since Wolf version 0.1
 */
class PagePart extends Record implements PagePartInterface {
    const TABLE_NAME = 'page_part';

    public $name = 'body';
    public $filter_id = '';
    public $page_id = 0;
    public $content = '';
    public $content_html = '';

    public function beforeSave() {
    // apply filter to save is generated result in the database
        if ( ! empty($this->filter_id))
            $this->content_html = Filter::get($this->filter_id)->apply($this->content);
        else
            $this->content_html = $this->content;

        return true;
    }

    public static function findByPageId($id) {
        return self::findAllFrom('PagePart', 'page_id='.(int)$id.' ORDER BY id');
    }

    public static function deleteByPageId($id) {
        return self::$__CONN__->exec('DELETE FROM '.self::tableNameFromClassName('PagePart').' WHERE page_id='.(int)$id) === false ? false: true;
    }

    // NEW FUNCTIONALITY

    /**
     * Checks if the value is valid.
     * Must be overridden.
     *
     * @return boolean
     */
    public function isValid() {
        if (preg_match('/[<>]+/', $this->content))
            return false;
        else
            return true;
    }

    public function getFormElement($class=null, $id=null, $size=255) {
        $form = '<input';

        if (null !== $class)
            $form .= ' class="'.$class.'"';

        if (null !== $id)
            $form .= ' id="'.$id.'"';

        $form .= ' maxlength="255" name="pagepart['.$this->name.']" size="'.$size.'" type="text" value="'.$this->content.'" />';

        return $form;
    }

    public function set($value) {
        $this->content = $value;
    }

    public function get() {
        return $this->content;
    }

    public function findPagePartById($id) {
        $name = 'body';
        $filter_id = '';
        $page_id = 0;
        $content = '';
        $content_html = '';
    }

} // end PagePart class
