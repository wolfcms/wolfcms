<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */


/**
 * @package Installer
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

// Errors?
$errors = false;

// Check PHP version
$check = (PHP_VERSION >= '5.3.0');
$php = '<span class="'.($check ? 'check' : 'notcheck');
$php .= '">PHP '.PHP_VERSION.'</span>';
if (!$check) $errors = true;

// check if the PDO driver we need is installed
$check = false;
$check = class_exists('PDO',false);
$pdo = '<span class="'.(($check) ? 'check' : 'notcheck');
$pdo .= '">'.(($check) ? 'true' : 'false').'</span>';
if (!$check) $errors = true;

// Check if proper PDO drivers are available
$mcheck = false;
$scheck = false;
$pcheck = false;

if ($check) {
    $drivers = PDO::getAvailableDrivers();

    $mcheck = in_array('mysql', $drivers);
    $mysql = '<span class="'.($mcheck ? 'check' : 'notcheck').'">'.($mcheck ? 'true' : 'false').'</span>';

    $scheck = in_array('sqlite', $drivers);
    $sqlite = '<span class="'.($scheck ? 'check' : 'notcheck').'">'.($scheck ? 'true' : 'false').'</span>';

    $pcheck = in_array('pgsql', $drivers);
    $pgsql = '<span class="'.($pcheck ? 'check' : 'notcheck').'">'.($pcheck ? 'true' : 'false').'</span>';

    // Make sure EITHER MySQL, SQLite or PostgreSQL is supported
    if (!$mcheck && !$scheck && !$pcheck) $errors = true;
}
else {
    $mysql = '-- n/a --';
    $sqlite = '-- n/a --';
    $pgsql = '-- n/a --';
}

// Check existence of config file
$check = file_exists(CFG_FILE);
$cfg_exists = '<span class="'.((true === $check) ? 'check' : 'notcheck').'">'.((true === $check) ? 'true' : 'false').'</span>';
if (!$check) $errors = true;

// Check config file is writable
$check = is_writable(CFG_FILE);
$cfg_writable = '<span class="'.($check ? 'check' : 'notcheck').'">'.($check ? 'true' : 'false').'</span>';
if (!$check) $errors = true;

// Check public directory is writable
$check = is_writable(PUBLIC_ROOT);
$public_writable = '<span class="'.($check ? 'check' : 'notcheck').'">'.($check ? 'true' : 'false').'</span>';
if (!$check) $errors = true;

// Test for mod_rewrite availability (is not mandatory)
$check = false;
if (isset($_GET['rewrite']) && $_GET['rewrite'] == 1) {
    $check = true;
}
$modrewrite = '<span class="'.($check ? 'check' : 'warning').'">'.($check ? 'true' : 'not detected').'</span>';

// Test for HTTPS support, only possible if user goes to this page with https
$check = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on" || $_SERVER['HTTPS'] == "1"));
$https = '<span class="'.($check ? 'check' : 'notcheck').'">'.($check ? 'true' : 'false').'</span>';

?>

    <h1>Requirements check <img src="install-logo.png" alt="Wolf CMS logo" class="logo" /></h1>
    <p>
        All of the items that are checked below, are <strong>required</strong>
        for proper installation and operation of Wolf CMS unless otherwise specified in the footnotes.
    </p>
    <p>
        Please make sure you either have: MySQL 4.1.x upwards, SQLite 3 or PostgreSQL available as a database.
    </p>
    <table>
        <thead>
            <tr>
                <th id="requirement">Requirement</th>
                <th>Available?</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>PHP 5.1.2</td>
                <td class="available"><?php echo $php; ?></td>
            </tr>
            <tr>
                <td>PDO supported</td>
                <td class="available"><?php echo $pdo; ?></td>
            </tr>
            <tr>
                <td>PDO supports MySQL <sup>1)</sup></td>
                <td class="available"><?php echo $mysql; ?></td>
            </tr>
            <tr>
                <td>PDO supports SQLite 3 <sup>1)</sup></td>
                <td class="available"><?php echo $sqlite; ?></td>
            </tr>
            <tr>
                <td>PDO supports PostgreSQL <sup>1)</sup></td>
                <td class="available"><?php echo $pgsql; ?></td>
            </tr>
            <tr>
                <td>Config file exists <sup>2)</sup></td>
                <td class="available"><?php echo $cfg_exists; ?></td>
            </tr>
            <tr>
                <td>Config file is writable <sup>2)</sup></td>
                <td class="available"><?php echo $cfg_writable; ?></td>
            </tr>
            <tr>
                <td>Public directory is writable <sup>3)</sup></td>
                <td class="available"><?php echo $public_writable; ?></td>
            </tr>
            <tr>
                <td>Clean URLs support available <sup>4)</sup></td>
                <td class="available"><?php echo $modrewrite; ?></td>
            </tr>
        </tbody>
    </table>
    <p class="footnotes">
        <sup>1)</sup> - Only one database <strong>has</strong> to be supported by PDO.
        If you use MySQL, you don't need a SQLite or PostgreSQL driver and visa versa.<br/>

        <sup>2)</sup> - "config.php" at install root.<br/>
        
        <sup>3)</sup> - "public" at install root.<br/>

        <sup>4)</sup> - If "clean urls support available" says "not detected", make sure you have renamed the _.htaccess file into .htaccess. You only need clean URLs support if you want to remove the question mark from the URLs. (mod_rewrite)
    </p>

    <p>
        <form style="text-align: right;" action="index.php" method="POST">
        <?php
        if ($errors) {
            echo 'Please fix these problems and <button type="submit">test again</button>';
        } else {
            if (file_exists(CFG_FILE) && filesize(CFG_FILE) == 0) {
                if ($scheck && !$mcheck && !$pcheck)
                    echo '<input type="hidden" name="dbtype" value="sqlite"/>';
                else if ($mcheck && !$scheck && !$pcheck)
                    echo '<input type="hidden" name="dbtype" value="mysql"/>';
                else if ($pcheck && !$mcheck && !$scheck)
                    echo '<input type="hidden" name="dbtype" value="pgsql"/>';
                echo '<button name="install" type="submit" value="1">Continue to install</button>';
            }
        }
        ?>
        </form>
    </p>
