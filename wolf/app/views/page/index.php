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
<h1><?php echo __('Pages'); ?></h1>

<div id="site-map-def">
    <div class="page"><?php echo __('Page'); ?> (<a href="#" id="toggle_reorder"><?php echo __('reorder'); ?></a>)</div>
    <div class="page-layout"><?php echo __('Layout'); ?></div>
    <div class="status"><?php echo __('Status'); ?></div>
    <div class="view"><?php echo __('View'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>

<ul id="site-map-root">
    <li id="page-0" class="node level-0">
      <div class="page" style="padding-left: 4px">
        <span class="w1">
<?php if (!AuthUser::hasPermission('page_edit') || (!AuthUser::hasPermission('admin_edit') && $root->is_protected)): ?>
          <img align="middle" class="icon" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/page.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span>
<?php else: ?>
          <a href="<?php echo get_url('page/edit/1'); ?>" title="/"><img align="middle" class="icon" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/page.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span></a>
<?php endif; ?>
        </span>
      </div>
      <div class="page-layout"><?php echo Layout::findById($root->layout_id)->name; ?></div>
      <div class="status published-status"><?php echo __('Published'); ?></div>
      <div class="view-page"><a href="<?php echo URL_PUBLIC; ?>" target="_blank"><img src="<?php echo PATH_PUBLIC;?>wolf/admin/images/magnify.png" align="middle" alt="<?php echo __('View Page'); ?>" title="<?php echo __('View Page'); ?>" /></a></div>
      <div class="modify">
          <a href="<?php echo get_url('page/add/1'); ?>"><img src="<?php echo PATH_PUBLIC;?>wolf/admin/images/plus.png" align="middle" title="<?php echo __('Add child'); ?>" alt="<?php echo __('Add child'); ?>" /></a>&nbsp;
          <img class="remove" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/icon-remove-disabled.gif" align="middle" alt="<?php echo __('remove icon disabled'); ?>" title="<?php echo __('Remove unavailable'); ?>"/>&nbsp;
      	  <img src="<?php echo PATH_PUBLIC;?>wolf/admin/images/copy-disabled.png" align="middle" title="<?php echo __('Copy Page Disabled'); ?>" alt="<?php echo __('Copy Page Disabled'); ?>" />
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
        height: 2.4em;
        line-height: 1.2em;
        border: 1px solid #fcefa1;
        background-color: #fbf9ee;
        color: #363636;
        /*height: 5px;
        background: #f00;*/
    }


</style>

