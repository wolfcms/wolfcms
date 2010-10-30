<?php

/* Security measure */
//if (!defined('IN_CMS')) { exit(); }

class MarkdownController extends PluginController {

    public function __construct() { }

    public function preview() {
        require_once('classMarkdown.php');
        $markdown = new Markdown_Parser();
        $in = $_POST['data'];
        echo $markdown->transform($in);
    }
}