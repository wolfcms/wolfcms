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
    <div class="page"><?php echo __('Page'); ?> (<a href="#" id="toggle_reorder"><?php echo __('reorder'); ?></a>)</div>
    <div class="status"><?php echo __('Status'); ?></div>
    <div class="view"><?php echo __('View'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>

<ul id="site-map-root">
    <li id="page-0" class="node level-0">
      <div class="page" style="padding-left: 4px">
        <span class="w1">
<?php if ($root->is_protected && ! AuthUser::hasPermission('page_edit')): ?>
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
          <img class="remove" src="<?php echo URI_PUBLIC;?>wolf/admin/images/icon-remove-disabled.gif" align="middle" alt="<?php echo __('remove icon disabled'); ?>" title="<?php echo __('Remove unavailable'); ?>"/>&nbsp;
      	  <img src="<?php echo URI_PUBLIC;?>wolf/admin/images/copy-disabled.png" align="middle" title="<?php echo __('Copy Page Disabled'); ?>" alt="<?php echo __('Copy Page Disabled'); ?>" />
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
                var pid = $j(this).attr('id')
                $j('#'+pid).hide()  // hide it initially
                .ajaxStop(function() {
                    $j('#'+pid).hide();
                });
            });

            return this;
        };

        jQuery.fn.sitemapSetup = function sitemapSetup() {
            this.each(function () {
            	if($j('ul',this).length) return;
                var pid = $j(this).attr('id').split('_')[1];
            });

            return this;
        };
        
        jQuery.fn.expandableSetup = function expandableSetup() {
            $j(this).live('click', function() {
                if ($j(this).hasClass("expanded")) {
                    $j(this).removeClass("expanded");
                    $j(this).attr('src', '<?php echo URI_PUBLIC; ?>wolf/admin/images/expand.png');

                    var parent = $j(this).parents("li.node:first")
                    var parentId = parent.attr('id').split('_')[1];

                    $j('#page_'+parentId).children('ul').hide();
                }
                else {
                    $j(this).addClass("expanded");
                    $j(this).attr('src', '<?php echo URI_PUBLIC; ?>wolf/admin/images/collapse.png');
                    var parent = $j(this).parents("li.node:first");
                    var parentId = parent.attr('id').split('_')[1];
                    if ($j('#page_'+parentId).children('ul').length == 0) {
                        $j('#busy-'+parentId).show();
                        $j.get("<?php echo get_url('page/children/'); ?>"+parentId+'/'+'1', function(data) {                        
                            $j('#page_'+parentId).append(data);
                            $j('#site-map li').sitemapSetup();
                            $j('.busy').spinnerSetup();
                        });
                    }
                    else {
                        $j('#page_'+parentId).children('ul').show();
                    }
                }
            });
        };
        
        jQuery.fn.sortableSetup = function sortableSetup() { 
			$j('ul#site-map').nestedSortable({
				disableNesting: 'no-nest',
				forcePlaceholderSize: true,
				handle: 'div',
				items: 'li',
				opacity: .6,
				placeholder: 'placeholder',
				tabSize: 25,
				tolerance: 'pointer',
				toleranceElement: '> span',
				listType: 'ul',
				helper: 'clone',
				beforeStop: function(event, ui) {
					// quick checks incase they have taken it out of the sitemap tree
					if(ui.item.parents("#page-0").is('li') === false)
					{	
						$j("ul#site-map").nestedSortable('cancel');
					}
				},
                stop: function(event, ui) {                    
                	var order = $j("ul#site-map").nestedSortable('serialize');
                	
 					$j.ajax({
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
        
			$j(this).live('click', function() {			
				var id = $j(this).attr('id').split('-');
				
				$j.ajax({
					type: 'post',
					url: '<?php echo get_url('page/copy'); ?>',
					data: "&originalid="+id[1],
					cache: false,
					success: function(data) {
					
						data = data.split('||');
						var newid = parseInt(data[0]);
						
						// setup the new row
						var newobj = $j("#page_"+id[1]).clone().css('display', 'none');					
						
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
						newobj.find('.copy-page').attr('id', 'copy-'+newid); // set the copy id						
						
						$j("#page_"+id[1]).after(newobj); // add row to dom and slide down
						newobj.slideDown();
					}						
				});
			});
            return this;
        };        
         
        
$j(document).ready(function(){
    $j('#site-map li').sitemapSetup();
    $j("img.expander").expandableSetup(); 
    $j(".busy").spinnerSetup();
    $j(".copy-page").copyableSetup();
    $j('ul#site-map').sortableSetup();
    $j('ul#site-map').nestedSortable('disable');

    $j('#toggle_reorder').toggle(
            function(){
    			$j('ul#site-map').nestedSortable('enable');  
    			$j('img.handle_reorder').show();
                $j('#toggle_reorder').text('<?php echo __('disable reorder');?>');
            },
            function() {
                $j('ul#site-map').nestedSortable('disable');               
                $j('img.handle_reorder').hide();
                $j('#toggle_reorder').text('<?php echo __('reorder');?>');
            }
    )      
});
</script>