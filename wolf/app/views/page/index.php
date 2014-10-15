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

<div class="panel panel-default">
	<!--<div id="site-map-def">
	    <div class="page"><?php echo __('Page'); ?> (<a href="#" id="toggle_reorder"><?php echo __('reorder'); ?></a>)</div>
	    <div class="page-layout"><?php echo __('Layout'); ?></div>
	    <div class="status"><?php echo __('Status'); ?></div>
	    <div class="view"><?php echo __('View'); ?></div>
	    <div class="modify"><?php echo __('Modify'); ?></div>
	</div> -->
	<div class="panel-heading">
		<div class="page-list-item">
			<div class="page-list-name"><?php echo __('Page'); ?> <span class="btn btn-default btn-xs" id="reorder-toggle"><?php echo __('reorder'); ?></span></div>
			<div class="page-list-layout"><?php echo __('Layout'); ?></div>
			<div class="page-list-status"><?php echo __('Status'); ?></div>
			<div class="page-list-view"><?php echo __('View'); ?></div>
			<div class="page-list-modify"><?php echo __('Modify'); ?></div>
		</div>
	</div><!-- # .panel-heading -->

	<div class="panel-body">
		<ul class="site-map-root">
			<li id="page-0" class="node level-0">
				<div class="page-list-item">
					<!-- Page -->
					<div class="page page-list-name">
						<?php if ( !AuthUser::hasPermission('page_edit') || (!AuthUser::hasPermission('admin_edit') && $root->is_protected) ): ?>
                            <i class="fa fa-file-o"></i> <span class="title"><?php echo $root->title; ?></span>
                        <?php else: ?>
                            <a href="<?php echo get_url('page/edit/1'); ?>" title="/"> <i class="fa fa-file"></i> <span class="title"><?php echo $root->title; ?></span></a>
                        <?php endif; ?>
					</div>
					<!-- # Page -->
					<!-- Layout -->
					<div class="layout page-list-layout">
						<?php echo Layout::findById($root->layout_id)->name; ?>
					</div>
					<!-- # Layout -->
					<!-- Status -->
					<div class="status page-list-status page-status-published">
						<?php echo __('Published'); ?>
					</div>
					<!-- # Status -->
					<!-- View -->
					<div class="view page-list-view">
						<a href="<?php echo URL_PUBLIC; ?>" target="_blank"><img src="<?php echo PATH_PUBLIC;?>wolf/admin/images/magnify.png" align="middle" alt="<?php echo __('View Page'); ?>" title="<?php echo __('View Page'); ?>" /></a>
					</div>
					<!-- # View -->
					<!-- Modify -->
					<div class="modify page-list-modify">
						<?php if ( AuthUser::hasPermission('page_add') ): ?>
                            <a href="<?php echo get_url('page/add/1'); ?>" title="<?php echo __('Add child'); ?>">
                                <i class="fa fa-plus-square"></i>
                            </a>
                        <?php endif; ?>
                        <i class="fa fa-minus-square remove"></i>
                        <i class="fa fa-copy"></i>
                        <!--<img class="remove" src="<?php echo PATH_PUBLIC; ?>wolf/admin/images/icon-remove-disabled.gif" align="middle" alt="<?php echo __('remove icon disabled'); ?>" title="<?php echo __('Remove unavailable'); ?>"/>-->
                        <!--<img src="<?php echo PATH_PUBLIC; ?>wolf/admin/images/copy-disabled.png" align="middle" title="<?php echo __('Copy Page Disabled'); ?>" alt="<?php echo __('Copy Page Disabled'); ?>" />-->

					</div>
					<!-- # Modify -->
				</div>
				<?php echo $content_children; ?>
			</li>
		</ul>
	</div><!-- #.panel-body -->
</div><!-- #panel -->

