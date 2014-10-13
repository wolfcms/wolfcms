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
 * @package Layouts
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

// Redirect to front page if user doesn't have appropriate roles.
if (!AuthUser::hasPermission('admin_view')) {
    header('Location: '.URL_PUBLIC.' ');
    exit();
}

// Setup some stuff...
$ctrl = Dispatcher::getController(Setting::get('default_tab'));

// Allow for nice title.
// @todo improve/clean this up.
if (!isset($title) || trim($title) == '') {
    $title = ($ctrl == 'plugin') ? Plugin::$controllers[Dispatcher::getAction()]->label : ucfirst($ctrl).'s';
    if (isset($this->vars['content_for_layout']->vars['action'])) {
        $tmp = $this->vars['content_for_layout']->vars['action'];
        $title .= ' - '.ucfirst($tmp);

        if ($tmp == 'edit' && isset($this->vars['content_for_layout']->vars['page'])) {
            $tmp = $this->vars['content_for_layout']->vars['page'];
            $title .= ' - '.$tmp->title;
        }
    }
}
?>
<!doctype html>
<html lang="<?php echo AuthUser::getRecord()->language; ?>">
  <head>
    <!--<meta http-equiv="Content-type" content="text/html; charset=utf-8" />-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title><?php use_helper('Kses'); echo $title . ' | ' . kses(Setting::get('admin_title'), array()); ?></title>

    <link rel="favourites icon" href="<?php echo PATH_PUBLIC; ?>wolf/admin/images/favicon.ico" />
    <link href="<?php echo PATH_PUBLIC; ?>wolf/admin/stylesheets/admin.css" media="screen" rel="Stylesheet" type="text/css" />
    <link href="<?php echo PATH_PUBLIC; ?>wolf/admin/themes/<?php echo Setting::get('theme'); ?>/styles.css" id="css_theme" media="screen" rel="Stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/cp-datepicker.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/wolf.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/jquery-1.8.3.min.js"></script> 
    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/jquery-ui-1.10.3.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/jquery.ui.nestedSortable.js"></script>
    <!-- bootstrap -->
    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/bootstrap.js"></script>

    <?php Observer::notify('view_backend_layout_head', CURRENT_PATH); ?>
        
    <script type="text/javascript" src="<?php echo PATH_PUBLIC; ?>wolf/admin/markitup/jquery.markitup.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo PATH_PUBLIC; ?>wolf/admin/markitup/skins/simple/style.css" />
    
<?php foreach(Plugin::$plugins as $plugin_id => $plugin): ?>
<?php if (file_exists(CORE_ROOT . '/plugins/' . $plugin_id . '/' . $plugin_id . '.js')): ?>
    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/plugins/<?php echo $plugin_id.'/'.$plugin_id; ?>.js"></script>
<?php endif; ?>
<?php if (file_exists(CORE_ROOT . '/plugins/' . $plugin_id . '/' . $plugin_id . '.css')): ?>
    <link href="<?php echo PATH_PUBLIC; ?>wolf/plugins/<?php echo $plugin_id.'/'.$plugin_id; ?>.css" media="screen" rel="Stylesheet" type="text/css" />
<?php endif; ?>
<?php endforeach; ?>
<?php foreach(Plugin::$stylesheets as $plugin_id => $stylesheet): ?>
    <link type="text/css" href="<?php echo PATH_PUBLIC; ?>wolf/plugins/<?php echo $stylesheet; ?>" media="screen" rel="Stylesheet" />
<?php endforeach; ?>
<?php foreach(Plugin::$javascripts as $jscript_plugin_id => $javascript): ?>
    <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/plugins/<?php echo $javascript; ?>"></script>
<?php endforeach; ?>
    
    <script type="text/javascript">
    // <![CDATA[
        $(document).ready(function() {
            (function showMessages(e) {
                e.fadeIn('slow')
                 .animate({opacity: 1.0}, Math.min(5000, parseInt(e.text().length * 50)))
                 .fadeOut('slow', function() {
                    if ($(this).next().attr('class') == 'message') {
                        showMessages($(this).next());
                    }
                    $(this).remove();
                 })
            })( $(".message:first") );

            $("input:visible:enabled:first").focus();
            
            // Get the initial values and activate filter
            $('.filter-selector').each(function() {
                var $this = $(this);
                $this.data('oldValue', $this.val());

                if ($this.val() == '') {
                    return true;
                }
                var elemId = $this.attr('id').slice(0, -10);
                var elem = $('#'+elemId+'_content');
                $this.trigger('wolfSwitchFilterIn', [$this.val(), elem]);
            });
            
            $('.filter-selector').live('change',function(){
                var $this = $(this);
                var newFilter = $this.val();
                var oldFilter = $this.data('oldValue');
                $this.data('oldValue', newFilter);
                var elemId = $this.attr('id').slice(0, -10);
                var elem = $('#'+elemId+'_content');
                $(this).trigger('wolfSwitchFilterOut', [oldFilter, elem]);
                $(this).trigger('wolfSwitchFilterIn', [newFilter, elem]);
            });
        });
        // ]]>
        </script>

