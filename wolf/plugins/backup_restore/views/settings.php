<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages, settings and uploaded files to an XML file.
 *
 * @package Plugins
 * @subpackage backup-restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Frank Edelhaeuser <mrpace2@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2011
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<h1><?php echo __('Settings'); ?></h1>

<form action="<?php echo get_url('plugin/backup_restore/save'); ?>" method="post">
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Backup settings'); ?></legend>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="label"><label for="setting_pwd"><?php echo __('Include passwords'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[pwd]" id="setting_pwd">
                        <option value="1" <?php if ($settings['pwd'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['pwd'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Do you want to include passwords in the backup file? <br/> If you select no, all passwords will be reset upon restoring the backup.'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="setting_backupfiles"><?php echo __('Include files'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[backupfiles]" id="setting_backupfiles">
                        <option value="1" <?php if ($settings['backupfiles'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['backupfiles'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Do you want to include uploaded files in the backup file?'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="setting_zip"><?php echo __('Package as zip file'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[zip]" id="setting_zip">
                        <option value="1" <?php if ($settings['zip'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['zip'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Do you want to download the backup as a zip file?'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="setting_stamp"><?php echo __('Filename timestamp style'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[stamp]" id="setting_stamp">
                        <option value="Ymd" <?php if ($settings['stamp'] == "Ymd") echo 'selected ="";' ?>><?php echo date('Ymd'); ?></option>
                        <option value="YmdHi" <?php if ($settings['stamp'] == "YmdHi") echo 'selected ="";' ?>><?php echo date('YmdHi'); ?></option>
                        <option value="YmdHis" <?php if ($settings['stamp'] == "YmdHis") echo 'selected ="";' ?>><?php echo date('YmdHis'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('What style of timestamp should be encorporated into the filename.'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="settings_extension"><?php echo __('Filename extension'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[extension]" id="setting_extension">
                        <option value="xml" <?php if ($settings['extension'] == "xml") echo 'selected ="";' ?>>.xml</option>
                        <option value="bak" <?php if ($settings['extension'] == "bak") echo 'selected ="";' ?>>.bak</option>
                        <option value="tmp" <?php if ($settings['extension'] == "tmp") echo 'selected ="";' ?>>.tmp</option>
                        <option value="ori" <?php if ($settings['extension'] == "ori") echo 'selected ="";' ?>>.ori</option>
                        <option value="dat" <?php if ($settings['extension'] == "dat") echo 'selected ="";' ?>>.dat</option>
                        <option value="db" <?php if ($settings['extension'] == "db") echo 'selected ="";' ?>>.db</option>
                    </select>
                </td>
                <td class="help"><?php echo __('What extension should be used for the filename.'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="example_filename"><?php echo __('Current style'); ?>: </label></td>
                <td class="field"><input class="textbox" id="example_filename" maxlength="255" name="example_filename" size="255" type="text" readonly="readonly" value="wolfcms-backup-<?php echo date($settings['stamp']); ?>.xml" /></td>
                <td class="help"><?php echo __('This is an example of the filename that will be used for the generated XML file.'); ?></td>
            </tr>
        </table>
    </fieldset>
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Restore settings'); ?></legend>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="label"><label for="setting_default_pwd"><?php echo __('Reset passwords to'); ?>: </label></td>
                <td class="field"><input class="textbox" id="setting_default_pwd" maxlength="255" name="settings[default_pwd]" size="255" type="text" value="<?php echo $settings['default_pwd']; ?>" /></td>
                <td class="help"><?php echo __('If no password is provided in the backup file, reset all password fields to this default.'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="setting_erasefiles"><?php echo __('Erase files'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[erasefiles]" id="setting_erasefiles">
                        <option value="1" <?php if ($settings['erasefiles'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['erasefiles'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Erase uploaded files before restoring backup?'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="setting_restorefiles"><?php echo __('Restore files'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[restorefiles]" id="setting_restorefiles">
                        <option value="1" <?php if ($settings['restorefiles'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['restorefiles'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Restore uploaded files from backup?'); ?></td>
            </tr>
        </table>
    </fieldset>

    <p class="buttons">
        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
    </p>
</form>

<script type="text/javascript">
// <![CDATA[
    function setConfirmUnload(on, msg) {
        window.onbeforeunload = (on) ? unloadMessage : null;
        return true;
    }

    function unloadMessage() {
        return '<?php echo __('You have modified this page.  If you navigate away from this page without first saving your data, the changes will be lost.'); ?>';
    }

    $(document).ready(function() {
        // Prevent accidentally navigating away
        $(':input').bind('change', function() { setConfirmUnload(true); });
        $('form').submit(function() { setConfirmUnload(false); return true; });
    });
// ]]>
</script>