<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The skeleton plugin serves as a basic plugin template.
 *
 * This skeleton plugin makes use/provides the following features:
 * - A controller without a tab
 * - Three views (sidebar, documentation and settings)
 * - A documentation page
 * - A sidebar
 * - A settings page (that does nothing except display some text)
 * - Code that gets run when the plugin is enabled (enable.php)
 *
 * Note: to use the settings and documentation pages, you will first need to enable
 * the plugin!
 *
 * @package Plugins
 * @subpackage skeleton
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

Plugin::setInfos(array(
    'id'          => 'skeleton',
    'title'       => __('Skeleton'),
    'description' => __('Provides a basic plugin implementation. (try enabling it!)'),
    'version'     => '1.1.0',
   	'license'     => 'GPL',
	'author'      => 'Martijn van der Kleijn',
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml',
    'require_wolf_version' => '0.5.5'
));

Plugin::addController('skeleton', __('Skeleton'), 'admin_view', false);


// Add a tab of your own
Observer::observe('view_page_edit_tab_links', 'addTab');

function addTab() {
    // Add example tab
    echo '<li class="tab"><a href="#mytab">My tab link</a></li>';
}

// Add the tab content that belongs to it
Observer::observe('view_page_edit_tabs', 'addTabContent');

function addTabContent() {
    // Add example tab content
    echo '<div id="mytab" class="page"><div id="div-metadata" title="My Tab Content"><p style="font-size: 24pt; text-align: center;">My Tab Content</p></div></div>';
}

// Add the custom box
Observer::observe('view_page_after_edit_tabs', 'addCustomBox');

function addCustomBox() {
    // Add custom box content
    echo '<div style="margin: 1em 0 1em 0; background-color: white;"><p style="font-size: 24pt; text-align: center;">Custom Box</p></div>';
}

// Add some setting stuff for example
Observer::observe('view_page_edit_plugins', 'addPluginSetting');

function addPluginSetting() {
    // Add custom settings for example
    echo '<p><label>Custom select box</label> <select><option>Option 1</option><option>Option 2</option></select></p><br/><br/>';
}

// Add some (by default) hidden stuff
Observer::observe('view_page_edit_popup', 'addHidden');

function addHidden() {
    // Add hidden stuff
    echo '<div style="padding: 1em; background-color: lightgray;"><p style="font-size: 24pt; text-align: center;">Hidden Dialog</p></div>';
}
