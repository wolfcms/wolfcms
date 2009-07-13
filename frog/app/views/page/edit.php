<?php
/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package frog
 * @subpackage views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Martijn van der Kleijn, 2008
 */

// TODO: clean up code/solution
$pagetmp = Flash::get('page');
$parttmp = Flash::get('page_parts');
$tagstmp = Flash::get('page_tag');
if ($pagetmp != null && !empty($pagetmp) && $parttmp != null && !empty($parttmp) && $tagstmp != null && !empty($tagstmp)) {
    $page = $pagetmp;
    $page_parts = $parttmp;
    $tags = $tagstmp;
}

if ($action == 'edit') { ?>
    <span style="float: right;"><a id="site-view-page" onclick="target='_blank'" onkeypress="target='_blank'" href="<?php echo URL_PUBLIC; echo (USE_MOD_REWRITE == false) ? '?' : ''; echo $page->getUri().URL_SUFFIX; ?>"><?php echo __('View this page'); ?></a></span>
<?php } ?>
<h1><?php echo __(ucfirst($action).' Page'); ?></h1>

<form action="<?php if ($action == 'add') echo get_url('page/add'); else echo  get_url('page/edit/'.$page->id); ?>" method="post">

  <input id="page_parent_id" name="page[parent_id]" type="hidden" value="<?php echo $page->parent_id; ?>" />
  <div class="form-area">
    <div id="tab-control-meta" class="tab_control">
        <div id="tabs-meta" class="tabs">
            <div id="tab-meta-toolbar" class="tab_toolbar">&nbsp;</div>
        </div>
        <div id="meta-pages" class="pages">
            <div id="div-title" class="title" title="<?php echo __('Page Title'); ?>">
              <input class="textbox" id="page_title" maxlength="255" name="page[title]" size="255" type="text" value="<?php echo $page->title; ?>" />
            </div>
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
              <?php if (isset($page->created_on)): ?>
                <tr>
                  <td class="label"><label for="page_created_on"><?php echo __('Created date'); ?></label></td>
                  <td class="field">
                    <input id="page_created_on" maxlength="10" name="page[created_on]" size="10" type="text" value="<?php echo substr($page->created_on, 0, 10); ?>" /> 
                    <img onclick="displayDatePicker('page[created_on]');" src="images/icon_cal.gif" alt="<?php echo __('Show Calendar'); ?>" /> 
                    <input id="page_created_on_time" maxlength="5" name="page[created_on_time]" size="5" type="text" value="<?php echo substr($page->created_on, 11); ?>" />
                <?php if (isset($page->published_on)): ?>
                    &nbsp; <label for="page_published_on"><?php echo __('Published date'); ?></label>
                    <input id="page_published_on" maxlength="10" name="page[published_on]" size="10" type="text" value="<?php echo substr($page->published_on, 0, 10); ?>" /> 
                    <img onclick="displayDatePicker('page[published_on]');" src="images/icon_cal.gif" alt="<?php echo __('Show Calendar'); ?>" />
                    <input id="page_published_on_time" maxlength="5" name="page[published_on_time]" size="5" type="text" value="<?php echo substr($page->published_on, 11); ?>" /> 
                <?php endif; ?>
                  </td>
                </tr>
              <?php endif; ?>
              </table>
            </div>
            <?php Observer::notify('view_page_edit_tabs', $page); ?>
        </div>
    </div>
    
    <script type="text/javascript">
      var tabControlMeta = new TabControl('tab-control-meta');
      $('meta-pages').childElements().each(function(item) {
        tabControlMeta.addTab('tab-'+item.id, item.title, item.id);
      });
      tabControlMeta.select(tabControlMeta.firstTab());
    </script>

    <div id="tab-control" class="tab_control">
      <div id="tabs" class="tabs">
        <div id="tab-toolbar" class="tab_toolbar">
          <a href="#" onclick="toggle_popup('add-part-popup', 'part-name-field'); return false;" title="<?php echo __('Add Tab'); ?>"><img src="images/plus.png" alt="plus icon" /></a>
          <a href="#" onclick="if (confirm('<?php echo __('Delete the current tab?'); ?>')) { tabControl.removeTab(tabControl.selected) }; return false;" title="<?php echo __('Remove Tab'); ?>"><img src="images/minus.png" alt="minus icon" /></a>
        </div>
      </div>
      <div id="pages" class="pages">
      <?php
      $index = 1;
      foreach ($page_parts as $page_part)
      {
          echo new View('page/part_edit', array('index' => $index, 'page_part' => $page_part));
          $index++; 
      }
      ?>
      </div>
    </div>
    
    <div class="row">
      <p><label for="page_layout_id"><?php echo __('Layout'); ?></label>
        <select id="page_layout_id" name="page[layout_id]">
          <option value="">&#8212; <?php echo __('inherit'); ?> &#8212;</option>
