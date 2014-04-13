<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * class PagePart
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class PagePart extends Record {
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
        return self::find(array(
            'where'  => 'page_id = :page_id',
            'order'  => 'id',
            'values' => array(':page_id' => (int) $id)
        ));
    }

    public static function deleteByPageId($id) {
        return self::deleteWhere('PagePart', 'page_id = :page_id', array(':page_id' => (int) $id)) === false ? false : true;
    }

} // end PagePart class
