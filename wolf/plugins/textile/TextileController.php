<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

class TextileController extends PluginController {

    public function __construct() {
    }

    public function preview() {
        require_once('classTextile.php');
        $textile = new TextileFilter();
        $in = $_POST['data'];
        echo $textile->TextileThis($in);

        // For untrusted user input, use TextileRestricted instead:
        // echo $textile->TextileRestricted($in);
    }
}