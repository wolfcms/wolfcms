<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010-2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * @package Plugins
 * @subpackage file-manager
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010-2013
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<h1><?php echo __('File Manager Settings');?></h1>
<form action="<?php echo get_url('plugin/file_manager/settings_save'); ?>" method="post">
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('General settings'); ?></legend>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="label"><label for="setting_show_hidden"><?php echo __('Show hidden files'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[show_hidden]" id="setting_show_hidden">
                        <option value="1" <?php if ($settings['show_hidden'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['show_hidden'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Do you want to display hidden files on unix systems? <br/> If you select no, all files starting with "." will not be displayed.'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="setting_show_backups"><?php echo __('Show backup files'); ?>: </label></td>
                <td class="field">
                    <select class="select" name="settings[show_backups]" id="setting_show_backups">
                        <option value="1" <?php if ($settings['show_backups'] == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
                        <option value="0" <?php if ($settings['show_backups'] == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
                    </select>
                </td>
                <td class="help"><?php echo __('Do you want to show backup files? If you select no, all files ending with "~" will not be displayed.'); ?></td>
            </tr>
        </table>
    </fieldset>
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('File Creation Defaults'); ?></legend>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="label"><label for="umask"><?php echo __('Umask:');?> </label></td>
                <td class="field"><input name="settings[umask]" id="umask" type="text" size="35" maxsize="255" value="<?php echo $settings['umask'];?>"/></td>
                <td class="help"><?php echo __('Default PHP umask; see <a href="http://php.net/manual/en/function.umask.php">umask()</a>');?></td>
            </tr>
            <tr>
                <td class="label"><label for="dirmode"><?php echo __('Directory Creation Mode:');?> </label></td>
                <td class="field"><input name="settings[dirmode]" id="dirmode" type="text" size="35" maxsize="255" value="<?php echo $settings['dirmode'];?>"/></td>
                <td class="help"><?php echo __('Default PHP directory creation mode; see <a href="http://php.net/manual/en/function.chmod.php">chmod()</a>');?></td>
            </tr>
            <tr>
                <td class="label"><label for="filemode"><?php echo __('File Creation Mode:');?> </label></td>
                <td class="field"><input name="settings[filemode]" id="filemode" type="text" size="35" maxsize="255" value="<?php echo $settings['filemode'];?>"/></td>
                <td class="help"><?php echo __('Default PHP file creation mode; see <a href="http://php.net/manual/en/function.chmod.php">chmod()</a>');?></td>
            </tr>
        </table>
    </fieldset>
    <p class="buttons">
        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save');?>" />
    </p>
</form>
