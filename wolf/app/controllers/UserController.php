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
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Martijn van der Kleijn, 2008,2009,2010
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 * Class UserController
 */
class UserController extends Controller {


    public function __construct() {
        AuthUser::load();
        if (!AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }

        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('user/sidebar'));
    }


    public function index() {
        if (!AuthUser::hasPermission('user_view')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));

            if (Setting::get('default_tab') === 'user') {
                redirect(get_url('page'));
            }
            else {
                redirect(get_url());
            }
        }

        $this->display('user/index', array(
            'users' => User::findAll()
        ));
    }


    public function add() {
        if (!AuthUser::hasPermission('user_add')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }

        // check if trying to save
        if (get_request_method() == 'POST') {
            return $this->_add();
        }

        // check if user have already enter something
        $user = Flash::get('post_data');

        if (empty($user)) {
            $user = new User;
            $user->language = Setting::get('language');
        }

        $this->display('user/edit', array(
            'action' => 'add',
            'csrf_token' => SecureToken::generateToken(BASE_URL.'user/add'),
            'user' => $user,
            'roles' => Record::findAllFrom('Role')
        ));
    }


    private function _add() {
        use_helper('Validate');
        $data = $_POST['user'];

        // Add pre-save checks here
        $errors = false;

        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'user/add')) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url('user/add'));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url('user/add'));
        }

        // check if pass and confirm are equal and >= 5 chars
        if (strlen($data['password']) >= 5 && $data['password'] == $data['confirm']) {
            //$data['password'] = sha1($data['password']);
            unset($data['confirm']);
        }
        else {
            Flash::set('error', __('Password and Confirm are not the same or too small!'));
            redirect(get_url('user/add'));
        }

        // check if username >= 3 chars
        if (strlen($data['username']) < 3) {
            Flash::set('error', __('Username must contain a minimum of 3 characters!'));
            redirect(get_url('user/add'));
        }

        // check if username != password
        if ($data['username'] == $data['password']) {
            Flash::set('error', __('The password must not be the same as the username!'));
            redirect(get_url('user/add'));
        }

        // Check alphanumerical fields
        $fields = array('username');
        foreach ($fields as $field) {
            if (!empty($data[$field]) && !Validate::alphanum_space($data[$field])) {
                $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => $field));
                // Reset to prevent XSS
                $data[$field] = '';
            }
        }
        
        if (!empty($data['name']) && !Validate::alphanum_space($data['name'], true)) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'name'));
        }

        if (!empty($data['email']) && !Validate::email($data['email'])) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'email'));
            // Reset to prevent XSS
            $data['email'] = '';
        }

        if (!empty($data['language']) && !Validate::alpha_dash($data['language'])) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'language'));
            // Reset to prevent XSS
            // @todo Remove hardcoded reset to 'en' language
            $data['language'] = 'en';
        }
        
        // Check if user with the same 'username' already exists
        if ( Record::existsIn('User', 'username=:username', array( ':username' => $data['username'] )) ) {
            $errors[] = __('Username <b>:username</b> is already in use, please choose other!', array( ':username' => $data['username'] ));
        }

        Flash::set('post_data', (object) $data);

        if ($errors !== false) {
            // Set the errors to be displayed.
            Flash::set('error', implode('<br/>', $errors));
            redirect(get_url('user/add'));
        }

        $user = new User($data);

        // Generate a salt and create encrypted password
        $user->salt = AuthUser::generateSalt();
        $user->password = AuthUser::generateHashedPassword($user->password, $user->salt);

        if ($user->save()) {
            // now we need to add roles if needed
            if (!empty($_POST['user_role']))
                UserRole::setRolesFor($user->id, $_POST['user_role']);

            Flash::set('success', __('User has been added!'));
            Observer::notify('user_after_add', $user->name, $user->id);
        }
        else {
            Flash::set('error', __('User has not been added!'));
        }

        redirect(get_url('user'));
    }


    public function edit($id) {
        if (AuthUser::getId() != $id && !AuthUser::hasPermission('user_edit')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }

        // check if trying to save
        if (get_request_method() == 'POST') {
            return $this->_edit($id);
        }

        if ($user = User::findById($id)) {
            $this->display('user/edit', array(
                'action' => 'edit',
                'csrf_token' => SecureToken::generateToken(BASE_URL.'user/edit/'.$id),
                'user' => $user,
                'roles' => Record::findAllFrom('Role')
            ));
        }
        else {
            Flash::set('error', __('User not found!'));
        }

        redirect(get_url('user'));
    }

