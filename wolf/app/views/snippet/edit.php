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
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @copyright Martijn van der Kleijn, 2009-2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 *
 * @version $Id$
 */
?>

<h1><?php echo __(ucfirst($action).' snippet'); ?></h1>

<div id="snippet">
    <form action="<?php echo $action=='edit' ? get_url('snippet/edit/'.$snippet->id): get_url('snippet/add'); ; ?>" method="post">
        <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />
        <div class="form-area">
            <p class="title">
                <label for="name"><?php echo __('Name'); ?></label>
                <input id="snippet_name" maxlength="100" name="snippet[name]" type="text" value="<?php echo $snippet->name; ?>" />
            </p>

            <p class="filter">
                <label for="snippet_filter_id"><?php echo __('Filter'); ?>
                    <select id="snippet_filter_id" class="filter-selector" name="snippet[filter_id]">
                        <option value=""<?php if($snippet->filter_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
                        <?php foreach ($filters as $filter): ?>
                        <option value="<?php echo $filter; ?>"<?php if($snippet->filter_id == $filter) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($filter); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>

            <p class="content">
                <textarea class="textarea" id="snippet_content" name="snippet[content]"><?php echo htmlentities($snippet->content, ENT_COMPAT, 'UTF-8'); ?></textarea>
            </p>

            <?php if (isset($snippet->updated_on)): ?>
                <p class="updated-by"><small><?php echo __('Last updated by'); ?> <?php echo $snippet->updated_by_name; ?> <?php echo __('on'); ?> <?php echo date('D, j M Y', strtotime($snippet->updated_on)); ?></small></p>
            <?php endif; ?>

        </div>
        <p class="buttons">
            <?php if (($action=='edit' && AuthUser::hasPermission('snippet_edit')) || ($action=='add' && AuthUser::hasPermission('snippet_add'))): ?>
                <button name="commit" type="submit" accesskey="s"><?php echo __('Save'); ?></button>
                <button name="continue" type="submit" accesskey="e"><?php echo __('Save and Continue Editing'); ?></button>
                <?php echo __('or'); ?> 
            <?php else: ?>
                <?php echo ($action=='add') ? __('You do not have permission to add snippets!') : __('You do not have permission to edit snippets!'); ?> 
            <?php endif;?>
            <a href="<?php echo get_url('snippet'); ?>"><?php echo __('Cancel'); ?></a>
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
    
  document.getElementById('snippet_name').focus();
// ]]>
</script>