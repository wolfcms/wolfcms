<?php

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

class Textile {

    function apply($text) {
        require_once('classTextile.php');
        $textile = new TextileFilter();
        return $textile->TextileThis($text);
    }
}