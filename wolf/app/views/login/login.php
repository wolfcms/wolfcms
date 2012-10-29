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
        <meta charset="utf-8" />
        <title><?php echo __('Login').' - '.Setting::get('admin_title'); ?></title>
        
        <!--[if lt IE 9]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        
        <link href="<?php echo URI_PUBLIC; ?>wolf/admin/themes/<?php echo Setting::get('theme'); ?>/login.css" media="screen" rel="stylesheet" type="text/css" />
        <script src="<?php echo URI_PUBLIC; ?>wolf/admin/javascripts/jquery-1.6.2.min.js"></script>
        <script>
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
        <section id="dialog">
            <h1><?php echo __('Login').' - '.Setting::get('admin_title'); ?></h1>
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
                            <input id="login-username" class="medium" type="text" name="login[username]" value="" />
                        </div>
                        <div id="login-password-div">
                            <label for="login-password"><?php echo __('Password'); ?>:</label>
                            <input id="login-password" class="medium" type="password" name="login[password]" value="" />
                        </div>
                        <div class="clean"></div>
                        <div style="margin-top: 6px">
                            <input id="login-remember-me" type="checkbox" class="checkbox" name="login[remember]" value="checked" />
                            <input id="login-redirect" type="hidden" name="login[redirect]" value="<?php echo $redirect; ?>" />
                            <label class="checkbox" for="login-remember-me"><?php echo __('Remember me for 30 minutes.'); ?></label>
                        </div>
                        <div id="login-submit">
                            <input class="submit" type="submit" accesskey="s" value="<?php echo __('Login'); ?>" />
                            <span>(<a href="<?php echo get_url('login/forgot'); ?>"><?php echo __('Forgot password?'); ?></a>)</span>
                        </div>
                    </form>
        </section>
                    <p><?php echo __('website:').' <a href="'.URL_PUBLIC.'">'.Setting::get('admin_title').'</a>'; ?></p>
        <script>
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