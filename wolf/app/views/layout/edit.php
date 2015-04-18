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
 * @package Views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */
?>
<h1><?php echo __(ucfirst($action).' layout'); ?></h1>
<div id="layout">
    <form action="<?php echo $action=='edit' ? get_url('layout/edit/'. $layout->id): get_url('layout/add'); ; ?>" method="post">
        <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />
          <div class="form-area">
            <p class="title">
                <label for="layout_name"><?php echo __('Name'); ?></label>
                <input class="textbox" id="layout_name" maxlength="100" name="layout[name]" size="100" type="text" value="<?php echo $layout->name; ?>" />
            </p>
            <p class="content-type">
                <label for="layout_content_type"><?php echo __('Content-Type'); ?></label>
                <input class="textbox" id="layout_content_type" maxlength="40" name="layout[content_type]" size="40" type="text" value="<?php echo $layout->content_type; ?>" />
            </p>
            <p class="content">
                <textarea class="textarea" id="layout_content" name="layout[content]"><?php echo htmlentities($layout->content, ENT_COMPAT, 'UTF-8'); ?></textarea>
            </p>
        <?php if (isset($layout->updated_on)) { ?>
            <p class="updated-by"><small><?php echo __('Last updated by'); ?> <?php echo $layout->updated_by_name; ?> <?php echo __('on'); ?> <?php echo date('D, j M Y', strtotime($layout->updated_on)); ?></small></p>
        <?php } ?>
          </div>
          <p class="buttons">
            <button name="commit" type="submit" accesskey="s"><?php echo __('Save'); ?></button>
            <button name="continue" type="submit" accesskey="e"><?php echo __('Save and Continue Editing'); ?></button>
            <?php echo __('or'); ?> <a href="<?php echo get_url('layout'); ?>"><?php echo __('Cancel'); ?></a>
          </p>
    </form>
</div>

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
    
  document.getElementById('layout_name').focus();
// ]]>
</script>