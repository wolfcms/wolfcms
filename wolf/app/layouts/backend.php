<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Layouts
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

// Redirect to front page if user doesn't have appropriate roles.
if (!AuthUser::hasPermission('admin_view')) {
    header('Location: ' . URL_PUBLIC . ' ');
    exit();
}

// Setup some stuff...
$ctrl = Dispatcher::getController(Setting::get('default_tab'));
$action = Dispatcher::getAction();

// Allow for nice title.
// @todo improve/clean this up.
$title = ($ctrl == 'plugin') ? Plugin::$controllers[Dispatcher::getAction()]->label : ucfirst($ctrl) . 's';
if (isset($this->vars['content_for_layout']->vars['action'])) {
    $tmp = $this->vars['content_for_layout']->vars['action'];
    $title .= ' - ' . ucfirst($tmp);

    if ($tmp == 'edit' && isset($this->vars['content_for_layout']->vars['page'])) {
        $tmp = $this->vars['content_for_layout']->vars['page'];
        $title .= ' - ' . $tmp->title;
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo Setting::get('admin_title'), ' - ', $title; ?></title>

        <!--[if lt IE 9]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        
        <link rel="shortcut icon" href="/admin/themes/<?php echo Setting::get('theme'); ?>/favicon.ico" type="image/vnd.microsoft.icon" />
        <link rel="stylesheet" href="<?php echo URI_PUBLIC; ?>wolf/admin/themes/<?php echo Setting::get('theme'); ?>/screen.css" media="screen" type="text/css" />
        <link rel="stylesheet" href="<?php echo URI_PUBLIC; ?>wolf/admin/markitup/skins/simple/style.css" type="text/css" />

        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/javascripts/jquery-1.6.2.min.js"></script> 
        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/javascripts/jquery-ui-1.8.5.custom.min.js"></script>
        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/javascripts/jquery.ui.nestedSortable.js"></script>
        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/javascripts/cp-datepicker.js"></script>
        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/markitup/jquery.markitup.js"></script>
        <script src="<?php echo BASE_URI; ?>wolfbase.js"></script>
        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/javascripts/wolf.js"></script>

    <script type="text/javascript" src="<?php echo URI_PUBLIC; ?>wolf/admin/markitup/jquery.markitup.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo URI_PUBLIC; ?>wolf/admin/markitup/skins/simple/style.css" />
    
<?php foreach(Plugin::$plugins as $plugin_id => $plugin): ?>
<?php if (file_exists(CORE_ROOT . '/plugins/' . $plugin_id . '/' . $plugin_id . '.js')): ?>
<script type="text/javascript" charset="utf-8" src="<?php echo URI_PUBLIC; ?>wolf/plugins/<?php echo $plugin_id.'/'.$plugin_id; ?>.js"></script>
<?php endif; ?>
<?php if (file_exists(CORE_ROOT . '/plugins/' . $plugin_id . '/' . $plugin_id . '.css')): ?>
<link href="<?php echo URI_PUBLIC; ?>wolf/plugins/<?php echo $plugin_id.'/'.$plugin_id; ?>.css" media="screen" rel="Stylesheet" type="text/css" />
<?php endif; ?>
<?php endforeach; ?>
<?php foreach(Plugin::$javascripts as $jscript_plugin_id => $javascript): ?>
<script type="text/javascript" charset="utf-8" src="<?php echo URI_PUBLIC; ?>wolf/plugins/<?php echo $javascript; ?>"></script>
<?php endforeach; ?>
    
    <script type="text/javascript">
    // <![CDATA[
        $(document).ready(function() {
            (function showMessages(e) {
                e.fadeIn('slow')
                 .animate({opacity: 1.0}, 1500)
                 .fadeOut('slow', function() {
                    if ($(this).next().attr('class') == 'message') {
                        showMessages($(this).next());
                    }
                    $(this).remove();
                 })
            })( $(".message:first") );
      // ]]>
</script>

    </head>
    <body>
        <header>
            <div id="logo"><a href="<?php echo URL_PUBLIC ?>"><?php echo Setting::get('admin_title') ?></a></div>
            <ul id="user-controls">
                <li><?php echo __('You are currently logged in as'); ?> <a href="<?php echo get_url('user/edit/' . AuthUser::getId()); ?>"><?php echo AuthUser::getRecord()->name; ?></a> | </li>
                <li><a href="<?php echo get_url('login/logout'); ?>"><?php echo __('Logout'); ?></a> | </li>
                <li><a id="site-view-link" href="<?php echo URL_PUBLIC; ?>" target="_blank"><?php echo __('View Site'); ?></a></li>
            </ul>
        </header>

        <nav>
            <ul>
                <li<?php if ($ctrl == 'page') echo ' class="current"'; ?>>
                    <a href="<?php echo get_url('page'); ?>">
                <?php echo __('Pages'); ?> <span class="counter"><?php //echo Record::countFrom('Page') ?></span>
                    </a>
                </li>
                <?php if (AuthUser::hasPermission('snippet_view')): ?>
                <li<?php if ($ctrl == 'snippet') echo ' class="current"'; ?>>
                    <a href="<?php echo get_url('snippet'); ?>"><?php echo __('Snippets'); ?> <span class="counter"><?php //echo Record::countFrom('Snippet') ?></span></a>
                </li>
                <?php endif; ?>
                <?php if (AuthUser::hasPermission('layout_view')): ?>
                <li<?php if ($ctrl == 'layout') echo ' class="current"'; ?>>
                    <a href="<?php echo get_url('layout'); ?>"><?php echo __('Layouts'); ?> <span class="counter"><?php //echo Record::countFrom('Layout') ?></span></a>
                </li>
                <?php endif; ?>
                <?php foreach (Plugin::$controllers as $plugin_name => $plugin): ?>
                    <?php if ($plugin->show_tab && (AuthUser::hasPermission($plugin->permissions))): ?>
                        <?php Observer::notify('view_backend_list_plugin', $plugin_name, $plugin); ?>
                    <li <?php if ($ctrl == 'plugin' && $action == $plugin_name) echo ' class="current"'; ?>>
                        <a href="<?php echo get_url('plugin/' . $plugin_name); ?>"><?php echo __($plugin->label); ?></a>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>           

                <ul class="right">
                    <?php if (AuthUser::hasPermission('admin_edit')): ?>
                    <li<?php if ($ctrl == 'setting' && $action != 'plugin') echo ' class="current"'; ?>>
                        <a href="<?php echo get_url('setting'); ?>"><?php echo __('Settings'); ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if (AuthUser::hasPermission('admin_edit')): ?>
                    <li<?php if ($ctrl == 'setting' && $action == 'plugin') echo ' class="current"'; ?>>
                        <a href="<?php echo get_url('setting/plugin'); ?>"><?php echo __('Plugins'); ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if (AuthUser::hasPermission('user_view')): ?>
                    <li<?php if ($ctrl == 'user') echo ' class="current"'; ?>>
                        <a href="<?php echo get_url('user'); ?>"><?php echo __('Users'); ?></a>
                    </li>
                    <?php endif; ?>
                </ul>
        </nav>
<?php if (Flash::get('error') !== null): ?>
<div id="error" class="message" style="display: none;"><?php echo Flash::get('error'); ?></div>
<?php endif; ?>
<?php if (Flash::get('success') !== null): ?>
<div id="success" class="message" style="display: none"><?php echo Flash::get('success'); ?></div>
<?php endif; ?>
<?php if (Flash::get('info') !== null): ?>
<div id="info" class="message" style="display: none"><?php echo Flash::get('info'); ?></div>
<?php endif; ?>
        <div id="content" <?php if (isset($sidebar) && trim($sidebar) != '') { echo ' class="use-sidebar sidebar-at-side2"'; } ?>>
            <?php if (isset($section_bar)) { ?>
            <div id="section-bar">
                <?php echo $section_bar; ?>
            </div> <!-- #section_bar -->
            <?php } ?>

            <?php if (isset($page_bar)) { ?>
            <div id="page-bar">
                <?php echo $page_bar; ?>
            </div> <!-- #page_bar -->
            <?php } ?>

            <section id="page-content">
                <?php echo $content_for_layout; ?>
            </section>

            <aside id="sidebar">
                <!-- sidebar -->
                <?php if (isset($sidebar) && trim($sidebar) != '') { echo $sidebar; } ?>
                <!-- end sidebar -->
            </aside>

            <div class="clearer">&nbsp;</div>

        </div>            

        <footer>
            <p>
                <?php echo __('Thank you for using'); ?> <a href="http://www.wolfcms.org/" target="_blank">Wolf CMS</a> <?php echo CMS_VERSION; ?> | <a href="http://forum.wolfcms.org/" target="_blank"><?php echo __('Feedback'); ?></a> | <a href="http://wiki.wolfcms.org/" target="_blank"><?php echo __('Documentation'); ?></a>
            </p>
            <?php if (DEBUG): ?>
            <p class="stats">
                <?php echo __('Page rendered in'); ?> <?php echo execution_time(); ?> <?php echo __('seconds'); ?>
                | <?php echo __('Memory usage:'); ?> <?php echo memory_usage(); ?>
            </p>
            <?php endif; ?>
        </footer>
    </body>
</html>
