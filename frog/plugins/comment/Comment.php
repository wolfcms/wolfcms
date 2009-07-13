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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package frog
 * @subpackage plugin.comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.2.0
 * @since Frog version 0.9.3
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 */

/**
 * The Comment class represents a comment on a page.
 */
class Comment extends Record
{
    const TABLE_NAME = 'comment';
    const NONE = 0;
    const OPEN = 1;
    const CLOSED = 2;

    public static function find($args = null)
    {
        // Collect attributes...
        $where = isset($args['where']) ? trim($args['where']) : '';
        $order_by = isset($args['order']) ? trim($args['order']) :
            'is_approved, comment.created_on DESC';
        $offset = isset($args['offset']) ? (int)$args['offset'] : 0;
        $limit = isset($args['limit']) ? (int)$args['limit'] : 0;

        // Prepare query parts
        $order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
        $limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';

        $tablename = self::tableNameFromClassName('Comment');

        // Prepare SQL
        // FIXME - do this in a better way (sqlite doesn't like empty WHEREs)
        if ($where != '')
        {
            $sql = "SELECT * FROM $tablename AS comment " .
                "WHERE $where $order_by_string $limit_string";
        }
        else
        {
            $sql = "SELECT * FROM $tablename AS comment " .
                "$order_by_string $limit_string";
        }

        $stmt = self::$__CONN__->prepare($sql);
        $stmt->execute();

        // Run!
        if ($limit == 1) {
            return $stmt->fetchObject('Comment');
        } else {
            $objects = array();
            while ($object = $stmt->fetchObject('Comment'))
                $objects[] = $object;

            return $objects;
        }
    }

    /**
     * Find Comments limited to 10.
     * 
     * @param mixed $args Unused.
     * @return Array An array of Comment objects.
     */
    public static function findAll($args = null)
    {
    	return self::find(array('limit' => 10));
    }

    /**
     * Find a specific comment by its id.
     * 
     * @param int $id The comment's id.
     * @return Comment A Comment object.
     */
    public static function findById($id)
    {
        return self::find(array('where' => 'comment.id=' . (int)$id, 'limit' => 1));
    }

    /**
     * Find all comments in approved status.
     *
     * @return Array An array of Comment objects.
     */
    public static function findApproved()
    {
        return self::find(array('where' => 'is_approved=1'));
    }

    /**
     * Allows user to find all comments in approved status belonging to a page.
     * 
     * @param int $id Page id.
     * @return Array An array of Comment objects.
     */
    public static function findApprovedByPageId($id)
    {
        return self::find(array('where' => 'is_approved=1 AND page_id=' . (int)$id));
    }


    function name($class='')
    {
        if ($this->author_link != '')
        {
            if ($class != '') {
                $fullclass = 'class="'.$class.'" ';
            } else {
                $fullclass = '';
            };

            return sprintf(
                '<a %s href="%s" title="%s">%s</a>',
                $fullclass,
                $this->author_link,
                $this->author_name,
                $this->author_name
            );
        }
            else return $this->author_name;
    }

    function email() { return $this->author_email; }
    function link() { return $this->author_link; }
    function body() { return $this->body; }

    function date($format='%a, %e %b %Y')
    {
        return strftime($format, strtotime($this->created_on));
    }

    /**
     * Produces a valid gravatar url for the comment's author.
     *
     * @param <type> $size
     */
    function gravatar($size = '80') {
        $default = URL_PUBLIC.'public/images/gravatar.png';
        $grav_url = 'http://www.gravatar.com/avatar.php?gravatar_id='.md5($this->author_email).'&amp;default='.urlencode($default).'&amp;size='.$size;
        echo $grav_url;
    }


} // end Comment class
