<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Wolf_CMS
 */

// Initialize the system.
define('IN_CMS', true);
define('CMS_ROOT', dirname(__FILE__));
define('CORE_ROOT', CMS_ROOT . DIRECTORY_SEPARATOR . 'wolf');
require_once CORE_ROOT . DIRECTORY_SEPARATOR . 'init.php';

// Run the system.
require APP_PATH.DS.'main.php';
