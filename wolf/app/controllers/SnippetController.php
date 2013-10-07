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
 * @package Controllers
 *
 * @author Martijn van der Kleijn, <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @copyright Martijn van der Kleijn, 2009-2010
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 * Controls adding, editing and deleting of Snippets.
 */
class SnippetController extends Controller {

    public function __construct() {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
        else {
            if ( ! AuthUser::hasPermission('snippet_view') ) {
                Flash::set('error', __('You do not have permission to access the requested page!'));

                if (Setting::get('default_tab') === 'snippet') {
                    redirect(get_url('page'));
                }
                else {
                    redirect(get_url());
                }
            }
        }

        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('snippet/sidebar'));
    }

    public function index() {
        $this->display('snippet/index', array(
                'snippets' => Record::findAllFrom('Snippet', '1=1 ORDER BY position')
        ));
    }

    /**
     * Either adds a Snippet or displays the Add Snippet screen.
     */
    public function add() {
        // check if trying to save
        if (get_request_method() == 'POST') {
            $this->_add();
        }

        // check if user have already enter something
        $snippet = Flash::get('post_data');

        if (empty($snippet)) {
            $snippet = new Snippet;
        }

        $this->display('snippet/edit', array(
                'action'  => 'add',
                'csrf_token' => SecureToken::generateToken(BASE_URL.'snippet/add'),
                'filters' => Filter::findAll(),
                'snippet' => $snippet
        ));
    }

    /**
     * Adds a Snippet.
     */
    private function _add() {
        $data = $_POST['snippet'];
        
        if (!AuthUser::hasPermission('snippet_add')) {
            Flash::set('error', __('You do not have permission to add snippets!'));
            redirect(get_url('snippet'));
        }
        
        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'snippet/add')) {
                Flash::set('post_data', (object) $data);
                Flash::set('error', __('Invalid CSRF token found!'));
                Observer::notify('csrf_token_invalid', AuthUser::getUserName());
                redirect(get_url('snippet/add'));
            }
        }
        else {
            Flash::set('post_data', (object) $data);
            Flash::set('error', __('No CSRF token found!'));
            Observer::notify('csrf_token_not_found', AuthUser::getUserName());
            redirect(get_url('snippet/add'));
        }

        $snippet = new Snippet($data);

        if ( ! $snippet->save()) {
            Flash::set('post_data', (object) $data);
            Flash::set('error', __('Snippet has not been added. Name must be unique!'));
            redirect(get_url('snippet', 'add'));
        }
        else {
            Flash::set('success', __('Snippet has been added!'));
            Observer::notify('snippet_after_add', $snippet);
        }

        // save and quit or save and continue editing?
        if (isset($_POST['commit'])) {
            redirect(get_url('snippet'));
        }
        else {
            redirect(get_url('snippet/edit/'.$snippet->id));
        }
    }

    /**
     * Saves the edited Snippet.
     *
     * @todo Merge _edit() and edit()
     *
     * @param string $id Snippet id.
     */
    public function edit($id) {
        // check if user have already enter something
        $snippet = Flash::get('post_data');

        if (empty($snippet)) {
            $snippet = Snippet::findById($id);
            if ( !$snippet ) {
                Flash::set('error', __('Snippet not found!'));
                redirect(get_url('snippet'));
            }
        }

        // check if trying to save
        if (get_request_method() == 'POST') {
            $this->_edit($id);
        }

        $this->display('snippet/edit', array(
                'action'  => 'edit',
                'csrf_token' => SecureToken::generateToken(BASE_URL.'snippet/edit'),
                'filters' => Filter::findAll(),
                'snippet' => $snippet
        ));
    }

    /**
     * Saves the edited Snippet.
     *
     * @todo Merge _edit() and edit()
     *
     * @param string $id Snippet id.
     */
    private function _edit($id) {
        $data = $_POST['snippet'];
        $data['id'] = $id;
        
        if (!AuthUser::hasPermission('snippet_edit')) {
            Flash::set('error', __('You do not have permission to edit snippets!'));
            redirect(get_url('snippet'));
        }
        
        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'snippet/edit')) {
                Flash::set('post_data', (object) $data);
                Flash::set('error', __('Invalid CSRF token found!'));
                Observer::notify('csrf_token_invalid', AuthUser::getUserName());
                redirect(get_url('snippet/edit/'.$id));
            }
        }
        else {
            Flash::set('post_data', (object) $data);
            Flash::set('error', __('No CSRF token found!'));
            Observer::notify('csrf_token_not_found', AuthUser::getUserName());
            redirect(get_url('snippet/edit/'.$id));
        }

        $snippet = new Snippet($data);

        if ( ! $snippet->save()) {
            Flash::set('post_data', (object) $data);
            Flash::set('error', __('Snippet :name has not been saved. Name must be unique!', array(':name'=>$snippet->name)));
            redirect(get_url('snippet/edit/'.$id));
        }
        else {
            Flash::set('success', __('Snippet :name has been saved!', array(':name'=>$snippet->name)));
            Observer::notify('snippet_after_edit', $snippet);
        }

        // save and quit or save and continue editing?
        if (isset($_POST['commit'])) {
            redirect(get_url('snippet'));
        }
        else {
            redirect(get_url('snippet/edit/'.$id));
        }
    }

    /**
     * Deletes a Snippet.
     *
     * @param string $id Snippet id
     */
    public function delete($id) {
        
        if (!AuthUser::hasPermission('snippet_delete')) {
            Flash::set('error', __('You do not have permission to delete snippets!'));
            redirect(get_url('snippet'));
        }
        
        // find the user to delete
        if ($snippet = Record::findByIdFrom('Snippet', $id)) {
            if ($snippet->delete()) {
                Flash::set('success', __('Snippet :name has been deleted!', array(':name'=>$snippet->name)));
                Observer::notify('snippet_after_delete', $snippet);
            }
            else {
                Flash::set('error', __('Snippet :name has not been deleted!', array(':name'=>$snippet->name)));
            }
        }
        else {
            Flash::set('error', __('Snippet not found!'));
        }

        redirect(get_url('snippet'));
    }

    /**
     * Reorders a Snippet's position relative to other Snippets.
     *
     * @todo Add input cleaning.
     */
    public function reorder() {
        parse_str($_POST['data']);

        foreach ($snippets as $position => $snippet_id) {
            $snippet = Record::findByIdFrom('Snippet', $snippet_id);
            $snippet->position = (int) $position + 1;
            $snippet->save();
        }
    }

}