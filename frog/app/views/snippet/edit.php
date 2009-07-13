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
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */
?>
<h1><?php echo __(ucfirst($action).' snippet'); ?></h1>

<form action="<?php echo $action=='edit' ? get_url('snippet/edit/'.$snippet->id): get_url('snippet/add'); ; ?>" method="post">

  <div class="form-area">
  <h3><?php echo __('Name'); ?></h3><br />
  <div id="meta-pages" class="pages">
    <p class="title">
      <input class="textbox" id="snippet_name" maxlength="100" name="snippet[name]" size="255" type="text" value="<?php echo $snippet->name; ?>" />
    </p>
</div>
	<h3><?php echo __('Body'); ?></h3>
    <p class="content">
<div id="pages" class="pages">
<div class="page" style="">
      <p>
      <label for="snippet_filter_id"><?php echo __('Filter'); ?></label>
      <select id="snippet_filter_id" name="snippet[filter_id]" onchange="setTextAreaToolbar('snippet_content', this[this.selectedIndex].value)">
        <option value=""<?php if($snippet->filter_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
<?php foreach ($filters as $filter): ?>
        <option value="<?php echo $filter; ?>"<?php if($snippet->filter_id == $filter) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($filter); ?></option>
<?php endforeach; ?>
      </select>
    </p>
	  <textarea class="textarea" cols="40" id="snippet_content" name="snippet[content]" rows="20" style="width: 100%" 
                onkeydown="return allowTab(event, this);"
                onkeyup="return allowTab(event,this);"
                onkeypress="return allowTab(event,this);"><?php echo htmlentities($snippet->content, ENT_COMPAT, 'UTF-8'); ?></textarea>
    </p>
<?php if (isset($snippet->updated_on)): ?>
    <p style="clear: left"><small><?php echo __('Last updated by'); ?> <?php echo $snippet->updated_by_name; ?> <?php echo __('on'); ?> <?php echo date('D, j M Y', strtotime($snippet->updated_on)); ?></small></p>
<?php endif; ?>
  </div></div></div>
  <p class="buttons">
    <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
    <input class="button" name="continue" type="submit" accesskey="e" value="<?php echo __('Save and Continue Editing'); ?>" />
    <?php echo __('or'); ?> <a href="<?php echo get_url('snippet'); ?>"><?php echo __('Cancel'); ?></a>
  </p>
</form>

<script type="text/javascript">
// <![CDATA[
  setTextAreaToolbar('snippet_content', '<?php echo $snippet->filter_id; ?>');
  document.getElementById('snippet_name').focus();
// ]]>
</script>