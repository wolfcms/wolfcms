<?php

/* Security measure */
//if (!defined('IN_CMS')) { exit(); }

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