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
 * @package Controllers
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Martijn van der Kleijn, 2008, 2009, 2010
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 * Class PagesController
 */
class PageController extends Controller {


    public function __construct() {
        AuthUser::load();
        if (!AuthUser::isLoggedIn())
            redirect(get_url('login'));
    }


    public function index() {
        $this->setLayout('backend');
        $this->display('page/index', array(
            'root' => Record::findByIdFrom('Page', 1),
            'content_children' => $this->children(1, 0, true)
        ));
    }


    /**
     * Action to add a page.
     *
     * @param int $parent_id The page id for the new page's parent. Defaults to page 1.
     * @return <type>
     */
    public function add($parent_id=1) {
        // Check if trying to save.
        if (get_request_method() == 'POST')
            return $this->_store('add');

        // If not trying to save, display "Add page" view.
        $data = Flash::get('post_data');
        $page = new Page($data);
        $page->parent_id = $parent_id;
        $page->status_id = Setting::get('default_status_id');
        $page->needs_login = Page::LOGIN_INHERIT;

        $page_parts = Flash::get('post_parts_data');

        if (empty($page_parts)) {
            // Check if we have a big sister.
            $big_sister = Record::findOneFrom('Page', 'parent_id=? ORDER BY id DESC', array($parent_id));
            if ($big_sister) {
                // Get list of parts create the same for the new little sister
                $big_sister_parts = Record::findAllFrom('PagePart', 'page_id=? ORDER BY id', array($big_sister->id));
                $page_parts = array();
                foreach ($big_sister_parts as $parts) {
                    $page_parts[] = new PagePart(array(
                                'name' => $parts->name,
                                'filter_id' => Setting::get('default_filter_id')
                            ));
                }
            }
            else {
                $page_parts = array(new PagePart(array('filter_id' => Setting::get('default_filter_id'))));
            }
        }

        // Display actual view.
        $this->setLayout('backend');
        $this->display('page/edit', array(
            'action' => 'add',
            'csrf_token' => SecureToken::generateToken(BASE_URL.'page/add'),
            'page' => $page,
            'tags' => array(),
            'filters' => Filter::findAll(),
            'behaviors' => Behavior::findAll(),
            'page_parts' => $page_parts,
            'layouts' => Record::findAllFrom('Layout'))
        );
    }


    /**
     * Ajax action to add a part.
     */
    public function addPart() {
        header('Content-Type: text/html; charset: utf-8');

        $data = isset($_POST['part']) ? $_POST['part'] : array();
        $data['name'] = isset($data['name']) ? trim($data['name']) : '';
        $data['index'] = isset($data['index']) ? $data['index'] : 1;

        echo $this->_getPartView($data['index'], $data['name']);
    }


