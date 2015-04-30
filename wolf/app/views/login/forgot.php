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
 * @package Views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo __('Forgot password'); ?></title>

        <base href="<?php echo trim(BASE_URL, '?/').'/'; ?>" />

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
                    <h2><?php echo __('Forgot password'); ?><span class="login-link"><a href="<?php echo get_url('login'); ?>"><?php echo __('Login'); ?></a></span></h2>
                    <?php if (Flash::get('error') !== null): ?>
                        <div id="error" class="message" style="display: none;"><?php echo Flash::get('error'); ?></div>
                    <?php endif; ?>
                    <?php if (Flash::get('success') !== null): ?>
                        <div id="success" class="message" style="display: none"><?php echo Flash::get('success'); ?></div>
                    <?php endif; ?>
                    <?php if (Flash::get('info') !== null): ?>
                        <div id="info" class="message" style="display: none"><?php echo Flash::get('info'); ?></div>
                    <?php endif; ?>
                    <form action="<?php echo get_url('login', 'forgot'); ?>" method="post">
                        <div>
                            <label for="forgot-email"><?php echo __('Email address'); ?>:</label>
                            <input class="long" id="forgot-email" type="text" name="forgot[email]" placeholder="<?php echo __('Email address'); ?>" value="<?php echo $email; ?>" />
                        </div>
                        <div id="forgot-submit">
                            <button type="submit" accesskey="s"><i class="fa fa-envelope"></i> <?php echo __('Send password'); ?></button>
                        </div>
                    </form>
                </div>
                <p><?php echo __('website:').' <a href="'.URL_PUBLIC.'">'.Setting::get('admin_title').'</a>'; ?></p>
        </div>
    </body>
</html>
