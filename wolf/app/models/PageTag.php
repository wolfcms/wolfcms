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
 * Class PageTag
 */
class PageTag extends Record {
    const TABLE_NAME = 'page_tag';

    public static function deleteByPageId($id) {
        return self::$__CONN__->exec('DELETE FROM '.self::tableNameFromClassName(self::TABLE_NAME).' WHERE page_id='.(int)$id) === false ? false: true;
    }
}
