<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Markdown plugin provides a Filter that uses the Markdown parser.
 *
 * @package Plugins
 * @subpackage markdown
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The main controller for the Markdown plugin.
 */
class MarkdownController extends PluginController {

    public function __construct() { }

    public function preview() {
        require_once('classMarkdown.php');
        $markdown = new Markdown_Parser();
        echo $markdown->transform($_POST['data']);
    }
}