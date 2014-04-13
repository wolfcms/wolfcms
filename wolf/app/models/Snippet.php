<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009,2010 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * class Snippet
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since Wolf version 0.1
 */
class Snippet extends Record {
    const TABLE_NAME = 'snippet';

    public $name;
    public $filter_id;
    public $content;
    public $content_html;

    public $created_on;
    public $updated_on;
    public $created_by_id;
    public $updated_by_id;

    public function beforeInsert() {
        $this->created_by_id = AuthUser::getId();
        $this->created_on = date('Y-m-d H:i:s');
        return true;
    }

    public function beforeUpdate() {
        $this->updated_by_id = AuthUser::getId();
        $this->updated_on = date('Y-m-d H:i:s');
        return true;
    }

    public function beforeSave() {
        // snippet name should not be empty
        if (empty($this->name)) {
            return false;
        }
        
        // apply filter to save is generated result in the database
        if ( ! empty($this->filter_id)) {
            $this->content_html = Filter::get($this->filter_id)->apply($this->content);
        }
        else {
            $this->content_html = $this->content;
        }
        return true;
    }

    public static function findAll($args = null) {
        return self::find($args);
    }

    public static function findById($id) {
        $tablename = self::tableNameFromClassName('Snippet');
        $tablename_user = self::tableNameFromClassName('User');
        
        return self::findOne(array(
            'select' => "$tablename.*, creator.name AS created_by_name, updater.name AS updated_by_name",
            'joins' => "LEFT JOIN $tablename_user AS creator ON $tablename.created_by_id = creator.id ".
                       "LEFT JOIN $tablename_user AS updater ON $tablename.updated_by_id = updater.id",
            'where' => $tablename . '.id = ?',
            'values' => array((int) $id)
        ));
    }

    public static function findByName($name) {
        return self::findOne(array(
            'where' => 'name LIKE ?',
            'values' => array($name)
        ));
    }

    public function getColumns() {
        return array(
            'id', 'name', 'filter_id', 'content', 'content_html',
            'created_on', 'updated_on', 'created_by_id', 'updated_by_id',
            'position'
        );
    }

} // end Snippet class

