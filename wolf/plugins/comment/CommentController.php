<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

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

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * class CommentController
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since Wolf version 0.6
 */
class CommentController extends PluginController {

    private static function _checkPermission() {
        AuthUser::load();
        if (!AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
    }

    function __construct() {
        self::_checkPermission();

        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/comment/views/sidebar'));
    }

    function index($page = 0) {
        $this->display('comment/views/index', array(
            'comments' => Comment::findAll(),
            'page' => $page
        ));
    }

    function documentation() {
        $this->display('comment/views/documentation');
    }

    function settings() {
        $tmp = Plugin::getAllSettings('comment');
        $settings = array('approve' => $tmp['auto_approve_comment'],
            'captcha' => $tmp['use_captcha'],
            'rowspage' => $tmp['rowspage'],
            'numlabel' => $tmp['numlabel']
        );
        $this->display('comment/views/settings', $settings);
    }

    function edit($id=null) {
        if (is_null($id))
            redirect(get_url('plugin/comment'));

        if (!$comment = Comment::findById($id)) {
            Flash::set('error', __('comment not found!'));
            redirect(get_url('plugin/comment'));
        }

        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_edit($id);

        // display things...
        $this->display('comment/views/edit', array(
            'action' => 'edit',
            'comment' => $comment
        ));
    }

    function _edit($id) {
        $comment = Comment::findById($id);
        $comment->setFromData($_POST['comment']);

        if (!$comment->save()) {
            Flash::set('error', __('Comment has not been saved!'));
            redirect(get_url('plugin/comment/edit/' . $id));
        } else {
            Flash::set('success', __('Comment has been saved!'));
            Observer::notify('comment_after_edit', $comment);
        }

        redirect(get_url('plugin/comment'));
    }

    function delete($id) {
        // find the user to delete
        if ($comment = Comment::findById($id)) {
            if ($comment->delete()) {
                Flash::set('success', __('Comment has been deleted!'));
                Observer::notify('comment_after_delete', $comment);
            }
            else
                Flash::set('error', __('Comment has not been deleted!'));
        }
        else
            Flash::set('error', __('Comment not found!'));

        redirect(get_url('plugin/comment'));
    }

    function approve($id) {
        // find the user to approve
        if ($comment = Comment::findById($id)) {
            $comment->is_approved = 1;
            if ($comment->save()) {
                Flash::set('success', __('Comment has been approved!'));
                Observer::notify('comment_after_approve', $comment);
            }
        }
        else
            Flash::set('error', __('Comment not found!'));

        redirect(get_url('plugin/comment/moderation'));
    }

    function unapprove($id) {
        // find the user to unapprove
        if ($comment = Comment::findById($id)) {
            $comment->is_approved = 0;
            if ($comment->save()) {
                Flash::set('success', __('Comment has been unapproved!'));
                Observer::notify('comment_after_unapprove', $comment);
            }
        }
        else
            Flash::set('error', __('Comment not found!'));

        redirect(get_url('plugin/comment'));
    }

    function save() {
        $approve = $_POST['autoapprove'];
        $captcha = $_POST['captcha'];
        $rowspage = $_POST['rowspage'];
        $numlabel = $_POST['numlabel'];

        $settings = array('auto_approve_comment' => $approve,
            'use_captcha' => $captcha,
            'rowspage' => $rowspage,
            'numlabel' => $numlabel
        );

        $ret = Plugin::setAllSettings($settings, 'comment');

        if ($ret)
            Flash::set('success', __('The settings have been updated.'));
        else
            Flash::set('error', 'An error has occured.');

        redirect(get_url('plugin/comment/settings'));
    }

    function moderation($page = 0) {
        $this->display('comment/views/moderation', array(
            'comments' => Comment::findAll(),
            'page' => $page
        ));
    }

}

// end CommentController class
