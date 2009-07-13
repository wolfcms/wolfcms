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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package frog
 * @subpackage plugin.comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.2.0
 * @since Frog version 0.9.3
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 */

/**
 * class CommentController
 *
 * @package frog
 * @subpackage plugin.comment
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since Frog version 0.6
 */
class CommentController extends PluginController
{
    function __construct()
    {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn())
            redirect(get_url('login'));
        
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/comment/views/sidebar'));
    }
    
    function index($page = 0)
    {
        $this->display('comment/views/index', array(
            'comments' => Comment::findAll(),
            'page' => $page
        ));
    }
    
    function edit($id=null)
    {
        if (is_null($id))
            redirect(get_url('plugin/comment'));
        
        if ( ! $comment = Comment::findById($id))
        {
            Flash::set('error', __('comment not found!'));
            redirect(get_url('plugin/comment'));
        }
        
        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_edit($id);
        
        // display things...
        $this->display('comment/views/edit', array(
            'action'  => 'edit',
            'comment' => $comment
        ));
    }
    
    function _edit($id)
    {
        $comment = Record::findByIdFrom('comment', $id);
        $comment->setFromData($_POST['comment']);
        
        if ( ! $comment->save())
        {
            Flash::set('error', __('Comment has not been saved!'));
            redirect(get_url('plugin/comment/edit/'.$id));
        }
        else
        {
            Flash::set('success', __('Comment has been saved!'));
            Observer::notify('comment_after_edit', $comment);
        }
        
        redirect(get_url('plugin/comment'));
    }
    
    function delete($id)
    {
        // find the user to delete
        if ($comment = Record::findByIdFrom('Comment', $id))
        {
            if ($comment->delete())
            {
                Flash::set('success', __('Comment has been deleted!'));
                Observer::notify('comment_after_delete', $comment);
            }
            else
                Flash::set('error', __('Comment has not been deleted!'));
        }
        else Flash::set('error', __('Comment not found!'));
        
        redirect(get_url('plugin/comment'));
    }
    
    function approve($id)
    {
        // find the user to approve
        if ($comment = Record::findByIdFrom('Comment', $id))
        {
            $comment->is_approved = 1;
            if ($comment->save())
            {
                Flash::set('success', __('Comment has been approved!'));
                Observer::notify('comment_after_approve', $comment);
            }
        }
        else Flash::set('error', __('Comment not found!'));
        
        redirect(get_url('plugin/comment/moderation'));
    }
    
    function unapprove($id)
    {
        // find the user to unapprove
        if ($comment = Record::findByIdFrom('Comment', $id))
        {
            $comment->is_approved = 0;
            if ($comment->save())
            {
                Flash::set('success', __('Comment has been unapproved!'));
                Observer::notify('comment_after_unapprove', $comment);
            }
        }
        else Flash::set('error', __('Comment not found!'));
        
        redirect(get_url('plugin/comment'));
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
    
	function save() {
		$approve = mysql_escape_string($_POST['autoapprove']);
        $captcha = mysql_escape_string($_POST['captcha']);
        $rowspage = mysql_escape_string($_POST['rowspage']);
        $numlabel = mysql_escape_string($_POST['numlabel']);

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
	
	function documentation() {
    	$this->display('comment/views/documentation'); 
    }
    function moderation($page = 0) {
    	 $this->display('comment/views/moderation', array(
            'comments' => Comment::findAll(),
            'page' => $page
        ));
    }
} // end CommentController class
