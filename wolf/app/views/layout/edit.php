<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
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
 * @package wolf
 * @subpackage views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */
?>
<h1><?php echo __(ucfirst($action).' layout'); ?></h1>

<form action="<?php echo $action=='edit' ? get_url('layout/edit/'. $layout->id): get_url('layout/add'); ; ?>" method="post">
    <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />
  <div class="form-area">
    <p class="title">
      <label for="layout_name"><?php echo __('Name'); ?></label>
      <input class="textbox" id="layout_name" maxlength="100" name="layout[name]" size="100" type="text" value="<?php echo $layout->name; ?>" />
    </p>
    
    <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td><label for="layout_content_type"><?php echo __('Content-Type'); ?></label></td>
        <td class="field"><input class="textbox" id="layout_content_type" maxlength="40" name="layout[content_type]" size="40" type="text" value="<?php echo $layout->content_type; ?>" /></td>
      </tr>
    </table>
    
    <p class="content">
      <label for="layout_content"><?php echo __('Body'); ?></label>
      <textarea class="textarea" cols="40" id="layout_content" name="layout[content]" rows="20" style="width: 100%"><?php echo htmlentities($layout->content, ENT_COMPAT, 'UTF-8'); ?></textarea>
    </p>
<?php if (isset($layout->updated_on)) { ?>
    <p style="clear: left"><small><?php echo __('Last updated by'); ?> <?php echo $layout->updated_by_name; ?> <?php echo __('on'); ?> <?php echo date('D, j M Y', strtotime($layout->updated_on)); ?></small></p>
<?php } ?>
  </div>
  <p class="buttons">
    <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
    <input class="button" name="continue" type="submit" accesskey="e" value="<?php echo __('Save and Continue Editing'); ?>" />
    <?php echo __('or'); ?> <a href="<?php echo get_url('layout'); ?>"><?php echo __('Cancel'); ?></a>
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

    $j(document).ready(function() {
        // Prevent accidentally navigating away
        $j(':input').bind('change', function() { setConfirmUnload(true); });
        $j('form').submit(function() { setConfirmUnload(false); return true; });
    });
    
  document.getElementById('layout_name').focus();
// ]]>
</script>