    /**
     * Action to edit a page.
     *
     * @aram int $id Page ID for page to edit.
     * @return <type>
     */
    public function edit($id) {
        if (!is_numeric($id)) {
            redirect(get_url('page'));
        }

        // Check if trying to save.
        if (get_request_method() == 'POST')
            return $this->_store('edit', $id);

        $page = Page::findById($id);

        if (!$page) {
            Flash::set('error', __('Page not found!'));
            redirect(get_url('page'));
        }

        // check for protected page and editor user
        if (!AuthUser::hasPermission('page_edit') || (!AuthUser::hasPermission('admin_edit') && $page->is_protected)) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url('page'));
        }

        // Encode the string to prevent page title input break
        // Unless people specify "Allow html in title" in the backend.
        // Then only replace double quotes.
        if (!Setting::get('allow_html_title')) {
            $page->title = html_encode($page->title);
        }
        else {
            $page->title = str_replace('"', '&quot;', $page->title);
        }

        // find all page_part of this pages
        $page_parts = PagePart::findByPageId($id);

        if (empty($page_parts))
            $page_parts = array(new PagePart);

        // display things ...
        $this->setLayout('backend');
        $this->display('page/edit', array(
            'action' => 'edit',
            'csrf_token' => SecureToken::generateToken(BASE_URL.'page/edit/'.$page->id),
            'page' => $page,
            'tags' => $page->getTags(),
            'filters' => Filter::findAll(),
            'behaviors' => Behavior::findAll(),
            'page_parts' => $page_parts,
            'layouts' => Record::findAllFrom('Layout', '1=1 ORDER BY position'))
        );
    }


    /**
     * Used to delete a page.
     *
     * @todo make sure we not only delete the page but also all parts and all children!
     *
     * @param int $id Id of page to delete
     */
    public function delete($id) {
        // Sanity checks
        use_helper('Validate');
        if (!Validate::numeric($id)) {
            Flash::set('error', __('Invalid input found!'));
            redirect(get_url());
        }
        
        // CSRF checks
        if (isset($_GET['csrf_token'])) {
            $csrf_token = $_GET['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'page/delete/'.$id)) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url('page'));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url('page'));
        }
        
        // security (dont delete the root page)
        if ($id > 1) {
            // find the page to delete
            if ($page = Record::findByIdFrom('Page', $id)) {
                // check for permission to delete this page
                if (!AuthUser::hasPermission('page_delete') && $page->is_protected) {
                    Flash::set('error', __('You do not have permission to access the requested page!'));
                    redirect(get_url('page'));
                }

                // need to delete all page_parts too !!
                PagePart::deleteByPageId($id);

                if ($page->delete()) {
                    Observer::notify('page_delete', $page);
                    Flash::set('success', __('MSG_PAGE_DELETED_SUCCESS', array(':title' => $page->title)));
                }
                else
                    Flash::set('error', __('Page :title has not been deleted!', array(':title' => $page->title)));
            }
            else
                Flash::set('error', __('Page not found!'));
        }
        else
            Flash::set('error', __('Action disabled!'));

        redirect(get_url('page'));
    }


    /**
     * Action to return a list View of all first level children of a page.
     *
     * @todo improve phpdoc desc
     *
     * @param <type> $parent_id
     * @param <type> $level
     * @param <type> $return
     * @return View
     */
    function children($parent_id, $level, $return=false) {
        $expanded_rows = isset($_COOKIE['expanded_rows']) ? explode(',', $_COOKIE['expanded_rows']) : array();

        // get all children of the page (parent_id)
        $childrens = Page::childrenOf($parent_id);

        foreach ($childrens as $index => $child) {
            $childrens[$index]->has_children = Page::hasChildren($child->id);
            $childrens[$index]->is_expanded = in_array($child->id, $expanded_rows);

            if ($childrens[$index]->is_expanded)
                $childrens[$index]->children_rows = $this->children($child->id, $level + 1, true);
        }

        $content = new View('page/children', array(
                    'childrens' => $childrens,
                    'level' => $level + 1,
                ));

        if ($return)
            return $content;

        echo $content;
    }


    /**
     * Ajax action to reorder (page->position) a page.
     *
     * All the children of the new page->parent_id have to be updated
     * and all nested tree have to be rebuild.
     *
     */
    function reorder() {
        $pages = $_POST['page'];

        $i = 1;
        foreach ($pages as $page_id => $parent_id) {
            if ($parent_id == 0) {
                $parent_id = 1;
            }

            $page = Record::findByIdFrom('Page', $page_id);
            $page->position = (int) $i;
            $page->parent_id = (int) $parent_id;
            $page->save();
            $i++;
        }
    }


    /**
     * Ajax action to copy a page or page tree.
     *
     */
    function copy() {
        $original_id = $_POST['originalid'];

        $page = Record::findByIdFrom('Page', $original_id);
        $new_root_id = Page::cloneTree($page, $page->parent_id);

        $page = Record::findByIdFrom('Page', $new_root_id);
        $page->position += 1;
        $page->save();

        $newUrl = URL_PUBLIC;
        $newUrl .= ( USE_MOD_REWRITE == false) ? '?' : '';
        $newUrl .= $page->path();
        $newUrl .= ( $page->path() != '') ? URL_SUFFIX : '';

        $newData = array($new_root_id,
            get_url('page/edit/'.$new_root_id),
            $page->title(),
            $page->slug(),
            $newUrl,
            get_url('page/add', $new_root_id),
            get_url('page/delete/'.$new_root_id.'?csrf_token='.SecureToken::generateToken(BASE_URL.'page/delete/'.$new_root_id)));
        echo implode('||', $newData);
    }

    //  Private methods  -----------------------------------------------------


    /**
     *
     * @param <type> $index
     * @param <type> $name
     * @param <type> $filter_id
     * @param <type> $content
     * @return <type>
     */
    private function _getPartView($index=1, $name='', $filter_id='', $content='') {
        $page_part = new PagePart(array(
                    'name' => $name,
                    'filter_id' => $filter_id,
                    'content' => $content)
        );

        return $this->render('page/part_edit', array(
            'index' => $index,
            'page_part' => $page_part
        ));
    }


    /**
     * Runs checks and stores a page.
     *
     * @param string $action   What kind of action this is: add or edit.
     * @param mixed $id        Page to edit if any.
     */
    private function _store($action, $id=false) {
        // Sanity checks
        if ($action == 'edit' && !$id)
            throw new Exception('Trying to edit page when $id is false.');

        use_helper('Validate');
        $data = $_POST['page'];
        $data['is_protected'] = !empty($data['is_protected']) ? 1 : 0;
        Flash::set('post_data', (object) $data);

        // Add pre-save checks here
        $errors = false;

        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            $csrf_id = '';
            if ($action === 'edit') { $csrf_id = '/'.$id; }
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'page/'.$action.$csrf_id)) {
                $errors[] = __('Invalid CSRF token found!');
            }
        }
        else {
            $errors[] = __('No CSRF token found!');
        }

        $data['title'] = trim($data['title']);
        if (empty($data['title'])) {
            $errors[] = __('You have to specify a title!');
        }

        // Make sure we have a slug
        if (isset($data['slug'])) {
            $data['slug'] = trim($data['slug']);
        }
        else {
            $data['slug'] = '';
        }
        
        if (empty($data['slug']) && $id != '1') {
            $errors[] = __('You have to specify a slug!');
        }
        else {
            if ($data['slug'] == ADMIN_DIR) {
                $errors[] = __('You cannot have a slug named :slug!', array(':slug' => ADMIN_DIR));
            }
            // Make sure home's slug is passed ok, but other slugs are validated properly
            if (($id != '1' && (!Validate::slug($data['slug']) || empty($data['slug']))) || ($id == '1' && !empty($data['slug']))) {
                $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'slug'));
            }
            if (Record::existsIn('Page','parent_id = :parent_id AND slug = :slug AND id <> :id',array(':parent_id' => $data['parent_id'], ':slug' => $data['slug'], ':id' => $id))) {
                $errors[] = __('Page with slug <b>:slug</b> already exists!', array(':slug' => $data['slug']));
            }            
        }

        // Check all numerical fields for a page
        $fields = array('parent_id', 'layout_id', 'needs_login');
        foreach ($fields as $field) {
            if (!Validate::digit($data[$field])) {
                $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => $field));
            }
        }

        // Check all date fields for a page
        $fields = array('created_on', 'published_on', 'valid_until');
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
                if (!empty($data[$field]) && !(bool) preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', (string) $data[$field])) {
                    $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => $field));
                }
            }
        }

        // Check all time fields for a page
        $fields = array('created_on_time', 'published_on_time', 'valid_until_time');
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
                if (!empty($data[$field]) && !(bool) preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/D', (string) $data[$field])) {
                    $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => $field));
                }
            }
        }

        // Check alphanumerical fields
        $fields = array('keywords', 'description');
        foreach ($fields as $field) {
            use_helper('Kses');
            $data[$field] = kses(trim($data[$field]), array());
            /*
            if (!empty($data[$field]) && !Validate::alpha_comma($data[$field])) {
                $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => $field));
            }
             * 
             */
        }

        // Check behaviour_id field
        if (!empty($data['behaviour_id']) && !Validate::slug($data['behaviour_id'])) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'behaviour_id'));
        }

        // Check is_protected field
        if (!empty($data['is_protected']) && !AuthUser::hasPermission('admin_edit')) {
            $errors[] = __('Only administrators can change <b>protected</b> status of pages!');
            unset($data['is_protected']);
        }
        
        // Make sure the title doesn't contain HTML
        if (Setting::get('allow_html_title') == 'off') {
            use_helper('Kses');
            $data['title'] = kses(trim($data['title']), array());
        }

        // Create the page object to be manipulated and populate data
        if ($action == 'add') {
            $page = new Page($data);
        }
        else {
            $page = Record::findByIdFrom('Page', $id);
            $page->setFromData($data);
        }

        // Upon errors, rebuild original page and return to screen with errors
        if (false !== $errors) {
            $tags = $_POST['page_tag'];
            
            // Rebuild time fields
            if (isset($page->created_on)) {
                $page->created_on = $page->created_on.' '.$page->created_on_time;
            }
            
            if (isset($page->published_on)) {
                $page->published_on = $page->published_on.' '.$page->published_on_time;
            }
            
            if (isset($page->valid_until)) {
                $page->valid_until = $page->valid_until.' '.$page->valid_until_time;
            }

            // Rebuild parts
            $part = $_POST['part'];
            if (!empty($part)) {
                $tmp = false;
                foreach ($part as $key => $val) {
                    $tmp[$key] = (object) $val;
                }
                $part = $tmp;
            }

            // Set the errors to be displayed.
            Flash::setNow('error', implode('<br/>', $errors));

            // display things ...
            $this->setLayout('backend');
            $this->display('page/edit', array(
                'action' => $action,
                'csrf_token' => SecureToken::generateToken(BASE_URL.'page/'.$action.$csrf_id),
                'page' => (object) $page,
                'tags' => $tags,
                'filters' => Filter::findAll(),
                'behaviors' => Behavior::findAll(),
                'page_parts' => (object) $part,
                'layouts' => Record::findAllFrom('Layout'))
            );
        }

        // Notify
        if ($action == 'add') {
            Observer::notify('page_add_before_save', $page);
        }
        else {
            Observer::notify('page_edit_before_save', $page);
        }

        // Time to actually save the page
        // @todo rebuild this so parts are already set before save?
        // @todo determine lazy init impact
        if ($page->save()) {
            // Get data for parts of this page
            $data_parts = $_POST['part'];
            Flash::set('post_parts_data', (object) $data_parts);

            if ($action == 'edit') {
                $old_parts = PagePart::findByPageId($id);

                // check if all old page part are passed in POST
                // if not ... we need to delete it!
                foreach ($old_parts as $old_part) {
                    $not_in = true;
                    foreach ($data_parts as $part_id => $data) {
                        $data['name'] = trim($data['name']);
                        if ($old_part->name == $data['name']) {
                            $not_in = false;

                            // this will not really create a new page part because
                            // the id of the part is passed in $data
                            $part = new PagePart($data);
                            $part->page_id = $id;

                            Observer::notify('part_edit_before_save', $part);
                            $part->save();
                            Observer::notify('part_edit_after_save', $part);

                            unset($data_parts[$part_id]);

                            break;
                        }
                    }

                    if ($not_in)
                        $old_part->delete();
                }
            }

            // add the new parts
            foreach ($data_parts as $data) {
                $data['name'] = trim($data['name']);
                $part = new PagePart($data);
                $part->page_id = $page->id;
                Observer::notify('part_add_before_save', $part);
                $part->save();
                Observer::notify('part_add_after_save', $part);
            }

            // save tags
            $page->saveTags($_POST['page_tag']['tags']);

            Flash::set('success', __('Page has been saved!'));
        }
        else {
            Flash::set('error', __('Page has not been saved!'));
            $url = 'page/';
            $url .= ( $action == 'edit') ? 'edit/'.$id : 'add/';
            redirect(get_url($url));
        }

        if ($action == 'add') {
            Observer::notify('page_add_after_save', $page);
        }
        else {
            Observer::notify('page_edit_after_save', $page);
        }

        // save and quit or save and continue editing ?
        if (isset($_POST['commit'])) {
            redirect(get_url('page'));
        }
        else {
            redirect(get_url('page/edit/'.$page->id));
        }
    }

}

// end PageController class
