<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Martijn van der Kleijn, 2008-2010
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * Model representing a page of content.
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class Page extends Node {
    const TABLE_NAME = 'page';

    const STATUS_DRAFT = 1;
    const STATUS_PREVIEW = 10;
    const STATUS_PUBLISHED = 100;
    const STATUS_HIDDEN = 101;
    const STATUS_ARCHIVED = 200;

    const LOGIN_NOT_REQUIRED = 0;
    const LOGIN_REQUIRED = 1;
    const LOGIN_INHERIT = 2;

    public $id;
    public $title = '';
    public $slug = '';
    public $breadcrumb;
    public $keywords = '';
    public $description = '';
    public $content;
    public $parent_id;
    public $layout_id;
    public $behavior_id;
    public $status_id;
    public $comment_status;
    public $created_on;
    public $published_on;
    public $valid_until;
    public $updated_on;
    public $created_by_id;
    public $updated_by_id;
    public $position;
    public $is_protected;
    public $needs_login;
    public $author;
    public $author_id;
    public $updater;
    public $updater_id;
    // non db fields
    private $parent = false;
    private $path = false;
    private $level = false;
    private $tags = false;

    public function __construct($object=null, $parent=null) {
        if ($parent !== null) {
            $this->parent = $parent;
        }

        if ($object !== null) {
            foreach ($object as $key => $value) {
                $this->$key = $value;
            }
        }
    }


    public function id() {
        return $this->id;
    }


    public function author() {
        return $this->author;
    }


    public function authorId() {
        return $this->author_id;
    }


    public function title() {
        return $this->title;
    }


    public function description() {
        return $this->description;
    }


    public function keywords() {
        return $this->keywords;
    }


    public function parentId() {
        return $this->parent_id;
    }


    /**
     * Returns the path for this node.
     * 
     * For instance, for a page with the URL http://www.example.com/wolfcms/path/to/page.html,
     * the path is: path/to/page (without the URL_SUFFIX)
     *
     * Note: The path does not start nor end with a '/'.
     *
     * @return string   The node's full path.
     */
    public function path() {
        if ($this->path === false) {
            if ($this->parent() !== false) {
                $this->path = trim($this->parent()->path().'/'.$this->slug, '/');
            } else {
                $this->path = trim($this->slug, '/');
            }
        }

        return $this->path;
    }


    /**
     * @deprecated
     * @see path()
     */
    public function uri() {
        return $this->path();
    }


    /**
     * @deprecated
     * @see path()
     */
    public function getUri() {
        return $this->path();
    }


    /**
     * Returns the current page object's url.
     *
     * Usage: <?php echo $this->url(); ?> or <?php echo $page->url(); ?>
     *
     * @return string   The url of the page object.
     */
    public function url($suffix=true) {
        if ($suffix === false) {
            return BASE_URL.$this->path();
        }
        else {
            return BASE_URL.$this->path().($this->path() != '' ? URL_SUFFIX : '');
        }
    }


    /**
     * Allows user to get the url of a page by page ID.
     *
     * This function will always produce a correct and current url to the page
     * despite it possibly having moved from its original position in the page
     * hierarchy.
     *
     * Usage: <?php echo Page::urlById(3); ?>
     *
     * @param   int     $id The id of the page to link to.
     * @return  mixed       Full url of page or error message.
     */
    public static function urlById($id) {
        if (!is_numeric($id) || !is_int($id) || $id <= 0) {
            return '[urlById: id NAN or id <= 0]';
        }

        $page = self::findById($id);

        if (!$page)
            return '[urlById: no page with that id]';

        return $page->url();
    }


    public function slug() {
        return $this->slug;
    }


    public function breadcrumb() {
        return $this->breadcrumb;
    }


    public function updater() {
        return $this->updater;
    }


    public function updaterId() {
        return $this->updater_id;
    }


    /**
     * Returns a set of breadcrumbs as html.
     *
     * @param   string      $separator  The separator between crumbs. Defaults to &gt;
     * @return  string      The breadcrumbs as an html snippet.
     */
    public function breadcrumbs($separator='&gt;') {
        $out = '';
        $url = '';
        $path = '';
        $paths = explode('/', '/'.$this->slug);
        $nb_path = count($paths);

        if ($this->parent() !== false)
            $out .= $this->parent()->_inversedBreadcrumbs($separator);

        return $out.'<span class="breadcrumb-current">'.$this->breadcrumb().'</span>';
    }


    /**
     *
     * @todo Finish _inversedBreadcrumbs PHPDoc
     *
     * @param type $separator
     * @return string
     */
    private function _inversedBreadcrumbs($separator) {
        $out = '<a href="'.$this->url().'" title="'.$this->breadcrumb.'">'.$this->breadcrumb.'</a><span class="breadcrumb-separator">'.$separator.'</span>';

        if ($this->parent() !== false)
            return $this->parent()->_inversedBreadcrumbs($separator).$out;

        return $out;
    }


    /**
     * Returns the subjective "previous" Page.
     *
     * @return mixed    Returns either a Page object or false.
     */
    public function previous() {
        if ($this->parent() !== false) {
            return $this->parent()->children(array(
                'limit' => 1,
                'where' => 'page.position < '.$this->position.' AND page.id < '.$this->id,
                'order' => 'page.position DESC'
            ));
        }

        return false;
    }


    /**
     * Returns the subjective "next" Page.
     *
     * @return mixed    Returns either a Page object or false.
     */
    public function next() {
        if ($this->parent() !== false) {
            return $this->parent()->children(array(
                'limit' => 1,
                'where' => 'page.position > '.$this->position.' AND page.id > '.$this->id,
                'order' => 'page.position ASC'
            ));
        }

        return false;
    }


    /**
     * Counts the number of children belonging to a Page.
     *
     * @fixme Remove dependency on CMS_CONN - not good
     *
     * @param type  $args
     * @param type  $value
     * @param type  $include_hidden
     * @return int  The number of children counted.
     */
    public function childrenCount($args=null, $value=array(), $include_hidden=false) {

        // Collect attributes...
        $where = isset($args['where']) ? $args['where'] : '';
        $order = isset($args['order']) ? $args['order'] : 'position, id';
        $limit = isset($args['limit']) ? $args['limit'] : 0;
        $offset = isset($args['offset']) ? $args['offset'] : 0;

        // Prepare query parts
        $where_string = trim($where) == '' ? '' : "AND ".$where;
        $limit_string = $limit > 0 ? "LIMIT $limit" : '';
        $offset_string = $offset > 0 ? "OFFSET $offset" : '';

        // Prepare SQL
        $sql = 'SELECT COUNT(*) AS nb_rows FROM '.TABLE_PREFIX.'page '
                .'WHERE parent_id = '.$this->id
                ." AND (COALESCE(valid_until, '') = '' OR '".date('Y-m-d H:i:s')."' < valid_until)"
                .' AND (status_id='.Page::STATUS_PUBLISHED
                .($include_hidden ? ' OR status_id='.Page::STATUS_HIDDEN : '').') '
                ."$where_string ORDER BY $order $limit_string $offset_string";

        $stmt = Record::getConnection()->prepare($sql);

        $stmt->execute($value);

        return (int) $stmt->fetchColumn();
    }


    /**
     * Returns the Page object's parent.
     *
     * The option $level parameter allows the user to specify the level on
     * which the found Page object should be.
     *
     * @param   int     $level  Optional level parameter
     * @return  Page    The object's parent.
     */
    public function parent($level=null) {

        // check to see if it's already been retrieved, if not get the parent!
        if ($this->parent === false && $this->parent_id != 0) {
            $this->parent = self::findById($this->parentId());
        }

        if ($level === null)
            return $this->parent;

        if ($level > $this->level())
            return false;
        else if ($this->level() == $level)
            return $this;
        else
            return $this->parent->parent($level);
    }


    public function executionTime() {
        return execution_time();
    }


    /**
     * Allows people to include the parsed content from a Snippet in a Page.
     *
     * The method returns either true or false depending on whether the snippet
     * was found or not.
     *
     * @param   string  $name   Snippet name.
     * @return  boolean         Returns either true or false.
     */
    public function includeSnippet($name) {
        $snippet = Snippet::findByName($name);

        if (false !== $snippet) {
            eval('?'.'>'.$snippet->content_html);
            return true;
        }

        return false;
    }


    private function _loadTags() {

        $this->tags = array();

        $sql = "SELECT tag.id AS id, tag.name AS tag FROM ".TABLE_PREFIX."page_tag AS page_tag, ".TABLE_PREFIX."tag AS tag ".
                "WHERE page_tag.page_id={$this->id} AND page_tag.tag_id = tag.id";

        if (!$stmt = Record::getConnection()->prepare($sql))
            return;

        $stmt->execute();

        // Run!
        while ($object = $stmt->fetchObject())
            $this->tags[$object->id] = $object->tag;
    }


    /**
     * Returns the Tags for this Page.
     *
     * @return array    An array of Tag objects.
     */
    public function tags() {
        if ($this->tags === false)
            $this->_loadTags();

        return $this->tags;
    }


    /**
     * @fixme Merge getTags() with tags() and _loadTags()
     * @deprecated
     * @see Page::tags()
     *
     * @return type
     */
    public function getTags() {
        $tablename_page_tag = self::tableNameFromClassName('PageTag');
        $tablename_tag = self::tableNameFromClassName('Tag');

        $sql = "SELECT tag.id AS id, tag.name AS tag FROM $tablename_page_tag AS page_tag, $tablename_tag AS tag ".
                "WHERE page_tag.page_id={$this->id} AND page_tag.tag_id = tag.id";

        if (!$stmt = Record::getConnection()->prepare($sql))
            return array();

        $stmt->execute();

        // Run!
        $tags = array();
        while ($object = $stmt->fetchObject())
            $tags[$object->id] = $object->tag;

        return $tags;
    }

    public function getColumns() {
        return array(
            'id', 'title', 'slug', 'breadcrumb', 'keywords', 'description',
            'parent_id', 'layout_id', 'behavior_id', 'status_id',
            'created_on', 'published_on', 'valid_until', 'updated_on',
            'created_by_id', 'updated_by_id', 'position', 'is_protected',
            'needs_login'
        );
    }


    /**
     * Return a numerical representation of this page's place in the page hierarchy.
     *
     * This uses the page url as returned by the url() method to check the level.
     * It might not always be what you'd expect.
     *
     * @return int The page's level.
     */
    public function level() {
        if ($this->level === false) {
            $path = $this->path();
            $this->level = empty($path) ? 0 : substr_count($path, '/') + 1;
        }

        return $this->level;
    }


    /**
     * Return formatted date for page. Defaults to 'created on' date.
     *
     * This function works through PHP's strftime() function. Please see
     * http://php.net/strftime for more details on formatting options.
     *
     * Example usage:
     *  '%a, %e %b %Y'        -> Wed, 20 Dec 2006 <- (default)
     *  '%A, %e %B %Y'        -> Wednesday, 20 December 2006
     *  '%B %e, %Y, %H:%M %p' -> December 20, 2006, 08:30 pm
     *
     * @param string    Format string.
     * @param which_one The date field to be used.
     * @return string   Formatted date.
     */
    public function date($format='%a, %e %b %Y', $which_one='created') {
        if ($which_one == 'update' || $which_one == 'updated')
            return strftime($format, strtotime($this->updated_on));
        else if ($which_one == 'publish' || $which_one == 'published')
            return strftime($format, strtotime($this->published_on));
        else if ($which_one == 'valid' || $which_one == 'valid')
            return strftime($format, strtotime($this->valid_until));
        else
            return strftime($format, strtotime($this->created_on));
    }


    /**
     * Return content of the page or a specific part of the page.
     *
     * @param string $part      Part to retrieve content for. Defaults to 'body'.
     * @param bool   $inherit   Check parents for part content if true.
     * @return string           Actual contents of the part.
     */
    public function content($part='body', $inherit=false) {
        // if part exist we generate the content en execute it!
        if (isset($this->part->$part)) {
            ob_start();
            eval('?'.'>'.$this->part->$part->content_html);
            $out = ob_get_contents();
            ob_end_clean();
            return $out;
        }
        else if ($inherit && $this->parent() !== false) {
            return $this->parent()->content($part, true);
        }
    }


    /**
     * Check if a part exists and it has content
     *
     * If inherit is set to true, it checks for the part
     * in this page's parents.
     *
     * @param string $part      Part name.
     * @param bool   $inherit   Check parents for part if true.
     * @return bool             Returns true if part was found or false if nothing was found
     */
    public function hasContent($part, $inherit=false) {
        if (isset($this->part->$part)) {
            $trim = trim($this->part->$part->content_html);
            if (!empty($trim)) {
                return true;
            }

            return false;
        }
        else if ($inherit && $this->parent() !== false) {
            return $this->parent()->hasContent($part, true);
        }
        return false;
    }


    /**
     * Check if a part exists.
     *
     * If inherit is set to true, it checks for the part
     * in this page's parents.
     *
     * @param string $part      Part name.
     * @param bool   $inherit   Check parents for part if true.
     * @return bool             Returns true if part was found or false if nothing was found
     */
    public function partExists($part, $inherit=false) {
        if (isset($this->part->$part)) {
            return true;
        }
        else if ($inherit && $this->parent() !== false) {
            return $this->parent()->partExists($part, true);
        }
        return false;
    }


    /**
     * Return an HTML anchor element for this page.
     *
     * @param string $label     A custom label. Defaults to page title.
     * @param array $options    Array containing attributes to add.
     * @return string           The actual anchor element.
     */
    public function link($label=null, $options='') {
        if ($label == null)
            $label = $this->title();

        return sprintf('<a href="%s" %s>%s</a>', $this->url(true), $options, $label
        );
    }


    /**
     * Allow user to link to a page by ID.
     *
     * This function will always produce a correct and current link to the page
     * despite it possibly having moved from its original position in the page
     * hierarchy.
     *
     * Usage: <?php echo Page::linkById(3); ?>
     *
     * @param int $id The id of the page to link to.
     * @param string $label The label or title of the link.
     * @param string $options Any other HTML options you want to use.
     * @return string XHTML compliant link code or error message.
     */
    public static function linkById($id, $label=null, $options='') {
        if (!is_numeric($id) || !is_int($id) || $id <= 0) {
            return '[linkById: id NAN or id <= 0]';
        }

        $page = self::findById($id);

        if ($label == null) {
            $label = $page->title();
        }

        return sprintf('<a href="%s" %s>%s</a>', $page->url(), $options, $label
        );
    }


    /**
     * Return an array of this page's children.
     *
     * Note: returns a single Page object if only one child exists.
     *
     * @param array $args               Array of key=>value pairs.
     * @param array $value
     * @param boolean $include_hidden   True if children with hidden status should be included.
     * @return mixed                    False, array of Page objects or single Page object.
     */
    public function children($args=null, $value=array(), $include_hidden=false) {

        $page_class = 'Page';

        // Collect attributes...
        $where = isset($args['where']) ? $args['where'] : '';
        $order = isset($args['order']) ? $args['order'] : 'page.position ASC, page.id DESC';
        $offset = isset($args['offset']) ? $args['offset'] : 0;
        $limit = isset($args['limit']) ? $args['limit'] : 0;

        // auto offset generated with the page param
        if ($offset == 0 && isset($_GET['page']))
            $offset = ((int) $_GET['page'] - 1) * $limit;

        // Prepare query parts
        $where_string = trim($where) == '' ? '' : "AND ".$where;
        $limit_string = $limit > 0 ? "LIMIT $limit" : '';
        $offset_string = $offset > 0 ? "OFFSET $offset" : '';


        // Prepare SQL
        $sql = 'SELECT page.*, author.name AS author, author.id AS author_id, updater.name AS updater, updater.id AS updater_id '
                .'FROM '.TABLE_PREFIX.'page AS page '
                .'LEFT JOIN '.TABLE_PREFIX.'user AS author ON author.id = page.created_by_id '
                .'LEFT JOIN '.TABLE_PREFIX.'user AS updater ON updater.id = page.updated_by_id '
                .'WHERE parent_id = '.$this->id.' AND (status_id='.Page::STATUS_PUBLISHED.($include_hidden ? ' OR status_id='.Page::STATUS_HIDDEN : '').') '
                ." AND (COALESCE(valid_until, '') = '' OR '".date('Y-m-d H:i:s')."' < valid_until)"
                ."$where_string ORDER BY $order $limit_string $offset_string";

        self::logQuery($sql);

        $pages = array();

        // hack to be able to redefine the page class with behavior
        if (!empty($this->behavior_id)) {
            // will return Page by default (if not found!)
            $page_class = Behavior::loadPageHack($this->behavior_id);
        }

        // Run!
        if ($stmt = Record::getConnection()->prepare($sql)) {
            $stmt->execute($value);

            while ($object = $stmt->fetchObject()) {
                $page = new $page_class($object, $this);

                // assignParts
                $page->part = self::get_parts($page->id);
                $pages[] = $page;
            }
        }

        if ($limit == 1)
            return isset($pages[0]) ? $pages[0] : false;

        return $pages;
    }


    /**
     * Finds the "login needed" status for the page.
     *
     * @return int Integer corresponding to one of the LOGIN_* constants.
     */
    public function getLoginNeeded() {
        if ($this->needs_login == Page::LOGIN_INHERIT && $this->parent() !== false)
            return $this->parent()->getLoginNeeded();
        else
            return $this->needs_login;
    }


    public function _executeLayout() {

        $sql = 'SELECT content_type, content FROM '.TABLE_PREFIX.'layout WHERE id = :layout_id';

        $stmt = Record::getConnection()->prepare($sql);

        $stmt->execute(array(':layout_id' => $this->_getLayoutId()));

        if ($layout = $stmt->fetchObject()) {
            // if content-type not set, we set html as default
            if ($layout->content_type == '')
                $layout->content_type = 'text/html';

            // set content-type and charset of the page
            header('Content-Type: '.$layout->content_type.'; charset=UTF-8');

            Observer::notify('page_before_execute_layout', $layout);

            // execute the layout code
            eval('?'.'>'.$layout->content);
            // echo $layout->content;
        }
    }


    /**
     * find the layoutId of the page where the layout is set
     */
    private function _getLayoutId() {
        if ($this->layout_id)
            return $this->layout_id;
        else if ($this->parent() !== false)
            return $this->parent()->_getLayoutId();
        else
            exit('You need to set a layout!');
    }


    public function beforeInsert() {
        $this->created_on = date('Y-m-d H:i:s');
        $this->created_by_id = AuthUser::getId();

        $this->updated_on = $this->created_on;
        $this->updated_by_id = $this->created_by_id;

        if ($this->status_id == Page::STATUS_PUBLISHED)
            $this->published_on = date('Y-m-d H:i:s');

        // Make sure we get a default position of 0;
        $this->position = 0;

        // Prevent certain stuff from entering the INSERT statement
        // @todo Replace by more appropriate use of Record::getColumns()
        unset($this->parent);
        unset($this->path);
        unset($this->level);
        unset($this->tags);

        return true;
    }


    public function beforeUpdate() {
        $this->created_on = $this->created_on.' '.$this->created_on_time;
        unset($this->created_on_time);

        if (!empty($this->published_on)) {
            $this->published_on = $this->published_on.' '.$this->published_on_time;
            unset($this->published_on_time);
        }
        else if ($this->status_id == Page::STATUS_PUBLISHED) {
            $this->published_on = date('Y-m-d H:i:s');
        }

        if (!empty($this->valid_until)) {
            $this->valid_until = $this->valid_until.' '.$this->valid_until_time;
            unset($this->valid_until_time);
            if ($this->valid_until < date('Y-m-d H:i:s')) {
                $this->status_id = Page::STATUS_ARCHIVED;
            }
        }
        unset($this->valid_until_time);

        $this->updated_by_id = AuthUser::getId();
        $this->updated_on = date('Y-m-d H:i:s');

        unset($this->path);
        unset($this->level);
        unset($this->tags);
        unset($this->parent);

        return true;
    }


    public function beforeDelete() {
        $ret = false;

        $ret = self::deleteChildrenOf($this->id);
        $ret = PagePart::deleteByPageId($this->id);
        $ret = PageTag::deleteByPageId($this->id);

        return $ret;
    }


    public function setTags($tags) {
        if (is_string($tags))
            $tags = explode(',', $tags);

        $tags = array_map('trim', $tags);

        $current_tags = $this->getTags();

        // no tag before! no tag now! ... nothing to do!
        if (count($tags) == 0 && count($current_tags) == 0)
            return;

        // delete all tags
        if (count($tags) == 0) {
            $tablename = self::tableNameFromClassName('Tag');

            // update count (-1) of those tags
            foreach ($current_tags as $tag)
                Record::update('Tag', array('count' => 'count - 1'), 'name = :tag_name', array(':tag_name' => $tag));

            return Record::deleteWhere('PageTag', 'page_id = :page_id', array(':page_id' => $this->id));
        }
        else {
            $old_tags = array_diff($current_tags, $tags);
            $new_tags = array_diff($tags, $current_tags);

            // insert all tags in the tag table and then populate the page_tag table
            foreach ($new_tags as $index => $tag_name) {
                if (!empty($tag_name)) {
                    // try to get it from tag list, if not we add it to the list
                    if (!$tag = Tag::findByName($tag_name))
                        $tag = new Tag(array('name' => trim($tag_name)));

                    $tag->count++;
                    $tag->save();

                    // create the relation between the page and the tag
                    $tag = new PageTag(array('page_id' => $this->id, 'tag_id' => $tag->id));
                    $tag->save();
                }
            }

            // remove all old tag
            foreach ($old_tags as $index => $tag_name) {
                // get the id of the tag
                $tag = Tag::findByName($tag_name);
                // delete the pivot record
                Record::deleteWhere('PageTag', 'page_id = :page_id AND tag_id = :tag_id', array(':page_id' => $this->id, ':tag_id' => $tag->id));
                $tag->count--;
                $tag->save();
            }
        }
    }


    /**
     * This function should no longer be used.
     *
     * @deprecated
     * @see setTags()
     *
     * @param type $tags
     * @return type
     */
    public function saveTags($tags) {
        return $this->setTags($tags);
    }


    /**
     * This function should no longer be used.
     *
     * @deprecated
     * @see findByPath()
     */
    public static function find_page_by_uri($uri) {
        return Page::findByPath($uri);
    }


    /**
     * This function should no longer be used.
     *
     * @deprecated
     * @see findByPath()
     */
    public static function findByUri($uri, $all = false) {
        return self::findByPath($uri, $all);
    }


    /**
     * Finds a Page record based on it's path.
     * 
     * @param string $path      path/to/page
     * @param bool $all         flag for returning all status types
     * @return mixed            Page object or false
     */
    public static function findByPath($path, $all = false) {
        $path = trim($path, '/');
        $has_behavior = false;

        // adding the home root
        $slugs = array_merge(array(''), explode_path($path));
        $url = '';

        $page = new stdClass;
        $page->id = 0;

        $parent = false;

        foreach ($slugs as $slug) {
            $url = ltrim($url . '/' . $slug, '/');

            $page = self::findBySlug($slug, $parent, $all);
            if ($page instanceof Page) {
                // check for behavior
                if ($page->behavior_id != '') {
                    // add an instance of the behavior with the name of the behavior
                    $params = explode_path(substr($path, strlen($url)));
                    $page->{$page->behavior_id} = Behavior::load($page->behavior_id, $page, $params);

                    return $page;
                }
            } else {
                break;
            }

            $parent = $page;
        } // foreach

        return $page;
    }


    /**
     * find a page by the slug and parent id
     *
     * @param string $slug      page slug to search for
     * @param object $parent    parent object
     * @param bool $all         flag for returning all status types
     * @return mixed            page object or false
     */
    public static function findBySlug($slug, &$parent, $all = false) {

        if (empty($slug)) {
            return self::findOne(array(
                'where' => 'parent_id=0'
            ));
        }

        $parent_id = $parent ? $parent->id : 0;

        if ($all) {
            return self::findOne(array(
                'where' => 'slug = :slug AND parent_id = :parent_id AND (status_id = :status_preview OR status_id = :status_published OR status_id = :status_hidden)',
                'values' => array(':slug' => $slug,
                    ':parent_id' => (int) $parent_id,
                    ':status_preview' => self::STATUS_PREVIEW,
                    ':status_published' => self::STATUS_PUBLISHED,
                    ':status_hidden' => self::STATUS_HIDDEN
                )
            ));
        }
        else {
            return self::findOne(array(
                'where' => 'slug = :slug AND parent_id = :parent_id AND (status_id = :status_published OR status_id = :status_hidden)',
                'values' => array(':slug' => $slug,
                    ':parent_id' => (int) $parent_id,
                    ':status_published' => self::STATUS_PUBLISHED,
                    ':status_hidden' => self::STATUS_HIDDEN
                )
            ));
        }
    }

    /**
     * Finds a Page record based on supplied arguments.
     *
     * Usage:
     *      $page = Page::find('/the/path/to/your/page');
     *      $page = Page::find(array('where' => 'created_by_id=12'));
     *
     * Argument array can contain:
     *      - where
     *      - order
     *      - offset
     *      - limit
     *
     * Return values can be:
     *      - A single Page object
     *      - An array of Page objects which can be empty
     *      - False
     *
     * @param mixed $args   Path string or array of arguments.
     * @return mixed        Page or array of Pages, otherwise false.
     */
    public static function find($options = null) {
        if (!is_array($options)) {
            // Assumes find was called with a uri
            return Page::findByPath($options);
        }

        $options = (is_null($options)) ? array() : $options;
        
        $class_name = get_called_class();
        $table_name = self::tableNameFromClassName($class_name);
        
        // Collect attributes
        $ses    = isset($options['select']) ? trim($options['select'])   : '';
        $frs    = isset($options['from'])   ? trim($options['from'])     : '';
        $jos    = isset($options['joins'])  ? trim($options['joins'])    : '';
        $whs    = isset($options['where'])  ? trim($options['where'])    : '';
        $gbs    = isset($options['group'])  ? trim($options['group'])    : '';
        $has    = isset($options['having']) ? trim($options['having'])   : '';
        $obs    = isset($options['order'])  ? trim($options['order'])    : '';
        $lis    = isset($options['limit'])  ? (int) $options['limit']    : 0;
        $ofs    = isset($options['offset']) ? (int) $options['offset']   : 0;
        $values = isset($options['values']) ? (array) $options['values'] : array();
        
        $single = ($lis === 1) ? true : false;
        $tablename_user = self::tableNameFromClassName('User');

        $jos .= " LEFT JOIN $tablename_user AS creator ON page.created_by_id = creator.id";
        $jos .= " LEFT JOIN $tablename_user AS updater ON page.updated_by_id = updater.id";
        
        // Prepare query parts
        // @todo Remove all "author" mentions and function and replace by more appropriate "creator" name.
        $select     = empty($ses) ? 'SELECT page.*, creator.name AS author, creator.id AS author_id, updater.name AS updater, updater.id AS updater_id, creator.name AS created_by_name, updater.name AS updated_by_name' : "SELECT $ses";
        $from       = empty($frs) ? "FROM $table_name AS page" : "FROM $frs";
        $joins      = empty($jos) ? '' : $jos;
        $where      = empty($whs) ? '' : "WHERE $whs";
        $group_by   = empty($gbs) ? '' : "GROUP BY $gbs";
        $having     = empty($has) ? '' : "HAVING $has";
        $order_by   = empty($obs) ? '' : "ORDER BY $obs";
        $limit      = $lis > 0 ? "LIMIT $lis" : '';
        $offset     = $ofs > 0 ? "OFFSET $ofs" : '';
        
        // Compose the query
        $sql = "$select $from $joins $where $group_by $having $order_by $limit $offset";
        
        $objects = self::findBySql($sql, $values);
        
        return ($single) ? (!empty($objects) ? $objects[0] : false) : $objects;
    }

    private static function findBySql($sql, $values = null) {
        $class_name = get_called_class();
        
        Record::logQuery($sql);
        
        // Prepare and execute
        $stmt = Record::getConnection()->prepare($sql);
        if (!$stmt->execute($values)) {
            return false;
        }

        $page_class = 'Page';
        
        $objects = array();
        while ($page = $stmt->fetchObject('Page')) {
            $parent = $page->parent();
            if (!empty($parent->behavior_id)) {
                // will return Page by default (if not found!)
                $page_class = Behavior::loadPageHack($parent->behavior_id);
            }

            // create the object page
            $page = new $page_class($page, $parent);
            $page->part = self::get_parts($page->id);
            $objects[] = $page;
        }
        
        return $objects;
    }

    public static function findAll($args = null) {
        return self::find($args);
    }


    public static function findById($id) {
        return self::findOne(array(
            'where' => 'page.id = :id',
            'values' => array(':id' => (int) $id)
        ));
    }
    
    
    /**
     * Returns the first Page object with a certain behaviour.
     * 
     * The optional parameter $parentId can be given to narrow the search if it
     * is known.
     * 
     * @param type $name
     * @param type $parentId
     * @return type
     */
    public static function findByBehaviour($name, $parent_id=false) {
        if ($parent_id !== false && is_int($parent_id)) {
            return self::findOne(array(
                'where' => 'behavior_id = :behavior_id AND parent_id = :parent_id',
                'values' => array(':behavior_id' => $name, ':parent_id' => (int) $parent_id)
            ));
        } else {
            return self::findOne(array(
                'where' => 'behavior_id = :behavior_id',
                'values' => array(':behavior_id' => $name)
            ));
        }
    }


    /**
     * Returns a single Page object, retrieved from the database.
     * 
     * @param array $options        Options array containing parameters for the query
     * @return                      Single Page object
     */
    public static function findOne($options = array()) {
        $options['limit'] = 1;
        return self::find($options);
    }


    /**
     * Returns all children (including those with a status other than published) of a Page.
     * 
     * @param   int     $parent_id  ID of the parent Page
     * @return  array               Array containing Page objects
     */
    public static function childrenOf($parent_id) {
        return self::find(array(
            'where' => 'parent_id = :parent_id',
            'values' => array(':parent_id' => $parent_id),
            'order' => 'page.position ASC, page.id DESC'
        ));
    }


    public static function hasChildren($id) {
        return (boolean) self::countFrom('Page', 'parent_id = :parent_id', array(':parent_id' => (int) $id));
    }


    public static function deleteChildrenOf($id) {
        $id = (int) $id;

        if (self::hasChildren($id)) {
            $children = self::childrenOf($id);
            if (is_array($children)) {
                foreach ($children as $child) {
                    if (!$child->delete()) {
                        return false;
                    }
                }
            }
            elseif ($children instanceof Page) { // because Page::childrenOf return directly an object when there is only 1 child...
                if (!$children->delete()) {
                    return false;
                }
            }
        }

        return true;
    }


    public static function cloneTree($page, $parent_id) {
        /* This will hold new id of root of cloned tree. */
        static $new_root_id = false;

        /* Clone passed in page. */
        $clone = Page::findById($page->id);
        $clone->parent_id = (int) $parent_id;
        $clone->id = null;

        if (!$new_root_id) {
            $clone->title .= " (copy)";
            $clone->slug .= "-copy";
        }
        $clone->save();

        /* Also clone the page parts. */
        $page_part = PagePart::findByPageId($page->id);
        if (count($page_part)) {
            foreach ($page_part as $part) {
                $part->page_id = $clone->id;
                $part->id = null;
                $part->save();
            }
        }

        /* Also clone the page tags. */
        $page_tags = $page->getTags();
        if (count($page_tags)) {
            foreach ($page_tags as $tag_id => $tag_name) {
                // create the relation between the page and the tag
                $tag = new PageTag(array('page_id' => $clone->id, 'tag_id' => $tag_id));
                $tag->save();
            }
        }

        /* This gets set only once even when called recursively. */
        if (!$new_root_id) {
            $new_root_id = $clone->id;
        }

        /* Clone and update childrens parent_id to clones new id. */
        if (Page::hasChildren($page->id)) {
            foreach (Page::childrenOf($page->id) as $child) {
                Page::cloneTree($child, $clone->id);
            }
        }

        return $new_root_id;
    }


    public static function get_parts($page_id) {

        $objPart = new stdClass;

        $sql = 'SELECT name, content_html FROM '.TABLE_PREFIX.'page_part WHERE page_id = :page_id';

        if ($stmt = Record::getConnection()->prepare($sql)) {
            $stmt->execute(array(':page_id' => $page_id));

            while ($part = $stmt->fetchObject())
                $objPart->{$part->name} = $part;
        }

        return $objPart;
    }

}

// end Page class