<?php $action = Dispatcher::getAction(); ?>
  </head>
  <body id="body_<?php echo $ctrl.'_'.Dispatcher::getAction(); ?>">
    <!-- Div to allow for modal dialogs -->
    <!-- <div id="mask"></div>  -->

    <header>
    <!--
      <div id="site-title"><a href="<?php echo get_url(); ?>"><?php echo Setting::get('admin_title'); ?></a></div>
      <div id="mainTabs">
        <ul>
          <li id="page-plugin" class="plugin"><a href="<?php echo get_url('page'); ?>"<?php if ($ctrl=='page') echo ' class="current"'; ?>><?php echo __('Pages'); ?></a></li>
<?php if (AuthUser::hasPermission('snippet_view')): ?>
          <li id="snippet-plugin" class="plugin"><a href="<?php echo get_url('snippet'); ?>"<?php if ($ctrl=='snippet') echo ' class="current"'; ?>><?php echo __('MSG_SNIPPETS'); ?></a></li>
<?php endif; ?>
<?php if (AuthUser::hasPermission('layout_view')): ?>
          <li id="layout-plugin" class="plugin"><a href="<?php echo get_url('layout'); ?>"<?php if ($ctrl=='layout') echo ' class="current"'; ?>><?php echo __('Layouts'); ?></a></li>
<?php endif; ?>

<?php foreach (Plugin::$controllers as $plugin_name => $plugin): ?>
<?php if ($plugin->show_tab && (AuthUser::hasPermission($plugin->permissions))): ?>
          <?php Observer::notify('view_backend_list_plugin', $plugin_name, $plugin); ?>
          <li id="<?php echo $plugin_name;?>-plugin" class="plugin"><a href="<?php echo get_url('plugin/'.$plugin_name); ?>"<?php if ($ctrl=='plugin' && $action==$plugin_name) echo ' class="current"'; ?>><?php echo $plugin->label; ?></a></li>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (AuthUser::hasPermission('admin_edit')): ?>
          <li class="right"><a href="<?php echo get_url('setting'); ?>"<?php if ($ctrl=='setting') echo ' class="current"'; ?>><?php echo __('Administration'); ?></a></li>
<?php endif; ?>
<?php if (AuthUser::hasPermission('user_view')): ?>
          <li class="right"><a href="<?php echo get_url('user'); ?>"<?php if ($ctrl=='user') echo ' class="current"'; ?>><?php echo __('Users'); ?></a></li>
