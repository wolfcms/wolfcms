<?php
/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS.
 *
 * Wolf CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Wolf CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wolf CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Wolf CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
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
define('CMS_ROOT', dirname(__FILE__).'/../..');
define('CORE_ROOT', CMS_ROOT.'/wolf');
define('PLUGINS_ROOT', CORE_ROOT.'/plugins');
define('APP_PATH',  CORE_ROOT.'/app');

require_once(CORE_ROOT.'/utils.php');
require_once(CMS_ROOT.'/config.php');

define('BASE_URL', URL_PUBLIC . (endsWith(URL_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));

require CORE_ROOT.'/Framework.php';

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
