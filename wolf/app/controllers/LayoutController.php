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
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * Class LayoutController
 */
class LayoutController extends Controller {


    function __construct() {
        AuthUser::load();
        if (!AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
        else {
            if (!AuthUser::hasPermission('layout_view')) {
                Flash::set('error', __('You do not have permission to access the requested page!'));

                if (Setting::get('default_tab') === 'layout')
                    redirect(get_url('page'));
                else
                    redirect(get_url());
            }
        }

        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('layout/sidebar'));
    }


    function index() {
        $this->display('layout/index', array(
            'layouts' => Record::findAllFrom('Layout', '1=1 ORDER BY position')
        ));
    }


    function add() {
        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_add();

        // check if user have already enter something
        $layout = Flash::get('post_data');

        if (empty($layout))
            $layout = new Layout;

        $this->display('layout/edit', array(
            'csrf_token' => SecureToken::generateToken(BASE_URL.'layout/add'),
            'action' => 'add',
            'layout' => $layout
        ));
    }


    function _add() {
        $data = $_POST['layout'];
        Flash::set('post_data', (object) $data);

        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'layout/add')) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url('layout/add'));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url('layout/add'));
        }

        if (empty($data['name'])) {
            Flash::set('error', __('You have to specify a name!'));
            redirect(get_url('layout/add'));
        }

        if (empty($data['content_type'])) {
            Flash::set('error', __('You have to specify a content-type!'));
            redirect(get_url('layout/add'));
        }

        $layout = new Layout($data);

        if (!$layout->save()) {
            Flash::set('error', __('Layout has not been added. Name must be unique!'));
            redirect(get_url('layout/add'));
        }
        else {
            Flash::set('success', __('Layout has been added!'));
            Observer::notify('layout_after_add', $layout);
        }

        // save and quit or save and continue editing?
        if (isset($_POST['commit']))
            redirect(get_url('layout'));
        else
            redirect(get_url('layout/edit/'.$layout->id));
    }


    function edit($id) {
        if (!$layout = Layout::findById($id)) {
            Flash::set('error', __('Layout not found!'));
            redirect(get_url('layout'));
        }

        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_edit($id);

        // display things...
        $this->display('layout/edit', array(
            'csrf_token' => SecureToken::generateToken(BASE_URL.'layout/edit'),
            'action' => 'edit',
            'layout' => $layout
        ));
    }


    /**
     * @todo Merge _add() and _edit() into one _store()
     *
     * @param <type> $id
     */
    function _edit($id) {
        $layout = Record::findByIdFrom('Layout', $id);
        $layout->setFromData($_POST['layout']);

        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'layout/edit')) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url('layout/edit/'.$id));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url('layout/edit/'.$id));
        }

        if (!$layout->save()) {
            Flash::set('error', __('Layout has not been saved. Name must be unique!'));
            redirect(get_url('layout/edit/'.$id));
        }
        else {
            Flash::set('success', __('Layout has been saved!'));
            Observer::notify('layout_after_edit', $layout);
        }

        // save and quit or save and continue editing?
        if (isset($_POST['commit']))
            redirect(get_url('layout'));
        else
            redirect(get_url('layout/edit/'.$id));
    }


    function delete($id) {
        // find the layout to delete
        if ($layout = Record::findByIdFrom('Layout', $id)) {
            if ($layout->isUsed())
                Flash::set('error', __('Layout <b>:name</b> is in use! It CAN NOT be deleted!', array(':name' => $layout->name)));
            else if ($layout->delete()) {
                Flash::set('success', __('Layout <b>:name</b> has been deleted!', array(':name' => $layout->name)));
                Observer::notify('layout_after_delete', $layout);
            }
            else
                Flash::set('error', __('Layout <b>:name</b> has not been deleted!', array(':name' => $layout->name)));
        }
        else
            Flash::set('error', __('Layout not found!'));

        redirect(get_url('layout'));
    }


    function reorder() {
        parse_str($_POST['data']);

        foreach ($layouts as $position => $layout_id) {
            $layout = Record::findByIdFrom('Layout', $layout_id);
            $layout->position = (int) $position + 1;
            $layout->save();
        }
    }

}