<?php endif; ?>
        </ul>
      </div>
      -->


      <!-- NAVIGATION -->
      <nav id="navigation" class="navbar navbar-inverse">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="<?php echo get_url(); ?>" class="navbar-brand"><?php echo Setting::get('admin_title'); ?></a>
        </div>
        <div class="collapse navbar-collapse">
            <section class="nav navbar-nav">
                <li id="page-plugin" class="<?php echo ( $ctrl == 'page' ) ? 'plugin active' : 'plugin'; ?>">
                    <a href="<?php echo get_url('page'); ?>">
                        <?php echo __('Pages'); ?>
                    </a>
                </li>
                <?php if ( AuthUser::hasPermission('snippet_view') ): ?>

                    <li id="snippet-plugin" class="<?php echo ( $ctrl == 'snippet' ) ? 'plugin active' : 'plugin'; ?>">
                        <a href="<?php echo get_url('snippet'); ?>">
                            <?php echo __('MSG_SNIPPETS'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ( AuthUser::hasPermission('layout_view') ): ?>

                    <li id="layout-plugin" class="<?php echo ( $ctrl == 'layout' ) ? 'plugin active' : 'plugin'; ?>">
                        <a href="<?php echo get_url('layout'); ?>">
                            <?php echo __('Layouts'); ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php foreach ( Plugin::$controllers as $plugin_name => $plugin ): ?>
                    <?php if ( $plugin->show_tab && (AuthUser::hasPermission($plugin->permissions)) ): ?>
                        <?php Observer::notify('view_backend_list_plugin', $plugin_name, $plugin); ?>
                        <?php if ( !empty($plugin->label) ): ?>

                            <li id="<?php echo $plugin_name; ?>-plugin" class="<?php echo ( $ctrl == 'plugin' && $action == $plugin_name ) ? 'plugin active' : 'plugin'; ?>">
                                <a href="<?php echo get_url('plugin/' . $plugin_name); ?>">
                                    <?php echo $plugin->label; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>
            <section class="nav navbar-nav navbar-right">
                <li class="dropdown<?php echo ( $ctrl == 'setting' || $ctrl == 'user' ) ? ' active' : ''; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="navbar-settings">
                            <?php echo __('Settings'); ?> <b class="caret"></b>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if ( AuthUser::hasPermission('admin_edit') ): ?>
                            <li class="<?php echo ( $ctrl == 'setting' && $action != 'plugin' ) ? ' active' : ''; ?>">
                                <a href="<?php echo get_url('setting'); ?>"><i class="fa fa-cog"></i> <?php echo __('Administration'); ?></a>
                            </li>
                        <?php endif; ?>
                        <!-- plugins -->
                        <?php if ( AuthUser::hasPermission('admin_view') ): ?>
                            <li class="<?php echo ( $ctrl == 'setting' && $action == 'plugin' ) ? ' active' : ''; ?>">
                                <a href="<?php echo get_url('setting/plugin'); ?>"><i class="fa fa-plug"></i> <?php echo __('Plugins'); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ( AuthUser::hasPermission('user_view') ): ?>
                            <li class="<?php echo ( $ctrl == 'user' ) ? ' active' : ''; ?>">
                                <a href="<?php echo get_url('user'); ?>"><i class="fa fa-users"></i> <?php echo __('Users'); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="navbar-username">
                            <?php echo AuthUser::getRecord()->name; ?>
                        </span>
                            <?php
                            use_helper('Gravatar');
                            echo Gravatar::img(AuthUser::getRecord()->email, array( 'align' => 'middle', 'alt' => 'user icon', 'class' => 'navbar-user-gravatar' ), '32', URL_PUBLIC . 'wolf/admin/images/user.png', 'g', USE_HTTPS);
                            ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?php echo get_url('user/edit/' . AuthUser::getId()); ?>">
                                <i class="fa fa-user"></i>
                                <?php echo __('Edit user'); ?>
                            </a>
                        <li>
                            <a id="site-view-link" href="<?php echo URL_PUBLIC; ?>" target="_blank">
                                <i class="fa fa-external-link-square"></i>
                                <?php echo __('View Site'); ?> 
                            </a>
                        </li>
                        <li role="presentation" class="divider"></li>
                        <li>
                            <a href="<?php echo get_url('login/logout' . '?csrf_token=' . SecureToken::generateToken(BASE_URL . 'login/logout')); ?>">
                                <i class="fa fa-power-off"></i>
                                <?php echo __('Log Out'); ?>
                            </a>
                        </li>
                    </ul>
                </li>                        
            </section>
        </div>
      </nav>
      <!-- END NAVIGATION -->
    </header> <!-- #header -->
<?php if (Flash::get('error') !== null): ?>
        <div id="error" class="alert alert-danger alert-dissmisable flash-message">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <?php echo Flash::get('error'); ?>
        </div>
<?php endif; ?>
<?php if (Flash::get('success') !== null): ?>
        <div id="success" class="alert alert-success alert-dissmisable flash-message">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <?php echo Flash::get('success'); ?>
        </div>
<?php endif; ?>
<?php if (Flash::get('info') !== null): ?>
        <div id="info" class="alert alert-info alert-dissmisable flash-message">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <?php echo Flash::get('info'); ?>
        </div>
<?php endif; ?>
    <div id="main">
        <div id="content-wrapper" <?php if (isset($sidebar) && trim($sidebar) != '') { echo ' class="use-sidebar sidebar-at-side2"'; } ?>>
            <div id="content">
        <!-- content -->
        <?php echo $content_for_layout; ?>
        <!-- end content -->
            </div>
        </div>
        <?php if (isset($sidebar)) { ?>
        <div id="sidebar-wrapper">
            <div id="sidebar">
            <!-- sidebar -->
            <?php echo $sidebar; ?>
            <!-- end sidebar -->
            </div>
        </div>
        <?php } ?>
    </div>

    <footer id="footer">
        <!-- WOLFCMS info -->
        <div class="version">
        <p><?php echo __('Thank you for using'); ?> <a href="http://www.wolfcms.org/" target="_blank">Wolf CMS</a> <?php echo CMS_VERSION; ?> | <a href="http://forum.wolfcms.org/" target="_blank"><?php echo __('Feedback'); ?></a> | <a href="http://docs.wolfcms.org/" target="_blank"><?php echo __('Documentation'); ?></a></p>
        <?php if (DEBUG): ?>
            <!-- DEBUG info -->
            <div class="debug">
                <p class="stats">
                    <?php echo __('Page rendered in'); ?> <?php echo execution_time(); ?> <?php echo __('seconds'); ?>
                    | <?php echo __('Memory usage:'); ?> <?php echo memory_usage(); ?>
                </p>
            </div>
        <?php endif; ?>
        </div>
        <div id="site-links">
            <p>
                <?php echo __('You are currently logged in as'); ?> <a href="<?php echo get_url('user/edit/'.AuthUser::getId()); ?>"><?php echo AuthUser::getRecord()->name; ?></a>
                <span class="separator"> | </span>
                <a href="<?php echo get_url('login/logout'.'?csrf_token='.SecureToken::generateToken(BASE_URL.'login/logout')); ?>"><?php echo __('Log Out'); ?></a>
                <span class="separator"> | </span>
                <a id="site-view-link" href="<?php echo URL_PUBLIC; ?>" target="_blank"><?php echo __('View Site'); ?></a>
            </p>
        </div>
    </footer>
  </body>
</html>
