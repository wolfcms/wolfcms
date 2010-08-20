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
<h1><?php echo __('Pages'); ?></h1>

<div id="site-map-def">
    <div class="page"><?php echo __('Page'); ?> (<a href="#" id="toggle_reorder" nclick="toggle_reorder = !toggle_reorder; toggle_copy = false; $$('.handle_reorder').each(function(e) { e.style.display = toggle_reorder ? 'inline': 'none'; }); $$('.handle_copy').each(function(e) { e.style.display = toggle_copy ? 'inline': 'none'; }); return false;"><?php echo __('reorder'); ?></a> <?php echo __('or'); ?> <a id="toggle_copy" href="#" onclick="toggle_copy = !toggle_copy; toggle_reorder = false; $$('.handle_copy').each(function(e) { e.style.display = toggle_copy ? 'inline': 'none'; }); $$('.handle_reorder').each(function(e) { e.style.display = toggle_reorder ? 'inline': 'none'; }); return false;"><?php echo __('copy'); ?></a>)</div>
    <div class="status"><?php echo __('Status'); ?></div>
    <div class="view"><?php echo __('View'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>

<ul id="site-map-root">
    <li id="page-0" class="node level-0">
      <div class="page" style="padding-left: 4px">
        <span class="w1">
<?php if ($root->is_protected && ! AuthUser::hasPermission('administrator') && ! AuthUser::hasPermission('developer')): ?>
          <img align="middle" class="icon" src="<?php echo URI_PUBLIC;?>wolf/admin/images/page.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span>
<?php else: ?>
          <a href="<?php echo get_url('page/edit/1'); ?>" title="/"><img align="middle" class="icon" src="<?php echo URI_PUBLIC;?>wolf/admin/images/page.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span></a>
<?php endif; ?>
        </span>
      </div>
      <div class="status published-status"><?php echo __('Published'); ?></div>
      <div class="view-page"><a href="<?php echo URL_PUBLIC; ?>" target="_blank"><img src="<?php echo URI_PUBLIC;?>wolf/admin/images/magnify.png" align="middle" alt="<?php echo __('View Page'); ?>" title="<?php echo __('View Page'); ?>" /></a></div>
      <div class="modify">
          <a href="<?php echo get_url('page/add/1'); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/admin/images/plus.png" align="middle" title="<?php echo __('Add child'); ?>" alt="<?php echo __('Add child'); ?>" /></a>&nbsp;
          <img class="remove" src="<?php echo URI_PUBLIC;?>wolf/admin/images/icon-remove-disabled.gif" align="middle" alt="<?php echo __('remove icon disabled'); ?>" title="<?php echo __('Remove unavailable'); ?>"/>
      </div>

<?php echo $content_children; ?>

    </li>
</ul>


<style type="text/css">

    ul {
        /*
        list-style: none inside;
        margin: 0;
        padding: 0;
        margin-top: 0.5em;
        order: 1px solid grey;
        min-height: 10px;
        height: auto !important;
        height: 30px;
        */
    }

    .child {
        min-height: 10px;
        height: auto !important;
        height: 30px;
    }

    .child li {
/*	    padding: 0;
	    padding-left: 0.5em;
	    argin: 1px;
	    margin: 0;
	    margin-top: 0.5em;
        margin-left: 0.5em;*/
        padding-left: 0.5em;
        margin-left: 0.5em;
	    border-left: 10px solid grey;
	}

	.i-sortable { display: block; background-color: #EDFE86; }
    .i-sortable li { display: block; background-color: #fff; }

    .placeholder {
        height: 5px;
        background: #f00;
    }


</style>

<script type="text/javascript">
 $j(document).ready(function(){

    $j('#site-map li').each(function(){
		if($j('ul',this).length) return;
		var pid = $j(this).attr('id').split('_')[1];
		$j('<ul class="sortable child" id="pages_'+pid+'"></ul>').appendTo(this);
	});

    $j(".sortable").sortable({
            'axis': 'y',
            'disabled':false,
			'connectWith':['.sortable'],
			'tolerance':'intersect',
//			'containment':'#pages_0',
			'placeholder':'placeholder',
			'opacity': 0.75,
			'revert': true,
			'cursor':'crosshair',
			'appendTo':'ul',
			'distance':'15',
            stop: function(event, ui) {
                var parentId = ui.item.parent().attr('id').split('_')[1];
                var order = $j(ui.item.parent()).sortable('serialize', {key: 'pages[]'});
                if (parentId == null) parentId = 1;
                $j.post('<?php echo get_url('page/reorder/'); ?>'+parentId, {data : order});
            }
		})
		.disableSelection();

    $j("#toggle_reorder").click(function() {

        $j(".child").each(function(){
            if ($j(this).hasClass("reorderable"))
                $j(this).removeClass("reorderable");
            else
                $j(this).addClass("reorderable");
        });

        var disabled = $j( ".sortable" ).sortable( "option", "disabled" );
       // if (disabled == false)
            //$j( ".sortable" ).sortable( "option", "disabled", true );
        //else
            //$j( ".sortable" ).sortable( "option", "disabled", false );
    });

    $j("img.expander").click( function(){
        //alert('TEST-'+$j(this).parent().parent().parent().attr('id'));
        var parent = $j(this).parent().parent().parent();
        var parentId = parent.attr('id').split('_')[1];
        $j.get("<?php echo get_url('page/children/'); ?>"+parentId+'/'+'1', function(data) {
            $j('#pages_'+parentId).append(data);
        });

        //$(obj).attr('src', '<?php echo URI_PUBLIC; ?>wolf/admin/images/collapse.png');
    });

    //$('ul:empty').remove();

/* $('.child').expandCollapse({ startHidden : true }); */

/*
$('.child').expandCollapse({
    updateText        : true,
    updateClass       : false,
    startHidden       : true,
    triggerElement    : $('.trigger'),
    expandDuration    : "fast",
    collapseDuration  : "slow"
});*/


});
</script>