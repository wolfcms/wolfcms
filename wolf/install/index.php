<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Installer
 */

// Make sure we hide ugly errrors
error_reporting(0);

define('INSTALL_SEQUENCE', true);

define('CORE_ROOT', dirname(__FILE__).'/../../wolf');
define('CFG_FILE', '../../config.php');
define('PUBLIC_ROOT', '../../public/');
define('DEFAULT_ADMIN_USER', 'admin');
require_once CORE_ROOT.'/Framework.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Wolf CMS - Install/Upgrade routine</title>
    <link href="install.css" media="screen" rel="Stylesheet" type="text/css" />
</head>
<body>
    <div id="header">
        <div id="site-title">Wolf CMS</div>
    </div>
    <div id="content">

<?php
if (isset($_POST['install']) && !isset($_POST['commit']) && file_exists(CFG_FILE) && !(filesize(CFG_FILE) > 1)) {
    require_once 'install.php';
}
else if (isset($_POST['install']) && isset($_POST['commit']) && isset($_POST['config'])) {
    $config = $_POST['config'];
    require_once 'do-install.php';
    require_once 'post-install.php';
}
else if (isset($_POST['upgrade']) && isset($_POST['commit']) && file_exists(CFG_FILE) && (filesize(CFG_FILE) > 1)) {
    require_once CFG_FILE;
    require_once CORE_ROOT.'/Framework.php';
    require_once 'do-upgrade.php';
}
else if (!isset($_POST['upgrade']) && !isset($_POST['commit']) && file_exists(CFG_FILE) && (filesize(CFG_FILE) > 1)) {
    require_once 'upgrade.php';
}
else {
    require_once 'requirements.php';
}
?>

    </div>
    <div id="footer">
        <p>Powered by <a href="http://www.wolfcms.org/">Wolf CMS</a></p>
    </div>
</body>
</html>