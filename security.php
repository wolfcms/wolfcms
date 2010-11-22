<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * Please see license.txt for the full license text.
 */

/**
 * @package wolf
 */

// Make sure we hide ugly errrors
error_reporting(0);

define('SECURITY_CHECK', true);

define('CORE_ROOT', dirname(__FILE__).'/wolf');
define('CFG_FILE', 'config.php');

require(CORE_ROOT.'/utils.php');
require(CFG_FILE);

if (!defined('DEBUG')) { echo 'Please install Wolf CMS first, thank you.'; exit(); }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Wolf CMS - Installation routine</title>
    <style>
        /* Reset ------------------------------------------------------------------ */

* {margin: 0px; padding: 0px;}


/* General ---------------------------------------------------------------- */

body {
  font-family: "Lucida Grande", "Bitstream Vera Sans", Helvetica, Verdana, Arial, sans-serif;
  background-color: #e5e5e5;
  color: #000;
}

.check, .notcheck {
    color: green;
    font-weight: bold;
}

.notcheck {
    color: red;
}

p { margin: 1.2em 0 0.6em; }

h1 { text-shadow: 1px 2px 3px #bbb; }

a { color: #147; }
a:hover { text-decoration: none; }

img { border: 0; }


/* Header ----------------------------------------------------------------- */

#header {
  background-color: #483E37;
  color: #fff;
  padding: 1.5em;
}

#header #site-title {
  font-size: 150%;
  font-weight: bold;
}


/* Content ---------------------------------------------------------------- */

#content {
  margin: 1em 1em 0em 1em;
  padding: 1.5em;
  background-color: #fff;
  border-bottom: 2px solid #76A83A;

  /* Border-radius not implemented yet */
  border-radius: 6px;
  -moz-border-radius: 6px;
  -webkit-border-radius: 6px;

  box-shadow: 3px 3px 4px #bbb;
  -moz-box-shadow: 3px 3px 4px #bbb;
  -webkit-box-shadow: 3px 3px 4px #bbb;
}

#content .logo { float: right; margin: 0em 0em 0.5em 0.5em;}

#content table {
    border-collapse: collapse;
    margin: 1em auto;
}

#content table thead tr th { font-weight: bold; }

#content table thead tr th,
#content table tbody tr td {
    padding: 0.3em;
}

#content table thead tr th#requirement {
    width: 20em;
}

#content table tbody tr td.available {
    text-align: center;
}

.footnotes {
    margin: 0.2em 0 0.6em;
    font-size: 90%;
}

/* Footer ----------------------------------------------------------------- */

#footer {
    font-size: 80%;
    padding: 0em 2.5em;
}


/* Form Elements ---------------------------------------------------------- */

.buttons {
    text-align: right;
}

.button {
    font-size: 150%;
}

table.fieldset {
  width: 100%;
}

table.fieldset td.label {
  text-align: right;
  width: 15em;
}

table.fieldset td.label .optional {
  color: #929488;
}

table.fieldset td.help {
  background-color: #eee;
  font-size: 80%;
  padding: 1em;
}


/* Lists -------------------------------------------------------------------*/

ol, ul {
    list-style-position: inside;
    margin: 1em;
}
    </style>
</head>
<body>
    <div id="header">
        <div id="site-title">Wolf CMS - security advisory</div>
    </div>
    <div id="content">

