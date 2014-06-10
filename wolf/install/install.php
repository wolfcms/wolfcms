<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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

$drivers = PDO::getAvailableDrivers();
?>

    <script type="text/javascript" charset="utf-8" src="../admin/javascripts/jquery-1.8.3.min.js"></script>
    <script type="text/javascript">
    // <![CDATA[
        $(document).ready(function() {
            $('#config_db_driver').change(function() {

                if (this.value == 'sqlite') {
                    $('#config_db_name').val('<?php echo str_replace('\\','/',realpath(dirname(__FILE__).'/../../../')).'/db/wolf.sq3' ?>');
                    $('#help-db-name').html('Required. Enter the <strong>absolute</strong> path to the database file.<br/>You are <strong>strongly</strong> advised to keep the Wolf CMS SQLite database outside of the webserver root.');
                    $('#help-db-prefix').html('Optional. Usefull to prevent conflicts if you have, or plan to have, multiple Wolf installations with a single database.');
                    $('#row-table-prefix label').addClass('optional');
                    $('#row-db-socket').hide();
                    $('#row-db-host').hide();
                    $('#row-db-port').hide();
                    $('#row-db-user').hide();
                    $('#row-db-pass').hide();
                    $('#row-table-prefix').hide();
                }
                else {
                    $('#config_db_name').val('wolf');
                    $('#help-db-name').html('Required. You have to create a database manually and enter its name here.');
                    if (this.value == 'mysql') {
                        $('#config_db_port').val('3306');
                        $('#row-table-prefix label').addClass('optional');
                        $('#help-db-prefix').html('Optional. Usefull to prevent conflicts if you have, or plan to have, multiple Wolf installations with a single database.');
                    }
                    if (this.value == 'pgsql') {
                        $('#config_db_port').val('5432');
                        $('#row-table-prefix label').removeClass('optional');
                        $('#config_table_prefix').val('wolf_');
                        $('#help-db-prefix').html('<strong>Required.</strong> When using PostgreSQL, you have to specify a table prefix.');
                    }
                    $('#row-db-socket').show();
                    $('#row-db-host').show();
                    $('#row-db-port').show();
                    $('#row-db-user').show();
                    $('#row-db-pass').show();
                    $('#row-table-prefix').show();
                }
            });

            $('#config_db_driver').trigger('change');
        });

    // ]]>
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
                    <select id="config_db_driver" name="config[db_driver]">
                        <?php /*if (isset($_POST['dbtype']) && !empty($_POST['dbtype']) && $_POST['dbtype'] == 'sqlite') {
                            echo '<option value="sqlite">SQLite 3</option>';
                        }
                        else if (isset($_POST['dbtype']) && !empty($_POST['dbtype']) && $_POST['dbtype'] == 'mysql') {
                            echo '<option value="mysql">MySQL</option>';
                        }
                        else if (isset($_POST['dbtype']) && !empty($_POST['dbtype']) && $_POST['dbtype'] == 'pgsql') {
                            echo '<option value="pgsql">PostgreSQL</option>';
                        } else {*/
                            if (in_array('mysql', $drivers)) {
                                echo '<option value="mysql">MySQL</option>';
                            }
                            if (in_array('pgsql', $drivers)) {
                                echo '<option value="pgsql">PostgreSQL</option>';
                            }
                            if (in_array('sqlite', $drivers)) {
                                echo '<option value="sqlite">SQLite 3</option>';
                            //}
                        } ?>
                    </select>
                </td>
                <td class="help">Required.</td>
            </tr>
            <tr id="row-db-host">
                <td class="label"><label for="config_db_host">Database server</label></td>
                <td class="field"><input class="textbox" id="config_db_host" maxlength="100" name="config[db_host]" size="50" type="text" value="localhost" /></td>
                <td class="help">Required.</td>
            </tr>
            <tr id="row-db-port">
                <td class="label"><label class="optional" for="config_db_port">Port</label></td>
                <td class="field"><input class="textbox" id="config_db_port" maxlength="10" name="config[db_port]" size="50" type="text" value="" /></td>
                <td class="help">Optional. Default MySQL: 3306; default PostgreSQL: 5432</td>
            </tr>
            <tr id="row-db-socket">
                <td class="label"><label for="config_db_socket">Database unix socket</label></td>
                <td class="field"><input class="textbox" id="config_db_socket" maxlength="100" name="config[db_socket]" size="50" type="text" value="" /></td>
                <td class="help">Optional. When filled, database servername and port are ignored. (/path/to/socket)</td>
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
                <td class="help" id="help-db-prefix">Optional. Useful to prevent conflicts if you have, or plan to have, multiple Wolf installations with a single database.</td>
            </tr>
            <tr>
                <td colspan="3"><h3>Other information</h3></td>
            </tr>
            <tr>
                <td class="label"><label for="config_admin_username">Administrator username</label></td>
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
