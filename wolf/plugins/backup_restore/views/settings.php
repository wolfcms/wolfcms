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

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages and settings to an XML file.
 *
 * @package wolf
 * @subpackage plugin.backup_restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.0.1
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Martijn van der Kleijn, 2009
 */
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
        </table>
    </fieldset>

    <p class="buttons">
        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
    </p>
</form>