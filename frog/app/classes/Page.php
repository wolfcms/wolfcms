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
 * class Page
 *
 * apply methodes for page, layout and snippet of a page
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since  0.1
 *
 * -- TAGS --
 * id()
 * title()
 * breadcrumb()
 * author()
 * slug()
 * url()
 *
 * link([label], [class])
 * date([format])
 *
 * hasContent(part_name, [inherit])
 * content([part_name], [inherit])
 * breadcrumbs([between])
 *
 * children([arguments :limit :offset :order])
 * find(url)
 
 todo:
 
 <r:navigation />

 Renders a list of links specified in the urls attribute according to three states:

 normal specifies the normal state for the link
 here specifies the state of the link when the url matches the current page’s URL
 selected specifies the state of the link when the current page matches is a child of the specified url
 The between tag specifies what should be inserted in between each of the links.

 Usage:
 <r:navigation urls="[Title: url | Title: url | ...]">
   <r:normal><a href="<r:url />"><r:title /></a></r:normal>
   <r:here><strong><r:title /></strong></r:here>
   <r:selected><strong><a href="<r:url />"><r:title /></a></strong></r:selected>
   <r:between> | </r:between>
 </r:navigation>
 
 **/

class Page
{
    const STATUS_DRAFT = 1;
    const STATUS_REVIEWED = 50;
    const STATUS_PUBLISHED = 100;
    const STATUS_HIDDEN = 101;
    
    const LOGIN_NOT_REQUIRED = 0;
    const LOGIN_REQUIRED = 1;
    const LOGIN_INHERIT = 2;

    public $id;
    public $title = '';
    public $breadcrumb;
    public $author;
    public $author_id;
    public $updator;
    public $updator_id;
    public $slug = '';
    public $keywords = '';
    public $description = '';
    public $url = '';
    
    public $parent = false;
    public $level = false;
    public $tags = false;

    public $needs_login;
    
    public function __construct($object, $parent)
    {
        $this->parent = $parent;
        
        foreach ($object as $key => $value) {
            $this->$key = $value;
        }
        
        if ($this->parent)
        {
            $this->setUrl();
        }
    }
    
    protected function setUrl()
    {
        $this->url = trim($this->parent->url .'/'. $this->slug, '/');
    }
    
    public function id() { return $this->id; }
    public function title() { return $this->title; }
    public function breadcrumb() { return $this->breadcrumb; }
    public function author() { return $this->author; }
    public function authorId() { return $this->author_id; }
    public function updator() { return $this->updator; }
    public function updatorId() { return $this->updator_id; }
    public function slug() { return $this->slug; }
    public function keywords() { return $this->keywords; }
    public function description() { return $this->description; }
    public function url() { return BASE_URL . $this->url . ($this->url != '' ? URL_SUFFIX: ''); }
    
    public function level()
    {
        if ($this->level === false)
            $this->level = empty($this->url) ? 0 : substr_count($this->url, '/')+1;
        
        return $this->level;
    }
    
    public function tags()
    {
        if ( ! $this->tags)
            $this->_loadTags();
            
        return $this->tags;
    }
    
    public function link($label=null, $options='')
    {
        if ($label == null)
            $label = $this->title();
        
        return sprintf('<a href="%s" %s>%s</a>',
               $this->url(),
               $options,
               $label
        );
    }
    
    /**
     * http://php.net/strftime
     * exemple (can be useful):
     *  '%a, %e %b %Y'      -> Wed, 20 Dec 2006 <- (default)
     *  '%A, %e %B %Y'      -> Wednesday, 20 December 2006
     *  '%B %e, %Y, %H:%M %p' -> December 20, 2006, 08:30 pm
     */
    public function date($format='%a, %e %b %Y', $which_one='created')
    {
        if ($which_one == 'update' || $which_one == 'updated')
            return strftime($format, strtotime($this->updated_on));
        else if ($which_one == 'publish' || $which_one == 'published')
            return strftime($format, strtotime($this->published_on));
        else
            return strftime($format, strtotime($this->created_on));
    }
    
