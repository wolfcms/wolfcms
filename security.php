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

// Make sure we hide ugly errrors
error_reporting(0);

define('SECURITY_CHECK', true);

define('DS', DIRECTORY_SEPARATOR);
define('CORE_ROOT', dirname(__FILE__).DS.'wolf');
define('CFG_FILE', 'config.php');

require(CORE_ROOT.DS.'utils.php');
require(CFG_FILE);

if (!defined('DEBUG')) { echo 'Please install Wolf CMS first, thank you.'; exit(); }

// Lets make sure we do a valid (uncached) check.
clearstatcache();
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


    /* RUN CHECKS - ADVISORIES */

    // Does the README.md file exist?
    if (file_exists(CORE_ROOT.DS.'..'.DS.'README.md')) {
        $advisories['README.md, file present'] = 'The README.md is still present. You may want to remove it for added security.';
    }

    // Does the CONTRIBUTING.md file exist?
    if (file_exists(CORE_ROOT.DS.'..'.DS.'CONTRIBUTING.md')) {
        $advisories['CONTRIBUTING.md, file present'] = 'The CONTRIBUTING.md is still present. You may want to remove it for added security.';
    }

    // Does the composer.json file exist?
    if (file_exists(CORE_ROOT.DS.'..'.DS.'composer.json')) {
        $advisories['composer.json, file present'] = 'The composer.json is still present. This file is only needed for Wolf CMS development. You may want to remove it for added security.';
    }

    // Does the .travis.yml file exist?
    if (file_exists(CORE_ROOT.DS.'..'.DS.'.travis.yml')) {
        $advisories['.travis.yml, file present'] = 'The .travis.yml is still present. This file is only needed for Wolf CMS development. You may want to remove it for added security.';
    }

    // Is the config file writable?
    if (isWritable(CFG_FILE)) {
        $advisories['config file, writable'] = 'The configuration file has been found to be writable. We would advise you to remove all write permissions on config.php on production systems. As long as no FATAL level potential security issues were detected with the config.php file, you will still be able to run Wolf CMS.';
    }
    
    // Is the 'wolf' directory writable?
    if (isWritable(CORE_ROOT.DS)) {
        $advisories['core directory, writable'] = 'The Wolf CMS core directory ("wolf/") and/or files underneath it has been found to be writable. We would advise you to remove all write permissions. <br/>You can do this on unix systems with: <code>chmod -R a-w '.CORE_ROOT.DS.'</code>';
    }
    
    // Is the '.htaccess' file writable?
    if (isWritable(dirname(__FILE__).DS.'.htaccess')) {
        $advisories['htaccess file, writable'] = 'The Wolf CMS .htaccess file has been found to be writable. We would advise you to remove all write permissions. <br/>You can do this on unix systems with: <code>chmod a-w '.dirname(__FILE__).DS.'.htaccess'.'</code>';
    }
    
    // Is the 'index.php' file writable?
    if (isWritable(dirname(__FILE__).DS.'index.php')) {
        $advisories['index.php file, writable'] = 'The Wolf CMS index.php file has been found to be writable. We would advise you to remove all write permissions. <br/>You can do this on unix systems with: <code>chmod a-w '.dirname(__FILE__).DS.'index.php'.'</code>';
    }


    /* RUN CHECKS - WARNINGS */

    // Is DEBUG turned on?
    if (DEBUG === true) {
        $warnings['debug on'] = 'Due to the type and amount of information an error might give intruders when debug is turned on, we strongly advise setting debug to FALSE in production systems.';
    }

    // Does the docs directory exist?
    if (file_exists(CORE_ROOT.DS.'..'.DS.'docs'.DS)) {
        $warnings['docs, directory present'] = 'The documenation directory ("docs/") is still present. You may want to remove it for added security.';
    }


    /* RUN CHECKS - FATALS */

    // fileperms() based checks
    if (function_exists('fileperms')) {
        
        // Does the config file have write permissions for the group or the world?
        $fileperms = fileperms(CFG_FILE);
        if ((($fileperms & 0x0010) || ($fileperms & 0x0002))) {
            $fatals['config file, group owned, world owned, file writable'] = 'Wolf CMS has automatically made itself unavailable because the configuration file was found to be writable for the group / the world. Until this security issue is corrected, only this screen will be available.';
        }

        // posix_getuid(), fileowner() and filegroup() based checks
        if (function_exists('posix_getuid') && function_exists('fileowner') && function_exists('filegroup')) {
            $processowner = posix_getuid();

            // Is the Wolf CMS root directory owned by http server and does it have write permissions?
            $rootowner = fileowner(CORE_ROOT.DS.'..'.DS);
            $rootperms = fileperms(CORE_ROOT.DS.'..'.DS);
            if ($rootowner == $processowner && ($rootperms & 0x0080)) {
                $fatals['wolf cms root directory, user owned, writable'] = 'The root directory in which Wolf CMS is installed was found to be owned by the same user under whom the HTTP server is running and it has write access.';
            }

            // Is the Wolf CMS root directory writable for the world?
            $rootperms = fileperms(CORE_ROOT.DS.'..'.DS);
            if (($rootperms & 0x0002)) {
                $fatals['wolf cms root directory, writable'] = 'The root directory in which Wolf CMS is installed was found to have write access for the world.';
            }

            // Is the Wolf CMS system directory owned by http server and does it have write permissions?
            $coreowner = fileowner(CORE_ROOT.DS);
            $coreperms = fileperms(CORE_ROOT.DS);
            if ($coreowner == $processowner && ($coreperms & 0x0080)) {
                $fatals['wolf directory, user owned, writable'] = 'The core directory of the Wolf CMS system ("wolf/") was found to be owned by the same user under whom the HTTP server is running and it has write access.';
            }

            // Is the Wolf CMS system directory has write permissions?
            $coreperms = fileperms(CORE_ROOT.DS);
            if (($coreperms & 0x0010) || ($coreperms & 0x0002)) {
                $fatals['wolf directory, group owned, world owned, writable'] = 'The core directory of the Wolf CMS system ("wolf/") was found to be writable for the group and/or the world.';
            }

            // Is the config file owned by http server and does it have write permissions?
            $fileowner = fileowner(CFG_FILE);
            $fileperms = fileperms(CFG_FILE);
            if ($fileowner == $processowner && ($fileperms & 0x0080)) {
                $fatals['config file, user owned, writable'] = 'The config file is owned by the same user under whom the HTTP server is running and has write access.';
            }

            // Does the public directory have write permissions for the group or the world while its not needed?
            $publicowner = filegroup(CORE_ROOT.DS.'..'.DS.'public'.DS);
            $publicperms = fileperms(CORE_ROOT.DS.'..'.DS.'public'.DS);
            if ($publicowner == $processowner && (($publicperms & 0x0010) || ($publicperms & 0x0002))) {
                $fatals['public directory, group owned, world owned, writable'] = 'The public directory ("public/") was found to be writable for the group and/or the world. We strongly advise you not to do this since it is usually not necessary for the proper operation of the Filemanager plugin.';
            }
        }
    }

    // Does the install directory exist?
    if (file_exists(CORE_ROOT.DS.'install'.DS)) {
        $fatals['install, directory present'] = 'The installation directory ("wolf/install/") is still present.';
    }

    // Does the SQlite DB file exist inside the Wolf CMS root directory?
    if (defined('DB_DSN') && startsWith(DB_DSN, 'sqlite:'.realpath(dirname(__FILE__)))) {
        $fatals['db, sqlite location'] = 'It would appear that the SQLite database file is stored inside of web accessible directory. This is an insecure practice.';
    }

    if (file_exists(CORE_ROOT.DS.'..'.DS.'security.php')) {
        $fatals['security.php, file present'] = 'The security.php file is still present. Please remove it to prevent abuse.';
    }

    /* END CHECKS - DUMP OUTPUT */
?>

    <h1>Overview</h1>
    <p>Once your Wolf CMS installation is running in production status, you are strongly advised to remove this file ("/security.php") to prevent abuse.</p>
    <?php if (count($fatals) > 0) { echo '<p>One or more FATAL level potential security issues have been detected. You are strongly advised to correct them!</p>'; } ?>
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
    <p>
        Go to your site's <a href="index.php">front page</a> or the <a href="<?php echo URL_PUBLIC . ((USE_MOD_REWRITE) ? '' : '?/') . ADMIN_DIR;?>">administrative interface</a>.
    </p>
    <hr/>
    <p><small>DISCLAIMER - neither the Wolf CMS project nor any of its contributors provide any warranty, for details, please see /docs/license.txt in the download package.</small></p>

    </div>
    <div id="footer">
        <p>Powered by <a href="http://www.wolfcms.org/">Wolf CMS</a></p>
    </div>
</body>
</html>