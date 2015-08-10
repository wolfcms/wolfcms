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
 * @copyright Martijn van der Kleijn, 2008, 2009, 2010
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 * Allows a user to access login/logout related functionality.
 *
 * It also has functionality to email a new password to the user if that user
 * cannot remember his or her password.
 */
class LoginController extends Controller {
/**
 * Sets up the LoginController.
 */
    function __construct() {
        // Redirect to HTTPS for login purposes if requested
        if (defined('USE_HTTPS') && USE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
            $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            header("Location: $url");
            exit;
        }

        AuthUser::load();
    }

    /**
     * Checks if a user is already logged in, otherwise it redirects the user
     * to the login screen.
     */
    function index() {
        $redirect = '';

        if (Flash::get('redirect') != null)
            $redirect = Flash::get('redirect');
        elseif (Flash::get('HTTP_REFERER') != null)
            $redirect = trim(Flash::get('HTTP_REFERER'));

        // already log in ?
        if (AuthUser::isLoggedIn()) {
            if ($redirect != '')
                redirect($redirect);
            else
                redirect(get_url());
        }

        // Allow plugins to handle login
        Observer::notify('login_required', $redirect);

        // show it!
        $this->display('login/login', array(
            'username' => Flash::get('username'),
            'redirect' => $redirect
        ));
    }

    /**
     * Allows a user to login.
     */
    function login() {
        $redirect = '';

        if (Flash::get('redirect') != null)
            $redirect = Flash::get('redirect');
        elseif (Flash::get('HTTP_REFERER') != null)
            $redirect = trim(Flash::get('HTTP_REFERER'));

        // Allow plugins to handle login
        Observer::notify('login_requested', $redirect);

        // already log in ?
        if (AuthUser::isLoggedIn())
            if ($redirect != '')
                redirect(self::sanitizeRedirect($redirect));
            else
                redirect(get_url());

        if (get_request_method() == 'POST') {
            $data = isset($_POST['login']) ? $_POST['login']: array('username' => '', 'password' => '');
            Flash::set('username', $data['username']);

            if (AuthUser::login($data['username'], $data['password'], isset($data['remember']))) {
                Observer::notify('admin_login_success', $data['username']);

                $this->_checkVersion();
                // redirect to defaut controller and action
                if ($data['redirect'] != null && $data['redirect'] != 'null')
                    redirect(self::sanitizeRedirect($data['redirect']));
                else
                    redirect(get_url());
            }
            else {
                Flash::set('error', __('Login failed. Check your username and password.<br/>If you tried to login more than :attempts times, you will have to wait at least :delay seconds before trying again.', array(':attempts' => DELAY_FIRST_AFTER, ':delay' => DELAY_ONCE_EVERY)));
                Observer::notify('admin_login_failed', $data['username']);
            }
        }

        // not find or password is wrong
        if ($data['redirect'] != null && $data['redirect'] != 'null')
            redirect(self::sanitizeRedirect($data['redirect']));
        else
            redirect(get_url('login'));

    }

    static function sanitizeRedirect($redirect) {
        if ($redirect != null) {
            $redirect = preg_replace('/.*:\/\/[^\/]+\//', '/', $redirect);

            return $redirect;
        }

        return '';
    }

    /**
     * Allows a user to logout.
     */
    function logout() {
        // CSRF checks
        if (isset($_GET['csrf_token'])) {
            $csrf_token = $_GET['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'login/logout')) {
                Flash::set('error', __('Invalid CSRF token found!'));
                redirect(get_url());
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            redirect(get_url());
        }

        // Allow plugins to handle logout events
        Observer::notify('logout_requested');

        $username = AuthUser::getUserName();
        AuthUser::logout();

        // Also eat cookies that were set by JS for backend gui
        setcookie("expanded_rows", "", time()-3600);
        setcookie("meta_tab", "", time()-3600);
        setcookie("page_tab", "", time()-3600);

        Observer::notify('admin_after_logout', $username);
        redirect(get_url());
    }

    /**
     * Allows a user to request a new password be mailed to him/her.
     *
     * @return <type> ???
     */
    function forgot() {
        if (get_request_method() == 'POST')
            return $this->_sendPasswordTo($_POST['forgot']['email']);

        $this->display('login/forgot', array('email' => Flash::get('email')));
    }

    /**
     * This method is used to send a newly generated password to a user.
     *
     * @param string $email The user's email adress.
     */
    private function _sendPasswordTo($email) {
        $user = User::findBy('email', $email);
        if ($user) {
            use_helper('Email');

            $new_pass = '12'.dechex(rand(100000000, 4294967295)).'K';
            $user->password = AuthUser::generateHashedPassword($new_pass.$user->salt);
            $user->save();

            $email = new Email();
            $email->from(Setting::get('admin_email'), Setting::get('admin_title'));
            $email->to($user->email);
            $email->subject(__('Your new password from ').Setting::get('admin_title'));
            $email->message(__('Username').': '.$user->username."\n".__('Password').': '.$new_pass);
            $email->send();

            Flash::set('success', __('An email has been sent with your new password!'));
            redirect(get_url('login'));
        }
        else {
            Flash::set('email', $email);
            Flash::set('error', __('No user found!'));
            redirect(get_url('login/forgot'));
        }
    }

    /**
     * Checks what the latest Wolf version is that is available at wolfcms.org
     *
     * @todo Make this check optional through the configuration file
     */
    private function _checkVersion() {
        if (!defined('CHECK_UPDATES') || !CHECK_UPDATES)
            return;

        $v = getContentFromUrl('http://www.wolfcms.org/version/');

        if (false !== $v && $v > CMS_VERSION) {
            Flash::set('error', __('<b>Information!</b> New Wolf version available (v. <b>:version</b>)! Visit <a href="http://www.wolfcms.org/">http://www.wolfcms.org/</a> to upgrade your version!',
                array(':version' => $v )));
        }
    }

} // end LoginController class