    public function breadcrumbs($separator='&gt;')
    {
        $url = '';
        $path = '';
        $paths = explode('/', '/'.$this->slug);
        $nb_path = count($paths);
        
        $out = '<div class="breadcrumb">'."\n";
        
        if ($this->parent)
            $out .= $this->parent->_inversedBreadcrumbs($separator);
        
        return $out . '<span class="breadcrumb-current">'.$this->breadcrumb().'</span></div>'."\n";
        
    }
    
    public function hasContent($part, $inherit=false)
    {
        if ( isset($this->part->$part) ) {
            return true;
        }
        else if ( $inherit && $this->parent )
        {
            return $this->parent->hasContent($part, true);
        }
    }
    
    public function content($part='body', $inherit=false)
    {
        // if part exist we generate the content en execute it!
        if (isset($this->part->$part))
        {
            ob_start();
            eval('?>'.$this->part->$part->content_html);
            $out = ob_get_contents();
            ob_end_clean();
            return $out;
        }
        else if ($inherit && $this->parent)
        {
            return $this->parent->content($part, true);
        }
    }
    
    public function previous()
    {
        if ($this->parent)
            return $this->parent->children(array(
                'limit' => 1,
                'where' => 'page.id < '. $this->id,
                'order' => 'page.created_on DESC'
            ));
    }
    
    public function next()
    {
        if ($this->parent)
            return $this->parent->children(array(
                'limit' => 1,
                'where' => 'page.id > '. $this->id,
                'order' => 'page.created_on ASC'
            ));
    }
    
    public function children($args=null, $value=array(), $include_hidden=false)
    {
        global $__FROG_CONN__;
        
        $page_class = 'Page';
        
        // Collect attributes...
        $where   = isset($args['where']) ? $args['where']: '';
        $order   = isset($args['order']) ? $args['order']: 'page.position, page.id';
        $offset  = isset($args['offset']) ? $args['offset']: 0;
        $limit   = isset($args['limit']) ? $args['limit']: 0;
        
        // auto offset generated with the page param
        if ($offset == 0 && isset($_GET['page']))
            $offset = ((int)$_GET['page'] - 1) * $limit;
        
        // Prepare query parts
        $where_string = trim($where) == '' ? '' : "AND ".$where;
        $limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
        
        // Prepare SQL
        $sql = 'SELECT page.*, author.name AS author, author.id AS author_id, updator.name AS updator, updator.id AS updator_id '
             . 'FROM '.TABLE_PREFIX.'page AS page '
             . 'LEFT JOIN '.TABLE_PREFIX.'user AS author ON author.id = page.created_by_id '
             . 'LEFT JOIN '.TABLE_PREFIX.'user AS updator ON updator.id = page.updated_by_id '
             . 'WHERE parent_id = '.$this->id.' AND (status_id='.Page::STATUS_REVIEWED.' OR status_id='.Page::STATUS_PUBLISHED.($include_hidden ? ' OR status_id='.Page::STATUS_HIDDEN: '').') '
             . "$where_string ORDER BY $order $limit_string";
        
        $pages = array();
        
        // hack to be able to redefine the page class with behavior
        if ( ! empty($this->behavior_id))
        {
            // will return Page by default (if not found!)
            $page_class = Behavior::loadPageHack($this->behavior_id);
        }
        
        // Run!
        if ($stmt = $__FROG_CONN__->prepare($sql))
        {
            $stmt->execute($value);
            
            while ($object = $stmt->fetchObject())
            {
                $page = new $page_class($object, $this);
                
                // assignParts
                $page->part = get_parts($page->id);
                $pages[] = $page;
            }
        }
        
        if ($limit == 1)
            return isset($pages[0]) ? $pages[0]: false;
        
        return $pages;
    }
    
