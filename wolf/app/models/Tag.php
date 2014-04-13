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
 * class PagePart
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class Tag extends Record {
    const TABLE_NAME = 'tag';
    
    public $id;
    public $name;
    public $count;
    
    public function getColumns() {
        return array('id', 'name', 'count');
    }
    
    public function afterSave() {
        parent::afterSave();
        
        if ($this->count === 0) {
            return $this->delete();
        }
        
        return true;
    }
    
    public static function findByName($name) {
        return self::findOne(array(
            'where' => 'name = :name',
            'values' => array(':name' => $name)
        ));
    }
}