<script type="text/javascript">
    //jQuery(function() {
    jQuery.fn.spinnerSetup = function spinnerSetup() {
        this.each(function() {
            var pid = $(this).attr('id');
            $('#' + pid).hide()  // hide it initially
                    .ajaxStop(function() {
                        $('#' + pid).hide();
                    });
        });

        return this;
    };

    jQuery.fn.sitemapSetup = function sitemapSetup() {
        this.each(function() {
            if ($('ul', this).length)
                return;
            var pid = $(this).attr('id').split('_')[1];
        });

        return this;
    };

    // update parents with children list expanded
    function persistExpanded() {
        var expanded_rows = [];
        $('ul#site-map .expanded').parents('li').not('#page-0').each(function() {
            expanded_rows.push($(this).attr('id').split('_')[1]);
        });
        var rows = expanded_rows.reverse().toString();
        if (rows === '') {
            rows += ';expires=Sat, 25 Dec 2010 06:07:00 UTC';
        }
        document.cookie = 'expanded_rows=' + rows + ';';
    }

    // handle expander icons
    $(document).on('click', '.expander', function() {
        // console.log(this);
        if ($(this).hasClass("expanded")) {
            $(this).removeClass("expanded");
            $(this).attr('src', '<?php echo PATH_PUBLIC; ?>wolf/admin/images/expand.png');

            var parent = $(this).parents("li.node:first")
            var parentId = parent.attr('id').split('_')[1];

            $('#page_' + parentId).removeClass('children-visible').addClass('children-hidden').children('ul').hide();
        }
        else {
            $(this).addClass("expanded");
            $(this).attr('src', '<?php echo PATH_PUBLIC; ?>wolf/admin/images/collapse.png');
            var parent = $(this).parents("li.node:first");
            var parentId = parent.attr('id').split('_')[1];
            $('#page_' + parentId).removeClass('children-hidden').addClass('children-visible');

            if ($('#page_' + parentId).children('ul').length == 0) {
                // Determine level
                var parentClasses = document.getElementById('page_' + parentId).className;
                var parentLevel = new RegExp(/level-(\d+) /i).exec(parentClasses)[1];
                //var parentLevel = matched[1];
                // alert(parentLevel);
                $('#busy-' + parentId).show();
                $.get("<?php echo get_url('page/children/'); ?>" + parentId + '/' + parentLevel, function(data) {
                    $('#page_' + parentId).append(data);
                    $('#site-map li').sitemapSetup();
                    $('.busy').spinnerSetup();
                });
            }
            else {
                $('#page_' + parentId).children('ul').show();
            }
        }
        persistExpanded();
    });


    jQuery.fn.sortableSetup = function sortableSetup() {
        $('ul#site-map').nestedSortable({
            disableNesting: 'no-nest',
            forcePlaceholderSize: true,
            handle: 'div',
            items: 'li',
            opacity: .6,
            cursor: 'crosshair',
            placeholder: 'nested-list-placeholder',
            tabSize: 0,
            tolerance: 'pointer',
            toleranceElement: 'div.page-list-name',
            listType: 'ul',
            helper: 'clone',
            start: function() {
                //console.log('beforeStart');
                $("ul#site-map").find('li.children-hidden ul').remove();
            },
            beforeStop: function(event, ui) {
                // quick checks incase they have taken it out of the sitemap tree
                if (ui.item.parents("#page-0").is('li') === false)
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
                    success: function() {
                        // check where we have put the row so we can change styles if needbe
                        var parent = ui.item.parent().parents('li.node:first');


                        if (parent.hasClass('no-children') || parent.hasClass('children-hidden'))
                        {
                            // put into a row that has children but is closed
//                            ui.item.parent().hide().remove();

                            parent.removeClass('no-children').addClass('children-hidden');

                            // parent.css({'background-color':'red'});
                            parent.find('.expander-placeholder')
                                    .removeClass('expander-placeholder')
                                    .addClass('expander')
                                    .attr('src', '<?php echo PATH_PUBLIC; ?>wolf/admin/images/expand.png');

                            setTimeout(function() {
                                parent.find('.expander').trigger('click');
                            }, 100);

                            // window.location.reload(true);

                        }
                        fixChildLevels($(parent));

                        $('li.children-visible').each(function() {
                            var has_children = ($(this).find('li').length > 0);
                            if (!has_children) {
                                // alert('No more children');
                                $(this).find('.expander')
                                        .removeClass('expander expanded')
                                        .addClass('expander-placeholder')
                                        .attr('src', '<?php echo PATH_PUBLIC; ?>wolf/admin/images/clear.gif')
                                        .css({
                                            'width': 17,
                                            'height': 17
                                        });
                                $(this).removeClass('children-visible children-hidden');
                                $(this).addClass('no-children');
                            } else {

                            }


                        });
                        persistExpanded();
                    },
                    cache: false
                });



            }
        });
        return this;
    };

    function fixChildLevels(item) {
        var subItems = item.find('li.node', item);
        var itemLevel = new RegExp(/level-(\d+)/i).exec(item.attr('class'))[1];
        item.removeClass('level-' + itemLevel);
        var actualLevel = item.parents('li.node').length;
        item.addClass('level-' + actualLevel);
        if (subItems.length > 0) {
            subItems.each(function() {
                fixChildLevels($(this));
            });
        }
    }

    jQuery.fn.copyableSetup = function() {

        $(this).on('click', function() {
            var id = $(this).attr('id').split('-');

            $.ajax({
                type: 'post',
                url: '<?php echo get_url('page/copy'); ?>',
                data: "&originalid=" + id[1],
                cache: false,
                success: function(data) {

                    data = data.split('||');
                    var newid = parseInt(data[0]);

                    // setup the new row
                    var newobj = $("#page_" + id[1]).clone().css('display', 'none');

                    newobj.attr('id', 'page_' + newid); // set the main li id
                    newobj.find('.edit-link').attr({// set the edit link
                        'href': data[1],
                        'title': newid + ' | ' + data[3]
                    });
                    newobj.find('.title').html(data[2]); // set the page title
                    newobj.find('.busy').attr('id', 'busy-' + newid); // set the spinner id
                    newobj.find('.view-link').attr('href', data[4]); // set the view page link
                    newobj.find('.add-child-link').attr('href', data[5]); // set the add child link
                    newobj.find('.remove').attr('href', data[6]); // set the delete link
                    newobj.find('.remove').attr('onclick', '').unbind('click'); //remove old confirm dialog for delete link (needs both for IE/FF/Chrome)
                    newobj.find('.remove').click(function() {
                        return confirm('Are you sure you want to delete ' + data[2] + ' and its underlying pages?');
                    }); //set the onclick dialog box for delete link
                    newobj.find('.copy-page').attr('id', 'copy-' + newid); // set the copy id						

                    $("#page_" + id[1]).after(newobj); // add row to dom and slide down
                    newobj.slideDown();
                }
            });
        });
        return this;
    };

    $(document).ready(function() {
        $('#site-map li').sitemapSetup();
        $(".busy").spinnerSetup();
        $(".copy-page").copyableSetup();
        $('.reorder-handle').hide();
        $('ul#site-map').sortableSetup();
        $('ul#site-map').nestedSortable('disable');

        $('#reorder-toggle').click(function() {
            $(this).data('reorder', !$(this).data('reorder'));
            if ($(this).data('reorder')) {
                $('ul#site-map').nestedSortable('enable');
                $('.reorder-handle').show();
                $('#reorder-toggle').text('<?php echo __('disable reorder'); ?>');

            } else {
                $('ul#site-map').nestedSortable('disable');
                $('.reorder-handle').hide();
                $('#reorder-toggle').text('<?php echo __('reorder'); ?>');

            }
        });
    });
</script>