<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Textile plugin provides a Filter that uses the Textile parser.
 *
 * @package Plugins
 * @subpackage textile
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * A Wolf CMS specific wrapper around the original parser.
 */
class Textile {

    function apply($text) {
        require_once('classTextile.php');
        $textile = new TextileFilter();
        return $textile->TextileThis($text);
    }
}