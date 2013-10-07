<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<h1><?php echo __('Comments Plugin'); ?></h1>

<form action="<?php echo get_url('plugin/comment/save'); ?>" method="post">
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Comments settings'); ?></legend>
        <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="label"><label for="autoapprove"><?php echo __('Auto approve'); ?>: </label></td>
                <td class="field">
					<select name="autoapprove">
						<option value="1" <?php if($approve == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
						<option value="0" <?php if($approve == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
					</select>	
				</td>
                <td class="help"><?php echo __('Choose yes if you want your comments to be auto approved. Otherwise, they will be placed in the moderation queue.'); ?></td>
            </tr>
            <tr>
                <td class="label"><label for="captcha"><?php echo __('Use captcha'); ?>: </label></td>
                <td class="field">
					<select name="captcha">
						<option value="1" <?php if($captcha == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
						<option value="2" <?php if($captcha == "2") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
					</select>	
				</td>
                <td class="help"><?php echo __('Choose yes if you want to use a captcha to protect yourself against spammers.'); ?></td>
            </tr>	
            <tr>
                <td class="label"><label for="rowspage"><?php echo __('Comments per page'); ?>: </label></td>
                <td class="field">
					<input type="text" class="textinput" value="<?php echo $rowspage; ?>" name="rowspage" />
				</td>
                <td class="help"><?php echo __('Sets the number of comments to be displayed per page in the backend.'); ?></td>
        	</tr>
            <tr>
                <td class="label"><label for="numlabel"><?php echo __('Enhance comments tab'); ?>: </label></td>
                <td class="field">
					<select name="numlabel">
						<option value="1" <?php if($numlabel == "1") echo 'selected ="";' ?>><?php echo __('Yes'); ?></option>
						<option value="0" <?php if($numlabel == "0") echo 'selected ="";' ?>><?php echo __('No'); ?></option>
					</select>
				</td>
                <td class="help"><?php echo __("Choose yes if you want to display the number of to-be-moderated &amp; total number of comment in the tab of the Comment plugin."); ?></td>
            </tr>
        </table>
    </fieldset>
    <br/>
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
