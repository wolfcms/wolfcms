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
 * Class Layout
 */
class Layout extends Record {
    const TABLE_NAME = 'layout';

    public $name;
    public $content_type;
    public $content;

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

    public static function findAll($args = null) {
        return self::find($args);
    }

    public static function findById($id) {
        $tablename = self::tableNameFromClassName('Layout');
        $tablename_user = self::tableNameFromClassName('User');
        
        return self::findOne(array(
            'select' => "$tablename.id as id,
                        $tablename.name as name,
                        $tablename.content_type as content_type,
                        $tablename.content as content,
                        $tablename.created_on as created_on,
                        $tablename.updated_on as updated_on,
                        creator.name AS created_by_name, updator.name AS updated_by_name",
            'joins' => "LEFT JOIN $tablename_user AS creator ON $tablename.created_by_id =creator.id
                        LEFT JOIN $tablename_user AS updator ON $tablename.updated_by_id =updator.id",
            'where' => $tablename . '.id = :id',
            'values' => array(':id' => (int) $id)
        ));
    }

    public function getColumns() {
        return array(
            'id', 'name', 'content_type', 'content', 'created_on',
            'updated_on', 'created_by_id', 'updated_by_id', 'position'
        );
    }

    public function isUsed() {
        return Record::countFrom('Page', 'layout_id = :layout_id', array(':layout_id' => $this->id));
    }

} // end Layout class
