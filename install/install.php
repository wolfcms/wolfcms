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

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}
?>

    <script type="text/javascript" charset="utf-8" src="../admin/javascripts/prototype.js"></script>
    <script type="text/javascript">
        function db_driver_change(driver) {
            Element.toggle('row-db-host');
            Element.toggle('row-db-port');
            Element.toggle('row-db-user');
            Element.toggle('row-db-pass');
            Element.toggle('row-table-prefix');

            if (driver == 'sqlite') {
                $('config_db_name').value = '<?php echo realpath(dirname(__FILE__).'/../../').'/db/wolf.sq3' ?>';
                $('help-db-name').innerHTML = 'Required. Enter the <strong>absolute</strong> path to the database file.<br/>You are <strong>strongly</strong> advised to keep the Wolf CMS SQLite database outside of the webserver root.';
            }
            else if (driver == 'mysql') {
                $('help-db-name').innerHTML = 'Required. You have to create a database manually and enter its name here.';
            }
        }
    </script>

    <h1>Installation information <img src="install-logo.png" alt="Wolf CMS logo" class="logo" /></h1>
    <p>
        When setting up Wolf CMS for use with multiple sites, please remember to either choose a site specific
        database name or to use a site specific table prefix.
    </p>

    <form action="index.php" method="post">
        <input type="hidden" name="install" value="1"/>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td colspan="3"><h3>Database information</h3></td>
            </tr>
            <tr>
                <td class="label"><label for="config_db_driver">Database driver</label></td>
                <td class="field">
                    <select id="config_db_driver" name="config[db_driver]" onchange="db_driver_change(this[this.selectedIndex].value);">
                        <?php if (isset($_POST['dbtype']) && !empty($_POST['dbtype']) && $_POST['dbtype'] == 'sqlite') { ?>
                        <option value="sqlite">SQLite 3</option>
                        <?php } else if (isset($_POST['dbtype']) && !empty($_POST['dbtype']) && $_POST['dbtype'] == 'mysql') { ?>
                        <option value="mysql">MySQL</option>
                        <?php } else { ?>
                        <option value="mysql">MySQL</option>
                        <option value="sqlite">SQLite 3</option>
                        <?php } ?>
                    </select>
                </td>
                <td class="help">Required. PDO support and the SQLite 3 plugin are required to use SQLite 3.</td>
            </tr>
            <tr id="row-db-host">
                <td class="label"><label for="config_db_host">Database server</label></td>
                <td class="field"><input class="textbox" id="config_db_host" maxlength="100" name="config[db_host]" size="50" type="text" value="localhost" /></td>
                <td class="help">Required.</td>
            </tr>
                <tr id="row-db-port">
                <td class="label"><label for="config_db_port">Port</label></td>
                <td class="field"><input class="textbox" id="config_db_port" maxlength="10" name="config[db_port]" size="50" type="text" value="3306" /></td>
                <td class="help">Optional. Default: 3306</td>
            </tr>
            <tr id="row-db-user">
                <td class="label"><label for="config_db_user">Database user</label></td>
                <td class="field"><input class="textbox" id="config_db_user" maxlength="255" name="config[db_user]" size="50" type="text" value="root" /></td>

                <td class="help">Required.</td>
            </tr>
            <tr id="row-db-pass">
                <td class="label"><label class="optional" for="config_db_pass">Database password</label></td>
                <td class="field"><input class="textbox" id="config_db_pass" maxlength="40" name="config[db_pass]" size="50" type="password" value="" /></td>
                <td class="help">Optional. If there is no database password, leave it blank.</td>
            </tr>
            <tr id="row-db-name">
                <td class="label"><label for="config_db_name">Database name</label></td>
                <td class="field"><input class="textbox" id="config_db_name" maxlength="120" name="config[db_name]" size="50" type="text" value="wolf" /></td>
                <td class="help" id="help-db-name">Required. You have to create a database manually and enter its name here.</td>
            </tr>
            <tr id="row-table-prefix">
                <td class="label"><label class="optional" for="config_table_prefix">Table prefix</label></td>
                <td class="field"><input class="textbox" id="config_table_prefix" maxlength="40" name="config[table_prefix]" size="50" type="text" value="" /></td>
                <td class="help">Optional. Usefull to prevent conflicts if you have, or plan to have, multiple Wolf installations with a single database.</td>
            </tr>
            <tr>
                <td colspan="3"><h3>Other information</h3></td>
            </tr>
            <tr>
                <td class="label"><label class="optional" for="config_admin_username">Administrator username</label></td>
                <td class="field"><input class="textbox" id="config_admin_username" maxlength="40" name="config[admin_username]" size="50" type="text" value="<?php echo DEFAULT_ADMIN_USER; ?>" /></td>
                <td class="help">Required. Allows you to specify a custom username for the administrator. Default: admin</td>
            </tr>
            <tr>
                <td class="label"><label class="optional" for="config_url_suffix">URL suffix</label></td>
                <td class="field"><input class="textbox" id="config_url_suffix" maxlength="40" name="config[url_suffix]" size="50" type="text" value=".html" /></td>
                <td class="help">Optional. Add a suffix to simulate static html files.</td>
            </tr>
            <tr>
                <td class="label"><label class="optional" for="config_mod_rewrite">Use clean URLs</label></td>
                <td class="field"><input class="checkbox" id="config_mod_rewrite" name="config[mod_rewrite]" type="checkbox"<?php echo (isset($_GET['rewrite']) && $_GET['rewrite'] == 1) ? ' checked="checked"' : ' disabled="disabled"'; ?>/></td>
                <td class="help">Optional. Use clean URLs without the question mark.</td>
            </tr>
        </table>
        <p class="buttons">
            <button class="button" name="commit" type="submit">Install now!</button>
        </p>
    </form>
<?php if (isset($_POST['dbtype']) && !empty($_POST['dbtype']) && $_POST['dbtype'] != 'mysql') { ?>
    <script type="text/javascript">
        // DOM is ready, do scripty stuff now
        $('config_db_driver').value = '<?php echo trim($_POST['dbtype']);?>';
        db_driver_change($('config_db_driver').value);
    </script>
<?php } ?>