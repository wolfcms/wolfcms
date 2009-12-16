<?php
/**
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

<!--[if IE]>

    		<script>
        		// allow IE to recognize HTMl5 elements
        		document.createElement('section');
        		document.createElement('article');
        		document.createElement('aside');
        		document.createElement('footer');
        		document.createElement('header');
        		document.createElement('nav');
        		document.createElement('time');

    		</script>
    		<![endif]-->



<div class="page" id="page-<?php echo $index; ?>">
    <div class="part" id="part-<?php echo $index; ?>">
        <input id="part_<?php echo ($index-1); ?>_name" name="part[<?php echo ($index-1); ?>][name]" type="hidden" value="<?php echo $page_part->name; ?>" />
      <?php if (isset($page_part->id)): ?>
        <input id="part_<?php echo ($index-1); ?>_id" name="part[<?php echo ($index-1); ?>][id]" type="hidden" value="<?php echo $page_part->id; ?>" />
      <?php endif; ?>
        <p>
            <label for="part_<?php echo ($index-1); ?>_filter_id"><?php echo __('Filter'); ?></label>
            <select id="part_<?php echo ($index-1); ?>_filter_id" name="part[<?php echo ($index-1); ?>][filter_id]" onchange="setTextAreaToolbar('part_<?php echo ($index-1); ?>_content', this[this.selectedIndex].value)">
                <option value=""<?php if ($page_part->filter_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
              <?php foreach (Filter::findAll() as $filter): ?>
                <option value="<?php echo $filter; ?>"<?php if ($page_part->filter_id == $filter) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($filter); ?></option>
              <?php endforeach; ?>
            </select>
        </p>
        <div>
            <!--div class="markdown_toolbar" id="part_<?php echo ($index-1); ?>_content_toolbar"></div-->
            <textarea class="textarea" id="part_<?php echo ($index-1); ?>_content" name="part[<?php echo ($index-1); ?>][content]" rows="20" cols="40"
                   nkeydown="return allowTab(event, this);"
                   nkeyup="return allowTab(event,this);"
                   nkeypress="return allowTab(event,this);"><?php echo htmlentities($page_part->content, ENT_COMPAT, 'UTF-8'); ?></textarea>
        </div>
    </div>
</div>
<script type="text/javascript" charset="utf-8">

    $("select[id^='part_'][id$='_filter_id']").bind("change", function(e) {
        var filter = $(this).val();
        filter = $.string(filter).dasherize().str;
        filter = '-'+filter;
        filter = $.string(filter).camelize().str;

        $("ul.filter_toolbar").remove();

        var textarea = $("textarea[id^='part_'][id$='_content']");
        var textarea = $("textarea[id^='part_'][id$='_content']").TextArea(textarea, {});

        if (filter != "") {
            var toolbar = $.Toolbar(textarea, {
                className: "filter_toolbar"
            });
        }

        func = eval("setup" + filter + "Toolbar");
        func(toolbar, textarea);
    }
);

</script>