<?php
    /* START CHECKS */
    $advisories = array();
    $warnings = array();
    $fatals   = array();

    /* RUN CHECKS - advisories */
    if (defined('DEBUG') && false === DEBUG && file_exists(CORE_ROOT.'/../readme.txt')) {
        $advisories['readme.txt, file present'] = 'The readme.txt is still present. You may want to remove it for added security since this is probably a production system. (DEBUG was set to FALSE)';
    }

    /* RUN CHECKS - warnings */
    if (true === DEBUG) {
        $warnings['debug on'] = 'Due to the type and amount of information an error might give intruders when debug is turned on, we strongly advise setting debug to FALSE in production systems.';
    }

    if (isWritable(CFG_FILE) && true === DEBUG) {
        $warnings['config file writable, debug on'] = 'The configuration file should never be writable in production systems. We advise you to remove write permissions on config.php';
    }
    
    if (defined('DEBUG') && false === DEBUG && file_exists(CORE_ROOT.'/install/')) {
        $warnings['install, directory present'] = 'The installation directory ("wolf/install/") is still present. You may want to remove it for added security since this is probably a production system. (DEBUG was set to FALSE)';
    }

    if (defined('DEBUG') && false === DEBUG && file_exists(CORE_ROOT.'/../docs/')) {
        $warnings['docs, directory present'] = 'The documenation directory ("docs/") is still present. You may want to remove it for added security since this is probably a production system. (DEBUG was set to FALSE)';
    }

    if (defined('DB_DSN') && startsWith(DB_DSN, 'sqlite:'.realpath(dirname(__FILE__)))) {
        $warnings['db, sqlite location'] = 'It would appear that the SQLite database file is stored inside of web accessible directory. We strongly recommend that you move the database files.';
    }

    /* RUN CHECKS - fatals */
    $perms = fileperms($config_file);
    if ((($perms & 0x0010) || ($perms & 0x0002)) && true !== DEBUG) {
        $fatals['config file writable, debug off'] = 'Wolf CMS has automatically made itself unavailable because the configuration file was found to be writable. Until this problem is corrected, only this screen will be available.';
    }

    $fileinfo = posix_getpwuid(fileowner($config_file));
    $processinfo = posix_getpwuid(posix_getuid());
    if (substr(PHP_OS, 0, 3) != 'WIN' && ($fileinfo['name'] == $processinfo['name'] || $fileinfo['gid'] == $processinfo['gid'])) {
        $fatals['config file owned, debug off'] = 'The config file is owned by the same user/group under whom the HTTP server is running. This is never a good idea.';
    }

    if (defined('DEBUG') && false === DEBUG && file_exists(CORE_ROOT.'/../security.php')) {
        $fatals['security.php, file present'] = 'The security.php file is still present. Please remove it to prevent abuse since this is a production system. (DEBUG was set to FALSE)';
    }

    /* END CHECKS - DUMP OUTPUT */
?>

    <h1>Overview</h1>
    <p>Once your Wolf CMS installation is running in production status, you are strongly advised to remove this file ("/security.php") to prevent abuse.</p>
    <?php if (true === DEBUG) { echo '<p><strong>NOTE:</strong> this check is assuming this installation of Wolf CMS is NOT a production system since DEBUG is set to TRUE. Please <strong>make sure to run this check again</strong> with DEBUG set to FALSE.</p>'; } ?>
    <?php if (count($fatals) > 0) { echo '<p>One or more FATAL level security problems have been detected. You are strongly advised to correct them!</p>'; } ?>
    <table style="margin: 20px auto; width: 90%;">
        <tbody>
<?php
    foreach ($fatals as $short => $long) {
?>
            <tr style="background-color: #483e37; color: #fff;">
                <td><span style="font-weight: bold; color: #e22;">FATAL</span> - [<?php echo $short; ?>]</td>
            </tr>
            <tr>
                <td><?php echo $long; ?></td>
            </tr>
<?php
    }
?>
<?php
    foreach ($warnings as $short => $long) {
?>
            <tr style="background-color: #483e37; color: #fff;">
                <td><span style="font-weight: bold; color: #ef8;">WARNING</span> - [<?php echo $short; ?>]</td>
            </tr>
            <tr>
                <td><?php echo $long; ?></td>
            </tr>
<?php
    }
?>
<?php
    foreach ($advisories as $short => $long) {
?>
            <tr style="background-color: #483e37; color: #fff;">
                <td><span style="font-weight: bold; color: #76a83a;">ADVISE</span> - [<?php echo $short; ?>]</td>
            </tr>
            <tr>
                <td><?php echo $long; ?></td>
            </tr>
<?php
    }
?>
        </tbody>
    </table>
    <hr/>
    <p><small>DISCLAIMER - neither the Wolf CMS project nor any of its contributors provide any warranty, for details, please see /docs/license.txt in the download package.</small></p>

    </div>
    <div id="footer">
        <p>Powered by <a href="http://www.wolfcms.org/">Wolf CMS</a></p>
    </div>
</body>
</html>