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
 * @package Wolf_CMS
 */

//  Constants  ---------------------------------------------------------------
define('IN_CMS', true);

define('CMS_ROOT', dirname(__FILE__));
require_once CMS_ROOT . DIRECTORY_SEPARATOR . 'init.php';

// run everything!
require APP_PATH.DS.'main.php';
