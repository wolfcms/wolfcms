<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Archive plugin provides an Archive pagetype behaving similar to a blog or news archive.
 *
 * @package Plugins
 * @subpackage archive
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2011
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * 
 */
class ArchiveController extends PluginController {

    public function __construct() {
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/archive/views/sidebar'));
    }

    public function index() {
        $this->settings();
    }

    /*
    public function documentation() {
        $this->display('skeleton/views/documentation');
    }
     * 
     */

    function settings() {
        $this->display('archive/views/settings', array('settings' => Plugin::getAllSettings('archive')));
    }
    
    function save() {
        if (isset($_POST['settings'])) {
            if (Plugin::setAllSettings($_POST['settings'], 'archive')) {
                Flash::set('success', __('The settings have been saved.'));
            }
            else {
                Flash::set('error', __('An error occured trying to save the settings.'));
            }
        }
        else {
            Flash::set('error', __('Could not save settings, no settings found.'));
        }

        redirect(get_url('plugin/archive/settings'));
    }
}