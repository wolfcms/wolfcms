<?php
/*
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

/**
 * @package Installer
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

require 'Template.php';
require CORE_ROOT.'/app/models/AuthUser.php';

use_helper('Hash');
$hash = new Crypt_Hash('sha256');

$msg = '';
$error = false;
$PDO = false;

// Setup default admin user name in case admin username is not entered in install screen
$admin_name = DEFAULT_ADMIN_USER;

// Generate admin user salt
$admin_salt = AuthUser::generateSalt();

// Create config.php template
$config_tmpl = new Template('config.tmpl');
$config_tmpl->assign($config);

// Get generated config.php
$config_content = $config_tmpl->fetch();

// Write config.php
if (!file_put_contents(CFG_FILE, $config_content)) {
    $error .= "<ul><li><strong>Config file could not be written!</strong></li>\n";
}
else {
    $msg .= "<ul><li>Config file successfully written.</li>\n";
}

if (false === $error) {
    // Include generated config.php
    require CFG_FILE;

    // Generate admin name (defaults to 'admin') and pwd
    if (isset($_POST['config']['admin_username'])) {
        $admin_name = $_POST['config']['admin_username'];
        $admin_name = trim($admin_name);

        try {
            $admin_passwd_precrypt = '12'.dechex(rand(100000000, 4294967295)).'K';
            $admin_passwd = AuthUser::generateHashedPassword($admin_passwd_precrypt,$admin_salt);
        } catch (Exception $e) {
            $error = 'Wolf CMS could not generate a default administration password and has not been installed.<br />The following error has occured: <p><strong>'. $e->getMessage() ."</strong></p>\n";
            file_put_contents(CFG_FILE, '');
        }
    }

    // If DB is SQLite, check that DB directory is writable.
    if (false === $error && $_POST['config']['db_driver'] == 'sqlite') {
        $sqlite_db = $_POST['config']['db_name'];

        if (false !== strrpos($sqlite_db, '/')) {
            $sqlite_dir = substr($sqlite_db, 0, strrpos($sqlite_db, '/'));
        }
        else {
            $sqlite_dir = substr($sqlite_db, 0, strrpos($sqlite_db, '\\'));
        }
        
        if (!file_exists($sqlite_db) && !is_writable($sqlite_dir)) {
            $error = 'Wolf CMS could not access the specified SQLite directory in order to create the SQLite DB.';
            file_put_contents(CFG_FILE, '');
        }

        if (file_exists($sqlite_db) && !is_writable($sqlite_db)) {
            $error = 'Wolf CMS could not access the specified SQLite DB.';
            file_put_contents(CFG_FILE, '');
        }
    }
    
    // Validate DB host
    $db_host = trim($_POST['config']['db_host']);
    if (empty($db_host)) {
        $error = 'Database host is missing.';
        file_put_contents(CFG_FILE, '');
    }

    // Validate DB user
    $db_user = trim($_POST['config']['db_user']);
    if (empty($db_user)) {
        $error = 'Database user is missing.';
        file_put_contents(CFG_FILE, '');
    }

    // Validate DB name
    $db_name = trim($_POST['config']['db_name']);
    if (empty($db_name)) {
        $error = 'Database name is missing.';
        file_put_contents(CFG_FILE, '');
    }

    // Validate DB table prefix
    $table_prefix = trim($_POST['config']['table_prefix']);
    if (!empty($table_prefix)) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_prefix)) {
            $error = 'Database prefix is not valid. Acceptable database prefix characters are: a-z, 0-9 and _.';
            file_put_contents(CFG_FILE, '');
        }
    }

    // Try creating a new PDO object to connect to DB
    if (false === $error) {
        try {
            $PDO = new PDO(DB_DSN, DB_USER, DB_PASS);
        } catch (PDOException $e) {
            $error = 'Wolf CMS could not connect to the database and has not been installed.<br />The following error has occured: <p><strong>'. $e->getMessage() ."</strong></p>\n";
            file_put_contents(CFG_FILE, '');
        }
    }

    // Run the SQL to setup DB contents
    if (false === $error) {
        if ($PDO) {
            $msg .= '<li>Database connection successfull.</li>';

            try {
                require_once 'schema_'.$_POST['config']['db_driver'].'.php';
            }
            catch (Exception $e) {
                $error = 'Wolf CMS could not create the database schema and has not been installed properly.<br />The following error has occured: <p><strong>'. $e->getMessage() ."</strong></p>\n";
            }

            try {
                $dbdriver = $_POST['config']['db_driver'];
                require_once 'sql_data.php';
            }
            catch (Exception $e) {
                $error = 'Wolf CMS could not create the default database contents and has not been installed properly.<br />The following error has occured: <p><strong>'. $e->getMessage() ."</strong></p>\n";
            }

            $msg .= '<li>Tables loaded successfully</li></ul>
                     <p>You can now login at <a href="'.URL_PUBLIC.(USE_MOD_REWRITE ? '' : '?/').ADMIN_DIR.'">the login page</a> with: </p>
                     <p>
                        <strong>Login</strong> - '.$admin_name.'<br />
                        <strong>Password <sup>1)</sup></strong> - '.$admin_passwd_precrypt.'
                     </p>
                    ';
        }
        else {
            $error = 'Wolf CMS could not connect to the database and was unable to create its database tables!';
        }
    }
}
?>