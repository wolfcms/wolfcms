<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Views
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2013
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $message; ?></title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="generator" content="Wolf CMS" />
    </head>
    <body>
        <h1><?php echo $message . ' (' . $code . ')'; ?></h1>
        <?php
        if (isset($longMessage)) {
            echo $longMessage;
        }
        ?>
    </body>
</html>

<!--
   - Unfortunately, Microsoft added a clever 'feature' to Internet Explorer. 
   - If the text of an error's message is 'too small', specifically less than 512 bytes, 
   - Internet Explorer returns its own error message. You can turn that off, but it's 
   - tricky to find. This comment serves as padding to prevent that behaviour in IE.
   -
   - I really don't like Microsoft sometimes...
-->