<?php foreach ($layouts as $layout): ?>
          <option value="<?php echo $layout->id; ?>"<?php echo $layout->id == $page->layout_id ? ' selected="selected"': ''; ?>><?php echo $layout->name; ?></option>
<?php endforeach; ?>
        </select>
      </p>

      <p><label for="page_behavior_id"><?php echo __('Page Type'); ?></label>
        <select id="page_behavior_id" name="page[behavior_id]">
          <option value=""<?php if ($page->behavior_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
<?php foreach ($behaviors as $behavior): ?>
          <option value="<?php echo $behavior; ?>"<?php if ($page->behavior_id == $behavior) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($behavior); ?></option>
<?php endforeach; ?>
        </select>
      </p>

<?php if ( ! isset($page->id) || $page->id != 1): ?>
      <p><label for="page_status_id"><?php echo __('Status'); ?></label>
        <select id="page_status_id" name="page[status_id]">
          <option value="<?php echo Page::STATUS_DRAFT; ?>"<?php echo $page->status_id == Page::STATUS_DRAFT ? ' selected="selected"': ''; ?>><?php echo __('Draft'); ?></option>
          <option value="<?php echo Page::STATUS_REVIEWED; ?>"<?php echo $page->status_id == Page::STATUS_REVIEWED ? ' selected="selected"': ''; ?>><?php echo __('Reviewed'); ?></option>
          <option value="<?php echo Page::STATUS_PUBLISHED; ?>"<?php echo $page->status_id == Page::STATUS_PUBLISHED ? ' selected="selected"': ''; ?>><?php echo __('Published'); ?></option>
          <option value="<?php echo Page::STATUS_HIDDEN; ?>"<?php echo $page->status_id == Page::STATUS_HIDDEN ? ' selected="selected"': ''; ?>><?php echo __('Hidden'); ?></option>
        </select>
      </p>
<?php endif; ?>

<?php Observer::notify('view_page_edit_plugins', $page); ?>

    </div>
    <p class="clear">&nbsp;</p>
    
<?php if (AuthUser::hasPermission('administrator') || AuthUser::hasPermission('developer')): ?>
    <p style="float: right">
        <label for="page_needs_login"><?php echo __('Login:'); ?></label>
        <select id="page_needs_login" name="page[needs_login]" title="<?php echo __('When enabled, users have to login before they can view the page.'); ?>">
          <option value="<?php echo Page::LOGIN_INHERIT; ?>"<?php echo $page->needs_login == Page::LOGIN_INHERIT ? ' selected="selected"': ''; ?>><?php echo __('&#8212; inherit &#8212;'); ?></option>
          <option value="<?php echo Page::LOGIN_NOT_REQUIRED; ?>"<?php echo $page->needs_login == Page::LOGIN_NOT_REQUIRED ? ' selected="selected"': ''; ?>><?php echo __('not required'); ?></option>
          <option value="<?php echo Page::LOGIN_REQUIRED; ?>"<?php echo $page->needs_login == Page::LOGIN_REQUIRED ? ' selected="selected"': ''; ?>><?php echo __('required'); ?></option>          
        </select>
        <input id="page_is_protected" name="page[is_protected]" class="checkbox" type="checkbox" value="1"<?php if ($page->is_protected) echo ' checked="checked"'; ?>/><label for="page_is_protected" title="<?php echo __('When enabled, only users who are an administor can edit the page.'); ?>"> <?php echo __('Protected'); ?> </label>
    </p>
<?php endif; ?>
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
<div id="popups">
  <div class="popup" id="add-part-popup" style="display: none;">
    <div id="busy" class="busy" style="display: none;"><img alt="Spinner" src="images/spinner.gif" /></div>
    <h3><?php echo __('Add Part'); ?></h3>
    <form action="<?php echo get_url('page/addPart'); ?>" method="post" onsubmit="if (valid_part_name()) { new Ajax.Updater('pages', '<?php echo get_url('page/addPart'); ?>', {asynchronous:true, evalScripts:true, insertion:Insertion.Bottom, onComplete:function(request){part_added()}, onLoading:function(request){part_loading()}, parameters:Form.serialize(this)}); }; return false;"> 
      <div>
        <input id="part-index-field" name="part[index]" type="hidden" value="<?php echo $index; ?>" />
        <input id="part-name-field" maxlength="100" name="part[name]" type="text" value="" /> 
        <input id="add-part-button" name="commit" type="submit" value="<?php echo __('Add'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('add-part-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>

<?php Observer::notify('view_page_edit_popup'); ?>

</div>

<script type="text/javascript">
    Field.activate('page_title');
</script>