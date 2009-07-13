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
 * @package frog
 * @subpackage controllers
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * Class UserController
 *
 * @package frog
 * @subpackage controllers
 *
 * @since 0.1
 */
class UserController extends Controller
{

    public function __construct()
    {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn())
            redirect(get_url('login'));
        
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('user/sidebar'));
    }
    
    public function index()
    {
        if ( ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));

            if (Setting::get('default_tab') === 'user')
                redirect(get_url('page'));
            else
                redirect(get_url());
        }
        
        $this->display('user/index', array(
            'users' => User::findAll()
        ));
    }
    
    public function add()
    {
        if ( ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_add();
        
        // check if user have already enter something
        $user = Flash::get('post_data');
        
        if (empty($user))
            $user = new User;
        
        $this->display('user/edit', array(
            'action' => 'add',
            'user' => $user,
            'permissions' => Record::findAllFrom('Permission')
        ));
    }
    
    private function _add()
    {
        $data = $_POST['user'];
        
        Flash::set('post_data', (object) $data);
        
        // check if pass and confirm are egal and >= 5 chars
        if (strlen($data['password']) >= 5 && $data['password'] == $data['confirm'])
        {
            $data['password'] = sha1($data['password']);
            unset($data['confirm']);
        }
        else
        {
            Flash::set('error', __('Password and Confirm are not the same or too small!'));
            redirect(get_url('user/add'));
        }
        
        // check if username >= 3 chars
        if (strlen($data['username']) < 3)
        {
            Flash::set('error', __('Username must contain a minimum of 3 characters!'));
            redirect(get_url('user/add'));
        }
        
        $user = new User($data);
        
        if ($user->save())
        {
            // now we need to add permissions if needed
            if ( ! empty($_POST['user_permission']))
                UserPermission::setPermissionsFor($user->id, $_POST['user_permission']);
            
            Flash::set('success', __('User has been added!'));
            Observer::notify('user_after_add', $user->name);
        }
        else Flash::set('error', __('User has not been added!'));
        
        redirect(get_url('user'));
    }
    
    public function edit($id)
    {
        if ( AuthUser::getId() != $id && ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_edit($id);
        
        if ($user = User::findById($id))
        {
            $this->display('user/edit', array(
                'action' => 'edit', 
                'user' => $user,
                'permissions' => Record::findAllFrom('Permission')
            ));
        }
        else Flash::set('error', __('User not found!'));
        
        redirect(get_url('user'));
        
    } // edit
    
    private function _edit($id)
    {
        $data = $_POST['user'];
        
        // check if user want to change the password
        if (strlen($data['password']) > 0)
        {
            // check if pass and confirm are egal and >= 5 chars
            if (strlen($data['password']) >= 5 && $data['password'] == $data['confirm'])
            {
                $data['password'] = sha1($data['password']);
                unset($data['confirm']);
            }
            else
            {
                Flash::set('error', __('Password and Confirm are not the same or too small!'));
                redirect(get_url('user/edit/'.$id));
            }
        }
        else unset($data['password'], $data['confirm']);
        
        $user = Record::findByIdFrom('User', $id);
        $user->setFromData($data);
        
        if ($user->save())
        {
            if (AuthUser::hasPermission('administrator'))
            {
                // now we need to add permissions
                $data = isset($_POST['user_permission']) ? $_POST['user_permission']: array();
                UserPermission::setPermissionsFor($user->id, $data);
            }
            
            Flash::set('success', __('User has been saved!'));
            Observer::notify('user_after_edit', $user->name);
        }
        else Flash::set('error', __('User has not been saved!'));
        
        if (AuthUser::getId() == $id)
            redirect(get_url('user/edit/'.$id));
        else
            redirect(get_url('user'));
        
    }
    
    public function delete($id)
    {
        if ( ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // security (dont delete the first admin)
        if ($id > 1)
        {
            // find the user to delete
            if ($user = Record::findByIdFrom('User', $id))
            {
                if ($user->delete())
                {
                    Flash::set('success', __('User <strong>:name</strong> has been deleted!', array(':name' => $user->name)));
                    Observer::notify('user_after_delete', $user->name);
                }
                else
                    Flash::set('error', __('User <strong>:name</strong> has not been deleted!', array(':name' => $user->name)));
            }
            else Flash::set('error', __('User not found!'));
        }
        else Flash::set('error', __('Action disabled!'));
        
        redirect(get_url('user'));
    }

} // end UserController class