    public function childrenCount($args=null, $value=array(), $include_hidden=false)
    {
        global $__FROG_CONN__;
        
        // Collect attributes...
        $where   = isset($args['where']) ? $args['where']: '';
        $order   = isset($args['order']) ? $args['order']: 'position, id';
        $limit   = isset($args['limit']) ? $args['limit']: 0;
        $offset  = 0;
        
        // Prepare query parts
        $where_string = trim($where) == '' ? '' : "AND ".$where;
        $limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
        
        // Prepare SQL
        $sql = 'SELECT COUNT(*) AS nb_rows FROM '.TABLE_PREFIX.'page '
             . 'WHERE parent_id = '.$this->id.' AND (status_id='.Page::STATUS_REVIEWED.' OR status_id='.Page::STATUS_PUBLISHED.($include_hidden ? ' OR status_id='.Page::STATUS_HIDDEN: '').') '
             . "$where_string ORDER BY $order $limit_string";
        
        $stmt = $__FROG_CONN__->prepare($sql);
        $stmt->execute($value);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function find($uri) { return find_page_by_uri($uri); }
    
    public function parent($level=null)
    {
        if ($level === null)
            return $this->parent;
        
        if ($level > $this->level)
            return false;
        else if ($this->level == $level)
            return $this;
        else
            return $this->parent($level);
    }
    
    public function includeSnippet($name)
    {
        global $__FROG_CONN__;
        
        $sql = 'SELECT content_html FROM '.TABLE_PREFIX.'snippet WHERE name LIKE ?';
        
        $stmt = $__FROG_CONN__->prepare($sql);
        $stmt->execute(array($name));
        
        if ($snippet = $stmt->fetchObject())
        {
            eval('?>'.$snippet->content_html);
        }
    }
    
    public function executionTime()
    {
        return execution_time();
    }
    
    // Private --------------------------------------------------------------
    
    private function _inversedBreadcrumbs($separator)
    {
        $out = '<a href="'.$this->url().'" title="'.$this->breadcrumb.'">'.$this->breadcrumb.'</a> <span class="breadcrumb-separator">'.$separator.'</span> '."\n";
    
        if ($this->parent)
            return $this->parent->_inversedBreadcrumbs($separator) . $out;
        
        return $out;
    }
     
    public function _executeLayout()
    {
        global $__FROG_CONN__;
        
        $sql = 'SELECT content_type, content FROM '.TABLE_PREFIX.'layout WHERE id = ?';
        
        $stmt = $__FROG_CONN__->prepare($sql);
        $stmt->execute(array($this->_getLayoutId()));
        
        if ($layout = $stmt->fetchObject())
        {
            // if content-type not set, we set html as default
            if ($layout->content_type == '')
                $layout->content_type = 'text/html';
            
            // set content-type and charset of the page
            header('Content-Type: '.$layout->content_type.'; charset=UTF-8');
            
            // execute the layout code
            eval('?>'.$layout->content);
        }
    }
    
    /**
     * find the layoutId of the page where the layout is set
     */
    private function _getLayoutId()
    {
        if ($this->layout_id)
            return $this->layout_id;
        else if ($this->parent)
            return $this->parent->_getLayoutId();
        else
            exit ('You need to set a layout!');
    }

    /**
     * Finds the "login needed" status for the page.
     *
     * @return int Integer corresponding to one of the LOGIN_* constants.
     */
    public function getLoginNeeded()
    {
        if ($this->needs_login == Page::LOGIN_INHERIT && $this->parent)
            return $this->parent->getLoginNeeded();
        else
            return $this->needs_login;
    }
    
    private function _loadTags()
    {
        global $__FROG_CONN__;
        $this->tags = array();
        
        $sql = "SELECT tag.id AS id, tag.name AS tag FROM ".TABLE_PREFIX."page_tag AS page_tag, ".TABLE_PREFIX."tag AS tag ".
               "WHERE page_tag.page_id={$this->id} AND page_tag.tag_id = tag.id";
        
        if ( ! $stmt = $__FROG_CONN__->prepare($sql))
            return;
            
        $stmt->execute();
        
        // Run!
        while ($object = $stmt->fetchObject())
             $this->tags[$object->id] = $object->tag;
    }

} // end Page class
