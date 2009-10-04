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

define('CORE_ROOT', dirname(__FILE__).'/../wolf');

// Check PHP version
$php = '<span class="'.((PHP_VERSION > 5.1) ? 'check' : 'notcheck');
$php .= '">PHP '.PHP_VERSION.'</span>';

// check if the PDO driver we need is installed
$pdocheck = false;
$pdocheck = method_exists('PDO','getAvailableDrivers');
$pdo = '<span class="'.(($pdocheck) ? 'check' : 'notcheck');
$pdo .= '">'.(($pdocheck) ? 'true' : 'false').'</span>';

// Check if proper PDO drivers are available
if ($pdocheck) {
    $drivers = PDO::getAvailableDrivers();

    $mysqlcheck = in_array('mysql', $drivers);
    $mysql = '<span class="'.($mysqlcheck ? 'check' : 'notcheck').'">'.($mysqlcheck ? 'true' : 'false').'</span>';

    $sqlitecheck = in_array('sqlite2', $drivers);
    $sqlite = '<span class="'.($sqlitecheck ? 'check' : 'notcheck').'">'.($sqlitecheck ? 'true' : 'false').'</span>';
}
else {
    $mysql = '-- n/a --';
    $sqlite = '-- n/a --';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <title>Wolf CMS - Requirements check</title>
        <link href="install.css" media="screen" rel="Stylesheet" type="text/css" />
    </head>
    <body id="installation">
        <div id="header">
            <div id="site-title">Wolf CMS - Requirements check</div>
        </div>
        <div id="main">
            <div id="content-wrapper">
                <div id="content">
                    <!-- Content -->
                    <div>
                        <img src="install-logo.png" alt="Wolf CMS logo" class="logo" />
                        <p>
                            All of the items that are checked below, are <strong>required</strong>
                            for proper operation of Wolf CMS unless otherwise specified in the footnotes.
                        </p>
                        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
                            <thead>
                                <tr>
                                    <td>Requirement</td>
                                    <td>Available?</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="requirement">PHP 5.1</td>
                                    <td style="text-align: center;"><?php echo $php; ?></td>
                                </tr>
                                <tr>
                                    <td class="requirement">PDO supported</td>
                                    <td style="text-align: center;"><?php echo $pdo; ?></td>
                                </tr>
                                <tr>
                                    <td class="requirement">PDO supports MySQL <sup>1)</sup></td>
                                    <td style="text-align: center;"><?php echo $mysql; ?></td>
                                </tr>
                                <tr>
                                    <td class="requirement">PDO supports SQLite 3 <sup>1)</sup></td>
                                    <td style="text-align: center;"><?php echo $sqlite; ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <p>
                            <sup>1)</sup> - Only one database <strong>has</strong> to be supported by PDO.
                            If you use MySQL, you don't need a SQLite driver and visa versa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div id="footer">
            <p>Powered by <a href="http://www.wolfcms.org/">Wolf CMS</a></p>
        </div>
    </body>
</html>
