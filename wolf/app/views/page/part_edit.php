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
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<div id="part-<?php echo $index; ?>-content" class="page">
	<div class="page" id="page-<?php echo $index; ?>">
	  <div class="part" id="part-<?php echo $index; ?>">
	    <input id="part_<?php echo ($index-1); ?>_name" name="part[<?php echo ($index-1); ?>][name]" type="hidden" value="<?php echo $page_part->name; ?>" />
	    <?php if (isset($page_part->id)): ?>
	    <input id="part_<?php echo ($index-1); ?>_id" name="part[<?php echo ($index-1); ?>][id]" type="hidden" value="<?php echo $page_part->id; ?>" />
	    <?php endif; ?>
	    <p>
	      <label for="part_<?php echo ($index-1); ?>_filter_id"><?php echo __('Filter'); ?></label>
	      <select id="part_<?php echo ($index-1); ?>_filter_id" class="filter-selector" name="part[<?php echo ($index-1); ?>][filter_id]">
	        <option value=""<?php if ($page_part->filter_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
	<?php foreach (Filter::findAll() as $filter): ?> 
	        <option value="<?php echo $filter; ?>"<?php if ($page_part->filter_id == $filter) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($filter); ?></option>
	<?php endforeach; ?> 
	      </select>
	    </p>
	    <div>
	    	<textarea class="textarea markitup<?php if($page_part->filter_id != "") { echo ' '.$page_part->filter_id; } ?>" id="part_<?php echo ($index-1); ?>_content" name="part[<?php echo ($index-1); ?>][content]" style="width: 100%" rows="20" cols="40"><?php echo htmlentities($page_part->content, ENT_COMPAT, 'UTF-8'); ?></textarea>
	    </div>
	  </div>
	</div>
</div>
<?php /*
<script type="text/javascript">
// <![CDATA[
    $(document).ready(function() {
 * 
 * THIS WAS MOVED INTO backend.php
 * 
        // Get the initial values and activate filter
        $('.filter-selector').each(function() {
            var $this = $(this);
            $this.data('oldValue', $this.val());
            
            if ($this.val() == '') {
                return true;
            }
            alert('ELEMENT IS: '+$this.attr('id'));
            var elemId = $this.attr('id').slice(0, -10);
            var elem = $('#'+elemId+'_content');
            $this.trigger('wolfSwitchFilterIn', [$this.val(), elem]);
        });
        
        $('.filter-selector').each(function(){
            var $this = $(this);
            
            $this.change(function() {
                var $this = $(this);
                var newFilter = $this.val();
                var oldFilter = $this.data('oldValue');
                $this.data('oldValue', newFilter);
                
                var elemId = $this.attr('id').slice(0, -10);
                var elem = $('#'+elemId+'_content');
                $('.filter-selector').trigger('wolfSwitchFilterOut', [oldFilter, elem]);
                $('.filter-selector').trigger('wolfSwitchFilterIn', [newFilter, elem]);
            });
        });
    });
// ]]>
</script> */ ?>