// edit


    /**
     * @todo merge _add() and _edit() into one _store()
     *
     * @param <type> $id
     */
    private function _edit($id) {
        use_helper('Validate');
        $data = $_POST['user'];
        Flash::set('post_data', (object) $data);

        // Add pre-save checks here
        $errors = false;

        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'user/edit/'.$id)) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url('user/edit/'.$id));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url('user/edit/'.$id));
        }

        // check if user want to change the password
        if (strlen($data['password']) > 0) {
            // check if pass and confirm are egal and >= 5 chars
            if (strlen($data['password']) >= 5 && $data['password'] == $data['confirm']) {
                unset($data['confirm']);
            }
            else {
                Flash::set('error', __('Password and Confirm are not the same or too small!'));
                redirect(get_url('user/edit/'.$id));
            }
        }
        else {
            unset($data['password'], $data['confirm']);
        }

        // Check alphanumerical fields
        $fields = array('username');
        foreach ($fields as $field) {
            if (!empty($data[$field]) && !Validate::alphanum_space($data[$field])) {
                $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => $field));
            }
        }

        if (!empty($data['name']) && !Validate::alphanum_space($data['name'], true)) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'name'));
        }

        if (!empty($data['email']) && !Validate::email($data['email'])) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'email'));
        }

        if (!empty($data['language']) && !Validate::alpha_dash($data['language'])) {
            $errors[] = __('Illegal value for :fieldname field!', array(':fieldname' => 'language'));
        }
        
        // Check if user with the same 'username' already exists
        if ( Record::existsIn('User', 'username=:username', array( ':username' => $data['username'] )) ) {
            $errors[] = __('Username <b>:username</b> is already in use, please choose other!', array( ':username' => $data['username'] ));
        }
        
        if ($errors !== false) {
            // Set the errors to be displayed.
            Flash::set('error', implode('<br/>', $errors));
            redirect(get_url('user/edit/'.$id));
        }

        $user = Record::findByIdFrom('User', $id);
        if (isset($data['password'])) {
            if (empty($user->salt)) {
                $user->salt = AuthUser::generateSalt();
            }
            $data['password'] = AuthUser::generateHashedPassword($data['password'], $user->salt);
        }

        $user->setFromData($data);

        if ($user->save()) {
            if (AuthUser::hasPermission('user_edit')) {
                // now we need to add roles
                $data = isset($_POST['user_role']) ? $_POST['user_role'] : array();
                UserRole::setRolesFor($user->id, $data);
            }

            Flash::set('success', __('User has been saved!'));
            Observer::notify('user_after_edit', $user->name, $user->id);
        }
        else {
            Flash::set('error', __('User has not been saved!'));
        }

        if (AuthUser::getId() == $id) {
            redirect(get_url('user/edit/'.$id));
        }
        else {
            redirect(get_url('user'));
        }
    }


    public function delete($id) {
        if (!AuthUser::hasPermission('user_delete')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // Sanity checks
        use_helper('Validate');
        if (!Validate::numeric($id)) {
            Flash::set('error', __('Invalid input found!'));
            redirect(get_url());
        }
        
        // CSRF checks
        if (isset($_GET['csrf_token'])) {
            $csrf_token = $_GET['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'user/delete/'.$id)) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url('user'));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url('user'));
        }

        // security (dont delete the first admin)
        if ($id > 1) {
            // find the user to delete
            if ($user = Record::findByIdFrom('User', $id)) {
                if ($user->delete()) {
                    // delete user-roles relationship
                    UserRole::setRolesFor($user->id, array());
                    Flash::set('success', __('User <strong>:name</strong> has been deleted!', array(':name' => $user->name)));
                    Observer::notify('user_after_delete', $user->name, $user->id);
                }
                else {
                    Flash::set('error', __('User <strong>:name</strong> has not been deleted!', array(':name' => $user->name)));
                }
            }
            else {
                Flash::set('error', __('User not found!'));
            }
        }
        else {
            Flash::set('error', __('Action disabled!'));
        }

        redirect(get_url('user'));
    }

}
