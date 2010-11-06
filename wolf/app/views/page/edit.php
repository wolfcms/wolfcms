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
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Martijn van der Kleijn, 2008
 */

// @todo clean up code/solution
$pagetmp = Flash::get('page');
$parttmp = Flash::get('page_parts');
$tagstmp = Flash::get('page_tag');
if ($pagetmp != null && !empty($pagetmp) && $parttmp != null && !empty($parttmp) && $tagstmp != null && !empty($tagstmp)) {
    $page = $pagetmp;
    $page_parts = $parttmp;
    $tags = $tagstmp;
}

if ($action == 'edit') { ?>
    <span style="float: right;"><a id="site-view-page" onclick="target='_blank'" onkeypress="target='_blank'" href="<?php echo URL_PUBLIC; echo (USE_MOD_REWRITE == false) ? '?' : ''; echo $page->getUri(); echo ($page->getUri() != '') ? URL_SUFFIX : ''; ?>"><?php echo __('View this page'); ?></a></span>
<?php } ?>

<h1><?php echo __(ucfirst($action).' Page'); ?></h1>

<form id="page_edit_form" action="<?php if ($action == 'add') echo get_url('page/add'); else echo  get_url('page/edit/'.$page->id); ?>" method="post">

  <input id="page_parent_id" name="page[parent_id]" type="hidden" value="<?php echo $page->parent_id; ?>" />
  <div class="form-area">
    <div id="metainfo-tabs" class="content tabs">
        <ul class="tabNavigation">
            <li class="tab"><a href="#pagetitle"><?php echo __('Page Title'); ?></a></li>
            <li class="tab"><a href="#metadata"><?php echo __('Metadata'); ?></a></li>
            <li class="tab"><a href="#settings"><?php echo __('Settings'); ?></a></li>
        </ul>
    </div>
    <div id="metainfo-content" class="pages">
        <div id="pagetitle" class="page">
            <div id="div-title" class="title" title="<?php echo __('Page Title'); ?>">
            <input class="textbox" id="page_title" maxlength="255" name="page[title]" size="255" type="text" value="<?php echo $page->title; ?>" />
            </div>
        </div>
        <div id="metadata" class="page">
            <div id="div-metadata" title="<?php echo __('Metadata'); ?>">
              <table cellpadding="0" cellspacing="0" border="0">
                <?php if ($page->parent_id != 0) : ?>
                <tr>
                  <td class="label"><label for="page_slug"><?php echo __('Slug'); ?></label></td>
                  <td class="field"><input class="textbox" id="page_slug" maxlength="100" name="page[slug]" size="100" type="text" value="<?php echo $page->slug; ?>" /></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <td class="label"><label for="page_breadcrumb"><?php echo __('Breadcrumb'); ?></label></td>
                  <td class="field"><input class="textbox" id="page_breadcrumb" maxlength="160" name="page[breadcrumb]" size="160" type="text" value="<?php echo htmlentities($page->breadcrumb, ENT_COMPAT, 'UTF-8'); ?>" /></td>
                </tr>
                <tr>
                  <td class="label optional"><label for="page_keywords"><?php echo __('Keywords'); ?></label></td>
                  <td class="field"><input class="textbox" id="page_keywords" maxlength="255" name="page[keywords]" size="255" type="text" value="<?php echo $page->keywords; ?>" /></td>
                </tr>
                <tr>
                  <td class="label optional"><label for="page_description"><?php echo __('Description'); ?></label></td>
                  <td class="field"><textarea class="textarea" id="page_description" name="page[description]" rows="40" cols="3"><?php echo $page->description; ?></textarea></td>
                </tr>
                <tr>
                  <td class="label optional"><label for="page_tags"><?php echo __('Tags'); ?></label></td>
                  <td class="field"><input class="textbox" id="page_tags" maxlength="255" name="page_tag[tags]" size="255" type="text" value="<?php echo join(', ', $tags); ?>" /></td>
                </tr>
              </table>
            </div>
        </div>
        <div id="settings" class="page">
            <div id="div-settings" title="<?php echo __('Settings'); ?>">
              <table cellpadding="0" cellspacing="0" border="0">
                <?php if ($page->parent_id != 0) : ?>
                <tr>
                  <td class="label"><label for="page_id"><?php echo __('Page id'); ?></label></td>
                  <td class="field"><input class="textbox" id="page_id" maxlength="100" name="unused" size="100" type="text" value="<?php echo $page->id; ?>" disabled="disabled"/></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <td class="label"><label for="page_layout_id"><?php echo __('Layout'); ?></label></td>
                  <td class="field">
                      <select id="page_layout_id" name="page[layout_id]">
                        <option value="0">&#8212; <?php echo __('inherit'); ?> &#8212;</option>
                      <?php foreach ($layouts as $layout): ?>
                        <option value="<?php echo $layout->id; ?>"<?php echo $layout->id == $page->layout_id ? ' selected="selected"': ''; ?>><?php echo $layout->name; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td class="label"><label for="page_behavior_id"><?php echo __('Page Type'); ?></label></td>
                  <td class="field">
                    <select id="page_behavior_id" name="page[behavior_id]">
                        <option value=""<?php if ($page->behavior_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
                    <?php foreach ($behaviors as $behavior): ?>
                        <option value="<?php echo $behavior; ?>"<?php if ($page->behavior_id == $behavior) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($behavior); ?></option>
                    <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
              <?php if (isset($page->created_on)): ?>
                <tr>
                  <td class="label"><label for="page_created_on"><?php echo __('Created date'); ?></label></td>
                  <td class="field">
                    <input id="page_created_on" maxlength="10" name="page[created_on]" size="10" type="text" value="<?php echo substr($page->created_on, 0, 10); ?>" />
                    <img onclick="displayDatePicker('page[created_on]');" src="<?php echo URI_PUBLIC;?>wolf/admin/images/icon_cal.gif" alt="<?php echo __('Show Calendar'); ?>" />
                    <input id="page_created_on_time" maxlength="5" name="page[created_on_time]" size="5" type="text" value="<?php echo substr($page->created_on, 11); ?>" />
                <?php if (isset($page->published_on)): ?>
                    &nbsp; <label for="page_published_on"><?php echo __('Published date'); ?></label>
                    <input id="page_published_on" maxlength="10" name="page[published_on]" size="10" type="text" value="<?php echo substr($page->published_on, 0, 10); ?>" />
                    <img onclick="displayDatePicker('page[published_on]');" src="<?php echo URI_PUBLIC;?>wolf/admin/images/icon_cal.gif" alt="<?php echo __('Show Calendar'); ?>" />
                    <input id="page_published_on_time" maxlength="5" name="page[published_on_time]" size="5" type="text" value="<?php echo substr($page->published_on, 11); ?>" />
                <?php endif; ?>
                  </td>
                </tr>
                <?php if (isset($page->published_on)): ?>
                <tr>
                  <td class="label">
                    <label for="page_valid_until"><?php echo __('Valid until date'); ?></label>
                  </td>
                  <td class="field">
                    <input id="page_valid_until" maxlength="10" name="page[valid_until]" size="10" type="text" value="<?php echo substr($page->valid_until, 0, 10); ?>" />
                    <img onclick="displayDatePicker('page[valid_until]');" src="<?php echo URI_PUBLIC;?>wolf/admin/images/icon_cal.gif" alt="<?php echo __('Show Calendar'); ?>" />
                    <input id="page_valid_until_time" maxlength="5" name="page[valid_until_time]" size="5" type="text" value="<?php echo substr($page->valid_until, 11); ?>" />
                  </td>
                </tr>
                <?php endif; ?>
              <?php endif; ?>
              <?php if (AuthUser::hasPermission('page_edit')): ?>
                <tr>
                  <td class="label"><label for="page_needs_login"><?php echo __('Login:'); ?></label></td>
                  <td class="field">
                    <select id="page_needs_login" name="page[needs_login]" title="<?php echo __('When enabled, users have to login before they can view the page.'); ?>">
                        <option value="<?php echo Page::LOGIN_INHERIT; ?>"<?php echo $page->needs_login == Page::LOGIN_INHERIT ? ' selected="selected"': ''; ?>><?php echo __('&#8212; inherit &#8212;'); ?></option>
                        <option value="<?php echo Page::LOGIN_NOT_REQUIRED; ?>"<?php echo $page->needs_login == Page::LOGIN_NOT_REQUIRED ? ' selected="selected"': ''; ?>><?php echo __('not required'); ?></option>
                        <option value="<?php echo Page::LOGIN_REQUIRED; ?>"<?php echo $page->needs_login == Page::LOGIN_REQUIRED ? ' selected="selected"': ''; ?>><?php echo __('required'); ?></option>
                    </select>
                    <input id="page_is_protected" name="page[is_protected]" class="checkbox" type="checkbox" value="1"<?php if ($page->is_protected) echo ' checked="checked"'; ?>/><label for="page_is_protected" title="<?php echo __('When enabled, only users who are an administrator can edit the page.'); ?>"> <?php echo __('Protected'); ?> </label>
                  </td>
                </tr>
              <?php endif; ?>

              </table>
            </div>
        </div>
        <?php Observer::notify('view_page_edit_tabs', $page); ?>
    </div>

    <div id="part-tabs" class="content tabs">
        <div id="tab-toolbar" class="tab_toolbar">
          <a href="#" id="add-part" nclick="toggle_popup('add-part-popup', 'part-name-field'); return false;" title="<?php echo __('Add Tab'); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/admin/images/plus.png" alt="plus icon" /></a>
          <a href="#" id="delete-part" nclick="if (confirm('<?php echo __('Delete the current tab?'); ?>')) { tabControl.removeTab(tabControl.selected) }; return false;" title="<?php echo __('Remove Tab'); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/admin/images/minus.png" alt="minus icon" /></a>
        </div>
        <ul class="tabNavigation">
            <?php foreach ($page_parts as $key => $page_part) { ?>
            <li id="part-<?php echo $key+1; ?>-tab" class="tab"><a href="#part-<?php echo $key+1; ?>-content"><?php echo $page_part->name; ?></a></li>
            <?php } ?>
        </ul>
    </div>
    <div id="part-content" class="pages">
      <?php
      $index = 1;
      foreach ($page_parts as $page_part) {
          echo new View('page/part_edit', array('index' => $index, 'page_part' => $page_part));
          $index++;
      }
      ?>
    </div>

    <?php Observer::notify('view_page_after_edit_tabs', $page); ?>

    <div class="row">
<?php if ( ! isset($page->id) || $page->id != 1): ?>
      <p><label for="page_status_id"><?php echo __('Status'); ?></label>
        <select id="page_status_id" name="page[status_id]">
          <option value="<?php echo Page::STATUS_DRAFT; ?>"<?php echo $page->status_id == Page::STATUS_DRAFT ? ' selected="selected"': ''; ?>><?php echo __('Draft'); ?></option>
          <option value="<?php echo Page::STATUS_PREVIEW; ?>"<?php echo $page->status_id == Page::STATUS_PREVIEW ? ' selected="selected"': ''; ?>><?php echo __('Preview'); ?></option>
          <option value="<?php echo Page::STATUS_PUBLISHED; ?>"<?php echo $page->status_id == Page::STATUS_PUBLISHED ? ' selected="selected"': ''; ?>><?php echo __('Published'); ?></option>
          <option value="<?php echo Page::STATUS_HIDDEN; ?>"<?php echo $page->status_id == Page::STATUS_HIDDEN ? ' selected="selected"': ''; ?>><?php echo __('Hidden'); ?></option>
          <option value="<?php echo Page::STATUS_ARCHIVED; ?>"<?php echo $page->status_id == Page::STATUS_ARCHIVED ? ' selected="selected"': ''; ?>><?php echo __('Archived'); ?></option>
        </select>
      </p>
<?php endif; ?>
<?php Observer::notify('view_page_edit_plugins', $page); ?>
    </div>
    
    <p><small>
<?php if (isset($page->updated_on)): ?>
    <?php echo __('Last updated by'); ?> <?php echo $page->updated_by_name; ?> <?php echo __('on'); ?> <?php echo date('D, j M Y', strtotime($page->updated_on)); ?>
<?php endif; ?>
    &nbsp;
    </small></p>

  </div>
  <p class="buttons">
    <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save and Close'); ?>" />
    <input class="button" name="continue" type="submit" accesskey="e" value="<?php echo __('Save and Continue Editing'); ?>" />
    <?php echo __('or'); ?> <a href="<?php echo get_url('page'); ?>"><?php echo __('Cancel'); ?></a>
  </p>

</form>

<div id="boxes">
	<!-- #Demo dialog -->
	<div id="dialog" class="window">
		<div class="titlebar">
            Demo dialog
            <a href="#" class="close">[x]</a>
        </div>
        <div class="content">
            <p>This is just a demo.</p>
        </div>
	</div>

	<!-- Add part dialog -->
	<div id="add-part-dialog" class="window">
		<div class="titlebar">
            <div id="busy" class="busy" style="display: none;"><img alt="Spinner" src="<?php echo URI_PUBLIC;?>wolf/admin/images/spinner.gif" /></div>
            <?php echo __('Add Part'); ?>
            <a href="" class="close">[x]</a>
        </div>
        <div class="content">
            <form action="<?php //echo get_url('page/addPart'); ?>" method="post">
            <div>
                <input id="part-index-field" name="part[index]" type="hidden" value="<?php echo $index; ?>" />
                <input id="part-name-field" maxlength="100" name="part[name]" type="text" value="" />
                <input id="add-part-button" name="commit" type="submit" value="<?php echo __('Add'); ?>" />
            </div>
            </form>
        </div>
	</div>
<?php Observer::notify('view_page_edit_popup', $page); ?>

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

    jQuery.fn.spinnerSetup = function spinnerSetup() {
        this.each(function() {
            var pid = $j(this).attr('id')
            $j('#'+pid).hide()  // hide it initially
            .ajaxStop(function() {
                $j('#'+pid).hide();
            });
        });

        return this;
    };

    $j(document).ready(function() {
        $j(".busy").spinnerSetup();

        // Store PHP value for later reference
        var partIndex = <?php echo $index; ?>;

        // Prevent accidentally navigating away
        $j('form#page_edit_form :input').bind('change', function() { setConfirmUnload(true); });
        $j('form#page_edit_form').submit(function() { setConfirmUnload(false); return true; });

        // Do the metainfo tab thing
        $j('div#metainfo-tabs ul.tabNavigation a').live('click', function() {
            $j('div#metainfo-content > div.page').hide().filter(this.hash).show();
            $j('div#metainfo-tabs ul.tabNavigation a').removeClass('here');
            $j(this).addClass('here');
            return false;
        }).filter(':first').click();

        // Do the parts tab thing
        $j('div#part-tabs ul.tabNavigation a').live('click', function() {
            $j('div#part-content > div.page').hide().filter(this.hash).show();
            $j('div#part-tabs ul.tabNavigation a').removeClass('here');
            $j(this).addClass('here');
            return false;
        }).filter(':first').click();

        // Do the add part button thing
        $j('#add-part').click(function() {

            // START show popup
            var id = 'div#boxes div#add-part-dialog';

            //Get the screen height and width
            var maskHeight = $j(document).height();
            var maskWidth = $j(window).width();

            //Set height and width to mask to fill up the whole screen
            $j('#mask').css({'width':maskWidth,'height':maskHeight,'top':0,'left':0});

            //transition effect
            $j('#mask').show();
            $j('#mask').fadeTo("slow",0.5);

            //Get the window height and width
            var winH = $j(window).height();
            var winW = $j(window).width();

            //Set the popup window to center
            $j(id).css('top',  winH/2-$j(id).height()/2);
            $j(id).css('left', winW/2-$j(id).width()/2);

            //transition effect
            $j(id).fadeIn(500); //2000

            $j(id+" :input:visible:enabled:first").focus();
            // END show popup
        });

        // Do the submit add part window thing
        $j('div#add-part-dialog div.content form').submit(function(e) {
            e.preventDefault();

            if (valid_part_name($j('div#add-part-dialog div.content form input#part-name-field').val())) {
                $j('div#part-tabs ul.tabNavigation').append('<li id="part-'+partIndex+'-tab" class="tab">\n\
                                                             <a href="#part-'+partIndex+'-content">'+$j('div#add-part-dialog div.content form input#part-name-field').val()+'</a></li>');

                $j('div#part-tabs ul.tabNavigation li#part-'+partIndex+'-tab a').click();

                $j('#busy').show();

                $j.post('<?php echo get_url('page/addPart'); ?>',
                        $j('div#add-part-dialog div.content form').serialize(),
                        function(data) {
                                $j('div#part-content').append(data);
                                $j('#busy').hide();
                            });

                partIndex++;

                // Make sure users save changes
                setConfirmUnload(true);
           }

           $j('#mask, .window').hide();

           return false;
        });

        // Do the delete part button thing
        $j('#delete-part').click(function() {
            // Delete the tab
            var partRegEx = /part-(\d+)-tab/i;
            var myRegEx = new RegExp(partRegEx);
            var matched = myRegEx.exec($j('div#part-tabs ul.tabNavigation li.tab a.here').parent().attr('id'));
            var removePart = matched[1];

            $j('div#part-tabs ul.tabNavigation li.tab a.here').remove();
            $j('div#part-tabs ul.tabNavigation a').filter(':first').click();

            // Delete the content section
            $j('div#part-'+removePart+'-content').remove();

            // Make sure users save changes
            setConfirmUnload(true);
        });


        // Make all modal dialogs draggable
        $j("#boxes .window").draggable({
            addClasses: false,
            containment: 'window',
            scroll: false,
            handle: '.titlebar'
        })

        //if close button is clicked
        $j('#boxes .window .close').click(function (e) {
            //Cancel the link behavior
            e.preventDefault();
            $j('#mask, .window').hide();
        });

    });
    
    Field.activate('page_title');
// ]]>
</script>