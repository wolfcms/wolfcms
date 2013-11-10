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

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

if (!class_exists('PDO',false)) {
    echo '<p>Wolf CMS requires PDO support which was not detected. Terminating.</p>';
    exit();
}

$drivers = PDO::getAvailableDrivers();
?>

    <h1>Upgrade information <img src="install-logo.png" alt="Wolf CMS logo" class="logo" /></h1>
    <p>
        An existing configuration file has been detected.
        If you wish to upgrade using this script, you will have to first validate your choice by entering
        your administrator username and password.
    </p>
    <p>
        <strong>Important:</strong> before you decide to upgrade, please make sure you have read and understood the following items
    </p>
    <ul>
        <li>Make sure you have created a BACKUP of your database before proceeding.</li>
        <li>This will only touch the CORE database tables, not third party plugin tables.</li>
        <li>This is <strong>only</strong> for upgrades from 0.7.7 to 0.7.8.</li>
    </ul>
    <p>
        Do you wish to upgrade:
        <strong>Wolf CMS 0.7.7</strong>
        =&gt; <strong>Wolf CMS 0.7.8</strong>?
    </p>

    <form action="index.php" method="post">
        <input type="hidden" name="upgrade" value="1"/>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td colspan="3">
                    <h3>Administrator validation</h3>
                </td>
            </tr>
            <tr>
                <td class="label"><label for="config_admin_username">Administrator username</label></td>
                <td class="field"><input class="textbox" id="config_admin_username" maxlength="40" name="upgrade[username]" size="50" type="text" value="" /></td>
                <td class="help">Required. Enter the username for the administrator. Default: admin</td>
            </tr>
            <tr>
                <td class="label"><label for="config_admin_password1">Administrator password</label></td>
                <td class="field"><input class="textbox" id="config_admin_password1" maxlength="40" name="upgrade[pwd]" size="50" type="password" value="" /></td>
                <td class="help">Required. Enter the password for the administrator.</td>
            </tr>
            <tr>
                <td class="label"><label for="config_admin_password1">Re-enter password</label></td>
                <td class="field"><input class="textbox" id="config_admin_password2" maxlength="40" name="upgrade[pwd_check]" size="50" type="password" value="" /></td>
                <td class="help">Required. Re-enter the password for the administrator.</td>
            </tr>
        </table>
        <p class="buttons">
            <button class="button" name="commit" type="submit">Upgrade now!</button>
        </p>
    </form>