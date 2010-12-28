<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Wolf_CMS
 */

// Start by outputting the image and then severing the browser connection.
// Any output in a cron run should NOT be done.

set_time_limit(86400);      // Stop the script after a day if need be

// Output web bug dummy image
ob_end_clean();
header("content-type: image/gif");
header("Connection: close");
ignore_user_abort(true);
ob_start();
echo base64_decode("R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="); //43byte 1x1 transparent pixel gif
$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush();
flush();            // Both need to be called!

// Setup system
define('IN_CMS', true);
define('DS', DIRECTORY_SEPARATOR);
define('CMS_ROOT', dirname(__FILE__).'/../..');
define('CORE_ROOT', CMS_ROOT.DS.'wolf');
define('PLUGINS_ROOT', CORE_ROOT.DS.'plugins');
define('APP_PATH',  CORE_ROOT.DS.'app');

require_once(CORE_ROOT.DS.'utils.php');
require_once(CMS_ROOT.DS.'config.php');

define('BASE_URL', URL_PUBLIC . (endsWith(URL_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));

require CORE_ROOT.DS.'Framework.php';

//  Database connection  -----------------------------------------------------

$__CMS_CONN__ = new PDO(DB_DSN, DB_USER, DB_PASS);
if ($__CMS_CONN__->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql')
    $__CMS_CONN__->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

Record::connection($__CMS_CONN__);
Record::getConnection()->exec("set names 'utf8'");

//  Initialize  --------------------------------------------------------------
use_helper('I18n');
Setting::init();
Plugin::init();

// Update cron run time
$cron = Cron::findByIdFrom('Cron', '1');
$cron->save();

// Run cron items
Observer::notify('cron_run');

?>
