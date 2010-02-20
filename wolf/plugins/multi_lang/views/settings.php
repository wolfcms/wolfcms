<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
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

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The multi lang plugin redirects users to a page with content in their language.
 *
 * The redirect only occurs when a user's indicated preferred language is
 * available. There are multiple methods to determine the desired language.
 * These are:
 *
 * - HTTP_ACCEPT_LANG header
 * - URI based language hint (for example: http://www.example.com/en/page.html
 * - Preferred language setting of logged in users
 *
 * @package wolf
 * @subpackage plugin.multi_lang
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.0.0
 * @since Wolf version 0.7.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2010
 */
?>

<h1><?php echo __('Multiple Language Settings'); ?></h1>
<form action="<?php echo get_url('plugin/multi_lang/save'); ?>" method="post">
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('General'); ?></legend>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="label"><label for="settings[style]"><?php echo __('Style'); ?>: </label></td>
                <td class="field">
					<select name="settings[style]">
						<option value="tab" <?php if($settings['style'] == "tab") echo 'selected ="";' ?>><?php echo __('Translations as tab'); ?></option>
						<option value="page" <?php if($settings['style'] == "page") echo 'selected ="";' ?>><?php echo __('Translations as page copy'); ?></option>
					</select>
				</td>
                <td class="help"><?php echo __('Do you want to create a translated version of a page as a tab of the same page or as a copy of the page in a language specific subtree? (i.e. Home->nl->About as a Dutch translation of Home->About)'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="settings[langsource]"><?php echo __('Language source'); ?>: </label></td>
                <td class="field">
					<select name="settings[langsource]">
						<option value="header" <?php if($settings['langsource'] == "header") echo 'selected ="";' ?>><?php echo __('HTTP_ACCEPT_LANG header'); ?></option>
						<option value="uri" <?php if($settings['langsource'] == "uri") echo 'selected ="";' ?>><?php echo __('URI'); ?></option>
                        <option value="preferences" <?php if($settings['langsource'] == "preferences") echo 'selected ="";' ?>><?php echo __('Wolf CMS user preferences'); ?></option>
					</select>
				</td>
                <td class="help"><?php echo __('Get the language preference from the HTTP header (default), the uri (/nl/about.html for the Dutch version of about.html) or from the stored preference of a logged in user.'); ?></td>
            </tr>
        </table>
    </fieldset>
    <br/>
    <p class="buttons">
        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
    </p>
</form>