<script type="text/javascript">
    //jQuery(function() {
        jQuery.fn.spinnerSetup = function spinnerSetup() {
            this.each(function() {
                var pid = $(this).attr('id')
                $('#'+pid).hide()  // hide it initially
                .ajaxStop(function() {
                    $('#'+pid).hide();
                });
            });

            return this;
        };

        jQuery.fn.sitemapSetup = function sitemapSetup() {
            this.each(function () {
            	if($('ul',this).length) return;
                var pid = $(this).attr('id').split('_')[1];
            });

            return this;
        };
        
        jQuery.fn.expandableSetup = function expandableSetup() {
            $(this).live('click', function() {
                if ($(this).hasClass("expanded")) {
                    $(this).removeClass("expanded");
                    $(this).attr('src', '<?php echo PATH_PUBLIC; ?>wolf/admin/images/expand.png');

                    var parent = $(this).parents("li.node:first")
                    var parentId = parent.attr('id').split('_')[1];

                    $('#page_'+parentId).removeClass('children-visible').addClass('children-hidden').children('ul').hide();
                }
                else {
                    $(this).addClass("expanded");
                    $(this).attr('src', '<?php echo PATH_PUBLIC; ?>wolf/admin/images/collapse.png');
                    var parent = $(this).parents("li.node:first");
                    var parentId = parent.attr('id').split('_')[1];
                    $('#page_'+parentId).removeClass('children-hidden').addClass('children-visible');

                    if ($('#page_'+parentId).children('ul').length == 0) {
                        $('#busy-'+parentId).show();
                        $.get("<?php echo get_url('page/children/'); ?>"+parentId+'/'+'1', function(data) {                        
                            $('#page_'+parentId).append(data);
                            $('#site-map li').sitemapSetup();
                            $('.busy').spinnerSetup();
                        });
                    }
                    else {
                        $('#page_'+parentId).children('ul').show();
                    }
                }
				// update parents with children list expanded
				(function persistExpanded() {
					var expanded_rows = [];
					$('ul#site-map img.expanded').parents('li').not('#page-0').each(function() {
						expanded_rows.push( $(this).attr('id').split('_')[1] );
					});
					var rows = expanded_rows.reverse().toString();
					if(rows===''){
						rows += ';expires=Sat, 25 Dec 2010 06:07:00 UTC';
					}
					document.cookie = 'expanded_rows=' + rows + ';'
				})();
            });
        };
        
        jQuery.fn.sortableSetup = function sortableSetup() { 
			$('ul#site-map').nestedSortable({
				disableNesting: 'no-nest',
				forcePlaceholderSize: true,
				handle: 'div',
				items: 'li',
				opacity: .6,
				placeholder: 'placeholder',
				tabSize: 25,
				tolerance: 'pointer',
				toleranceElement: '> div.content-children',
				listType: 'ul',
				helper: 'clone',
				beforeStop: function(event, ui) {
					// quick checks incase they have taken it out of the sitemap tree
					if(ui.item.parents("#page-0").is('li') === false)
					{	
						$("ul#site-map").nestedSortable('cancel');
					}
				},
                stop: function(event, ui) {                    
                	var order = $("ul#site-map").nestedSortable('serialize');
                	
 					$.ajax({
						type: 'post',
						url: '<?php echo get_url('page/reorder'); ?>',
						data: order,
						cache: false
					});  
								             	
					// check where we have put the row so we can change styles if needbe
					var parent = ui.item.parent().parents('li.node:first');					
										
					if(parent.hasClass('level-0'))
					{
						// put back as homepage child
						var childClass = '';
						if(ui.item.hasClass('no-children'))
						{
							childClass = 'no-children';
						} else if(ui.item.hasClass('children-visible'))
						{
							childClass = 'children-visible';
						} else if(ui.item.hasClass('children-hidden'))
						{
							childClass = 'children-hidden';
						}				
						ui.item.removeClass();
						ui.item.addClass('node level-1 '+childClass);
					} else if(parent.find('img.expander').hasClass('expanded') == false)
					{
						// put into a row that has children but is closed
						ui.item.parent().hide().remove();
						
						// todo: improve
						// dirty fix for reloading tree
						window.location.reload(true);
						
					} else if(parent.find('img.expander').hasClass('expanded') == true)
					{
						// put into a row that has expanded children
						var siblingClass = ui.item.siblings('li.node').attr('class');
						var levelClass = siblingClass.split(' ');
						var childClass = '';
						if(ui.item.hasClass('no-children'))
						{
							childClass = 'no-children';
						} else if(ui.item.hasClass('children-visible'))
						{
							childClass = 'children-visible';
						} else if(ui.item.hasClass('children-hidden'))
						{
							childClass = 'children-hidden';
						}
						ui.item.removeClass();
						ui.item.addClass('node '+levelClass[1]+' '+childClass);	
					}
                }
			});
            return this;
        };
        
        jQuery.fn.copyableSetup = function() { 
        
			$(this).live('click', function() {			
				var id = $(this).attr('id').split('-');
				
				$.ajax({
					type: 'post',
					url: '<?php echo get_url('page/copy'); ?>',
					data: "&originalid="+id[1],
					cache: false,
					success: function(data) {
					
						data = data.split('||');
						var newid = parseInt(data[0]);
						
						// setup the new row
						var newobj = $("#page_"+id[1]).clone().css('display', 'none');					
						
						newobj.attr('id', 'page_'+newid); // set the main li id
						newobj.find('.edit-link').attr({ // set the edit link
							'href' : data[1],
							'title' : newid+' | '+data[3]
						});	
						newobj.find('.title').html(data[2]); // set the page title
						newobj.find('.busy').attr('id', 'busy-'+newid); // set the spinner id
						newobj.find('.view-link').attr('href', data[4]); // set the view page link
						newobj.find('.add-child-link').attr('href', data[5]); // set the add child link
						newobj.find('.remove').attr('href', data[6]); // set the delete link
						newobj.find('.remove').attr('onclick', '').unbind('click'); //remove old confirm dialog for delete link (needs both for IE/FF/Chrome)
						newobj.find('.remove').click(function(){return confirm('Are you sure you want to delete '+data[2]+' and its underlying pages?');}); //set the onclick dialog box for delete link
						newobj.find('.copy-page').attr('id', 'copy-'+newid); // set the copy id						
						
						$("#page_"+id[1]).after(newobj); // add row to dom and slide down
						newobj.slideDown();
					}						
				});
			});
            return this;
        };        
         
        
$(document).ready(function(){
    $('#site-map li').sitemapSetup();
    $("img.expander").expandableSetup(); 
    $(".busy").spinnerSetup();
    $(".copy-page").copyableSetup();
    $('ul#site-map').sortableSetup();
    $('ul#site-map').nestedSortable('disable');

    $('#toggle_reorder').toggle(
            function(){
    			$('ul#site-map').nestedSortable('enable');  
    			$('img.handle_reorder').show();
                $('#toggle_reorder').text('<?php echo __('disable reorder');?>');
            },
            function() {
                $('ul#site-map').nestedSortable('disable');               
                $('img.handle_reorder').hide();
                $('#toggle_reorder').text('<?php echo __('reorder');?>');
            }
    )      
});
</script>