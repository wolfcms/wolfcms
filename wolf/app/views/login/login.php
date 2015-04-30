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
 * @package Views
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 *
 * @copyright Martijn van der Kleijn, 2008-2010
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<!doctype html>
<html lang="en">
    <head>
        <title><?php echo __('Login').' - '.Setting::get('admin_title'); ?></title>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Font awesome CDN -->
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

        <link href="<?php echo PATH_PUBLIC; ?>wolf/admin/stylesheets/admin.css" media="screen" rel="stylesheet" type="text/css">
        <link href="<?php echo PATH_PUBLIC; ?>wolf/admin/themes/<?php echo Setting::get('theme'); ?>/screen.css" id="css_theme" media="screen" rel="Stylesheet" type="text/css" />

        <script type="text/javascript" charset="utf-8" src="<?php echo PATH_PUBLIC; ?>wolf/admin/javascripts/jquery-1.8.3.min.js"></script>
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

                $("input:visible:enabled:first").focus();
            });
            // ]]>
        </script>
    </head>
    <body>
    <div class="login">
        <h1 class="admin-title"><?php echo Setting::get('admin_title'); ?></h1>
            <div id="dialog">
                <h2><?php echo __('Login') ?> <span class="forgot-password-link"><a href="<?php echo get_url('login/forgot'); ?>"><?php echo __('Forgot password?'); ?></a></span></h2>
                    <?php if (Flash::get('error') !== null): ?>
                        <div id="error" class="message" style="display: none;"><?php echo Flash::get('error'); ?></div>
                    <?php endif; ?>
                    <?php if (Flash::get('success') !== null): ?>
                            <div id="success" class="message" style="display: none"><?php echo Flash::get('success'); ?></div>
                    <?php endif; ?>
                    <?php if (Flash::get('info') !== null): ?>
                                <div id="info" class="message" style="display: none"><?php echo Flash::get('info'); ?></div>
                    <?php endif; ?>
                        <form action="<?php echo get_url('login/login'); ?>" method="post">
                            <div id="login-username-div">
                                <label for="login-username"><?php echo __('Username'); ?>:</label>
                                <input id="login-username" type="text" name="login[username]" placeholder="<?php echo __('Username'); ?>" value="" />
                            </div>
                            <div id="login-password-div">
                                <label for="login-password"><?php echo __('Password'); ?>:</label>
                                <input id="login-password" type="password" name="login[password]" placeholder="<?php echo __('Password'); ?>" value="" />
                            </div>
                            <div class="remember-me">
                                <label for="login-remember-me">
                                    <input id="login-remember-me" type="checkbox" class="checkbox" name="login[remember]" value="checked" />
                                    <input id="login-redirect" type="hidden" name="login[redirect]" value="<?php echo $redirect; ?>" />
                                    <?php echo __('Remember me for :min minutes.', array(':min' => round(COOKIE_LIFE/60))); ?>
                                </label>
                            </div>
                            <div id="login-submit">
                                <button type="submit" accesskey="s"><i class="fa fa-lock"></i> <?php echo __('Login'); ?></button>
                            </div>
                        </form>
                </div>
            <p><?php echo __('website:').' <a href="'.URL_PUBLIC.'">'.Setting::get('admin_title').'</a>'; ?></p>
        </div>
        <script type="text/javascript" charset="utf-8">
            // <![CDATA[
            var loginUsername = document.getElementById('login-username');
            if (loginUsername.value == '') {
                loginUsername.focus();
            } else {
                document.getElementById('login-password').focus();
            }
            // ]]>
        </script>
    </body>
</html>
