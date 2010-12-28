<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
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
        $limit_string = $limit > 0 ? "LIMIT $limit" : '';
        $offset_string = $offset > 0 ? "OFFSET $offset" : '';

        $tablename = self::tableNameFromClassName('Comment');

        // Prepare SQL
        // @todo FIXME - do this in a better way (sqlite doesn't like empty WHEREs)
        if ($where != '')
        {
            $sql = "SELECT * FROM $tablename AS comment " .
                "WHERE $where $order_by_string $limit_string $offset_string";
        }
        else
        {
            $sql = "SELECT * FROM $tablename AS comment " .
                "$order_by_string $limit_string $offset_string";
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
