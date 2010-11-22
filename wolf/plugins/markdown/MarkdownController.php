<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

class MarkdownController extends PluginController {

    public function __construct() { }

    public function preview() {
        require_once('classMarkdown.php');
        $markdown = new Markdown_Parser();
        $in = $_POST['data'];
        echo $markdown->transform($in);
    }
}