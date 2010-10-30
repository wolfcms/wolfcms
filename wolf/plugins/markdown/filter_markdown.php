<?php

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

class Markdown {

    function apply($text) {
        require_once('classMarkdown.php');
        $markdown = new Markdown_Parser();
        return $markdown->transform($text);
    }
}