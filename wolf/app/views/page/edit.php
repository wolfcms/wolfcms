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
 * @copyright Philippe Archambault, Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
// @todo clean up code/solution
$pagetmp = Flash::get('page');
$parttmp = Flash::get('page_parts');
$tagstmp = Flash::get('page_tag');
if ( $pagetmp != null && !empty($pagetmp) && $parttmp != null && !empty($parttmp) && $tagstmp != null && !empty($tagstmp) ) {
    $page       = $pagetmp;
    $page_parts = $parttmp;
    $tags       = $tagstmp;
}

if ( $action == 'edit' ):
    $viev_page_url = URL_PUBLIC
                . (USE_MOD_REWRITE == false) ? '?' : ''
                . $page->path()
                . ($page->path() != '') ? URL_SUFFIX : '';
endif;
?>

<h1><?php echo __(ucfirst($action) . ' Page'); ?></h1>

<div class="pane panel-default">

<form id="page_edit_form" action="<?php
if ( $action == 'add' )
    echo get_url('page/add');
else
    echo get_url('page/edit/' . $page->id);
?>" method="post">
    <input id="page_parent_id" name="page[parent_id]" type="hidden" value="<?php echo $page->parent_id; ?>" />
    <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />
    
    <div class="panel-heading">

    <div class="form-horizontal">
        <div class="form-group">
            <label class="control-label page-title-label">
                <?php echo __('Page Title'); ?>
            </label>
            <div class="page-title-value">
                <input class="form-control" id="page_title" maxlength="255" name="page[title]" type="text" value="<?php echo $page->title; ?>" />
            </div>
            <div class="page-title-actions">
                <a class="btn btn-primary" id="page-view-frontend" target="_blank" href="<?php echo $view_page_url; ?>">
                    <span class="glyphicon glyphicon-zoom-in"></span>
                    <?php echo __('View this page'); ?>
                </a>
            </div>
        </div>                
    </div>

    <?php if ( isset($page->updated_on) ): ?>
        <p class="last-modified-info">            
            <?php echo __('Last updated by :username on :date', array( ':username' => $page->updated_by_name, ':date' => date('D, j M Y', strtotime($page->updated_on)) )); ?>
        </p>
    <?php endif; ?>

    </div> <!-- # .panel-heading -->
    
    <div class="panel-body">

    <ul class="nav nav-tabs page-part-tabs">
        <?php foreach ( $page_parts as $key => $page_part ) { ?>
            <li id="part-<?php echo $key + 1; ?>-tab" class="tab part-tab">
                <a href="#part-<?php echo $key + 1; ?>-content" data-toggle="tab">
                    <?php echo $page_part->name; ?>
                </a>
            </li>
        <?php } ?>
        <li class="add-part-tab">
            <a data-toggle="modal" href="#add-part-dialog" title="<?php echo __('Add Tab'); ?>">
                <i class="fa fa-plus-square"></i>
            </a>
        </li>
        <li>
            <a href="#" class="delete-part" title="<?php echo __('Remove Tab'); ?>">
                <i class="fa fa-minus-square"></i>
            </a>
        </li>

        <li class="pull-right core-tab">
            <a href="#settings" data-toggle="tab" data-toggle="" title="<?php echo __('Settings'); ?>">
                <span class="glyphicon glyphicon-list-alt"></span>
                <span class="tab-label">
                    <?php echo __('Settings'); ?>
                </span>
            </a>
        </li>
        <li class="pull-right core-tab">
            <a href="#metadata" data-toggle="tab" title="<?php echo __('Metadata'); ?>">
                <span class="glyphicon glyphicon-cog"></span>
                <span class="tab-label">
                    <?php echo __('Metadata'); ?>
                </span>
            </a>
        </li>
        <?php Observer::notify('view_page_edit_tab_links', $page); ?>            
    </ul>  


    <fieldset class="settings-tab-pane">
        <div id="page-part-contents" class="tab-content page-part-contents form-group">      
            <?php
            $index = 1;
            foreach ( $page_parts as $page_part ) {
                echo new View('page/part_edit', array( 'index' => $index, 'page_part' => $page_part ));
                $index++;
            }
            ?>
            <div class="tab-pane settings-tab-pane form-horizontal" id="metadata">
                <?php if ( $page->parent_id != 0 ) : ?>
                    <div class="form-group">
                        <label class="control-label setting-2col-label" for="page_slug"><?php echo __('Slug'); ?></label>
                        <div class="setting-2col-value">
                            <input class="form-control" id="page_slug" maxlength="100" name="page[slug]" type="text" value="<?php echo $page->slug; ?>" />
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="control-label setting-2col-label" for="page_breadcrumb">
                        <?php echo __('Breadcrumb'); ?>
                    </label>
                    <div class="setting-2col-value">
                        <input class="form-control" id="page_breadcrumb" maxlength="160" name="page[breadcrumb]" type="text" value="<?php echo htmlentities($page->breadcrumb, ENT_COMPAT, 'UTF-8'); ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label setting-2col-label" for="page_keywords"
                           ><?php echo __('Keywords'); ?>
                    </label>
                    <div class="setting-2col-value">
                        <input class="form-control" id="page_keywords" maxlength="255" name="page[keywords]" type="text" value="<?php echo $page->keywords; ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label setting-2col-label" for="page_description">
                        <?php echo __('Description'); ?>
                    </label>
                    <div class="setting-2col-value">
                        <textarea class="form-control" id="page_description" name="page[description]" rows="2"><?php echo $page->description; ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label setting-2col-label" for="page_tags">
                        <?php echo __('Tags'); ?>
                    </label>
                    <div class="setting-2col-value">
                        <input class="form-control" id="page_tags" maxlength="255" name="page_tag[tags]" type="text" value="<?php echo join(', ', $tags); ?>" />
                    </div>
                </div>
            </div>
            <div class="tab-pane settings-tab-pane form-horizontal" id="settings">
                <div id="div-settings">
                    <div class="settings-panel-general">
                        <?php if ( $page->parent_id != 0 ) : ?>
                            <div class="form-group">
                                <label class="control-label setting-2col-label" for="page_id">
                                    <?php echo __('Page id'); ?>
                                </label>
                                <div class="setting-2col-value">
                                    <input class="form-control" id="page_id" maxlength="100" name="unused" type="text" value="<?php echo $page->id; ?>" disabled="disabled"/>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="control-label setting-2col-label" for="page_layout_id">
                                <?php echo __('Layout'); ?>
                            </label>
                            <div class="setting-2col-value">
                                <select class="form-control" id="page_layout_id" name="page[layout_id]">
                                    <option value="0">&#8212; <?php echo __('inherit'); ?> &#8212;</option>
                                    <?php foreach ( $layouts as $layout ): ?>
                                        <option value="<?php echo $layout->id; ?>"<?php echo $layout->id == $page->layout_id ? ' selected="selected"' : ''; ?>><?php echo $layout->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label setting-2col-label" for="page_behavior_id">
                                <?php echo __('Page Type'); ?>
                            </label>
                            <div class="setting-2col-value">
                                <select class="form-control" id="page_behavior_id" name="page[behavior_id]">
                                    <option value=""<?php if ( $page->behavior_id == '' ) echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
                                    <?php foreach ( $behaviors as $behavior ): ?>
                                        <option value="<?php echo $behavior; ?>"<?php if ( $page->behavior_id == $behavior ) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($behavior); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if ( AuthUser::hasPermission('page_edit') ): ?>
                            <div class="form-group">
                                <label class="control-label setting-2col-label" for="page_needs_login">
                                    <?php echo __('Login'); ?>
                                </label>
                                <div class="setting-2col-value">
                                    <select class="form-control" id="page_needs_login" name="page[needs_login]" title="<?php echo __('When enabled, users have to login before they can view the page.'); ?>">
                                        <option value="<?php echo Page::LOGIN_INHERIT; ?>"<?php echo $page->needs_login == Page::LOGIN_INHERIT ? ' selected="selected"' : ''; ?>><?php echo __('&#8212; inherit &#8212;'); ?></option>
                                        <option value="<?php echo Page::LOGIN_NOT_REQUIRED; ?>"<?php echo $page->needs_login == Page::LOGIN_NOT_REQUIRED ? ' selected="selected"' : ''; ?>><?php echo __('not required'); ?></option>
                                        <option value="<?php echo Page::LOGIN_REQUIRED; ?>"<?php echo $page->needs_login == Page::LOGIN_REQUIRED ? ' selected="selected"' : ''; ?>><?php echo __('required'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label setting-2col-label" for="page_needs_login">
                                    <?php echo __('Protected'); ?> 
                                </label>
                                <div class="setting-2col-value">
                                    <div class="checkbox" title="<?php echo __('When enabled, only users who are an administrator can edit the page.'); ?>">
                                        <input id="page_is_protected" name="page[is_protected]" type="checkbox" value="1"<?php if ( $page->is_protected ) echo ' checked="checked"'; ?><?php if ( !AuthUser::hasPermission('admin_edit') ) echo ' disabled="disabled"'; ?>/>
                                        <?php echo __('Only administrators can edit this page'); ?> 
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>                        
                    </div> <!-- .settings-general -->
                    <div class="settings-panel-dates">
                        <?php if ( isset($page->created_on) ): ?>
                            <div class="form-group">
                                <label class="control-label setting-2col-label" for="page_created_on">
                                    <?php echo __('Created date'); ?>
                                </label>
                                <div class="setting-2col-value">
                                    <div class="input-day">
                                        <input class="form-control" id="page_created_on" type="date" name="page[created_on]" type="text" value="<?php echo substr($page->created_on, 0, 10); ?>" />
                                    </div>
                                    <div class="input-hour">
                                        <input class="form-control" id="page_created_on_time" type="time" step="1" name="page[created_on_time]" value="<?php echo substr($page->created_on, 11); ?>" />
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ( isset($page->published_on) ): ?>
                            <div class="form-group">
                                <label class="control-label setting-2col-label" for="page_published_on">
                                    <?php echo __('Published date'); ?>
                                </label>
                                <div class="setting-2col-value">
                                    <div class="input-day">
                                        <input class="form-control" id="page_published_on" type="date" name="page[published_on]" type="text" value="<?php echo substr($page->published_on, 0, 10); ?>" />
                                    </div>
                                    <div class="input-hour">
                                        <input class="form-control" id="page_published_on_time" type="time" step="1" name="page[published_on_time]" value="<?php echo substr($page->published_on, 11); ?>" />
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ( isset($page->published_on) ): ?>
                            <div class="form-group">
                                <label class="control-label setting-2col-label" for="page_valid_until">
                                    <?php echo __('Valid until date'); ?>
                                </label>
                                <div class="setting-2col-value">
                                    <div class="input-day">
                                        <input class="form-control" id="page_valid_until" type="date" name="page[valid_until]" type="text" value="<?php echo substr($page->valid_until, 0, 10); ?>" />
                                    </div>
                                    <div class="input-hour">
                                        <input class="form-control" id="page_valid_until_time" type="time" step="1" name="page[valid_until_time]" value="<?php echo substr($page->valid_until, 11); ?>" />
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div> <!-- .settings-general -->
                </div>
            </div>
            <?php Observer::notify('view_page_edit_tabs', $page); ?>
        </div>
        <div>
            <?php Observer::notify('view_page_after_edit_tabs', $page); ?>
            <div class="form-group form-inline page-plugins-area">
                <label class="control-label" for="page_status_id">
                    <?php echo __('Status'); ?>
                </label>
                <select  class="form-control" id="page_status_id" name="page[status_id]">
                    <option value="<?php echo Page::STATUS_DRAFT; ?>"<?php echo $page->status_id == Page::STATUS_DRAFT ? ' selected="selected"' : ''; ?>><?php echo __('Draft'); ?></option>
                    <option value="<?php echo Page::STATUS_PREVIEW; ?>"<?php echo $page->status_id == Page::STATUS_PREVIEW ? ' selected="selected"' : ''; ?>><?php echo __('Preview'); ?></option>
                    <option value="<?php echo Page::STATUS_PUBLISHED; ?>"<?php echo $page->status_id == Page::STATUS_PUBLISHED ? ' selected="selected"' : ''; ?>><?php echo __('Published'); ?></option>
                    <option value="<?php echo Page::STATUS_HIDDEN; ?>"<?php echo $page->status_id == Page::STATUS_HIDDEN ? ' selected="selected"' : ''; ?>><?php echo __('Hidden'); ?></option>
                    <option value="<?php echo Page::STATUS_ARCHIVED; ?>"<?php echo $page->status_id == Page::STATUS_ARCHIVED ? ' selected="selected"' : ''; ?>><?php echo __('Archived'); ?></option>
                </select>

                <?php Observer::notify('view_page_edit_plugins', $page); ?>

            </div>
        </div>


    </fieldset>


    <fieldset class="buttons form-inline smart">
        <button class="btn btn-primary" name="commit" type="submit" accesskey="s"><?php echo __('Save and Close'); ?></button>
        <button class="btn btn-primary" name="continue" type="submit" accesskey="e"><?php echo __('Save and Continue Editing'); ?></button>
        <a class="btn btn-default edit-cancel" href="<?php echo get_url('page'); ?>"><span class="glyphicon glyphicon-remove"></span> <?php echo __('Cancel'); ?></a>
    </fieldset>

    </div> <!-- # .panel-body -->

</form>

</div><!-- #panel -->


<!-- Modal -->
<div class="modal fade static" id="add-part-dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><?php echo __('Add Tab'); ?>
                    <span id="busy" class="busy" style="display: none;">
                        <img alt="Spinner" src="<?php echo PATH_PUBLIC; ?>wolf/admin/images/spinner.gif" />
                    </span>
                </h3>
            </div>
            <div class="modal-body">
                <form>
                    <div>
                        <input id="part-index-field" name="part[index]" type="hidden" value="<?php echo $index; ?>" />
                        <input class="form-control" id="part-name-field" maxlength="100" name="part[name]" type="text" value="" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <button type="button" class="btn btn-primary" id="add-part-button"><?php echo __('Add'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php Observer::notify('view_page_edit_popup', $page); ?>

<div id="boxes">
    <!-- Add part dialog -->

</div>

<script type="text/javascript">
// <![CDATA[
    jQuery.fn.spinnerSetup = function spinnerSetup() {
        this.each(function() {
            var pid = $(this).attr('id')
            $('#' + pid).hide()  // hide it initially
                    .ajaxStop(function() {
                        $('#' + pid).hide();
                    });
        });

        return this;
    };

    $(document).ready(function() {
        $(".busy").spinnerSetup();

        var editAction = '<?php echo $action; ?>';

        if (editAction == 'add') {
            $('#page_title').change(function() {
                $('#page_slug').val(toSlug(this.value));
                $('#page_breadcrumb').val(this.value);
            });
        }

        // Store PHP value for later reference
        var partIndex = <?php echo $index; ?>;

        // Prevent accidentally navigating away
        $('form#page_edit_form :input').bind('change', function() {
            setConfirmUnload(true);
        });
        $('form#page_edit_form').submit(function() {
            setConfirmUnload(false);
            return true;
        });

        // Do the metainfo tab thing
        $('ul.page-metainfo-tabs li a').bind('click', function(event) {
            // $('div#metainfo-content > div.page').hide().filter(this.hash).show();
            // Get index and current page id
            var i = $(this).parent('li').index();
            var pageID = page_id();

            document.cookie = "meta_tab=" + pageID + ':' + i;
            // return false;
        });

        // Part switch handler
        $('ul.page-part-tabs li a').live('click', function(event) {
            /* Get index and current page id */
            var i = $(this).parent('li').index();
            var pageID = page_id();

            $(this).addClass('here');

            document.cookie = "page_tab=" + pageID + ':' + i;
            $(this).trigger('pageTabFocus', [i, this.hash]);
            return false;
        });

        (function() {
            var id, metaTab, pageTab,
                    pageId = page_id(),
                    meta = document.cookie.match(/meta_tab=(\d+):(\d+);/),
                    part = document.cookie.match(/page_tab=(\d+):(\d+);/);

            if (meta && pageId == meta[1]) {
                metaTab = (meta[2]) ? meta[2] : 0;
            } else {
                metaTab = 0;
            }

            if (part && pageId == part[1]) {
                pageTab = (part[2]) ? part[2] : 0;
            } else {
                pageTab = 0;
            }

            // $('div#metainfo-content > div.page').hide();
            $('ul.page-metainfo-tabs li a').eq(metaTab).click();

            // $('div#metainfo-content > div.page').hide();

            $('ul.page-part-tabs li a').eq(pageTab).click();
        })();

        // Do the submit add part window thing
        $('#add-part-button').click(function(e) {
            e.preventDefault();

            var newPartName = $('div#add-part-dialog input#part-name-field').val();
            if (valid_part_name(newPartName)) {
                // alert('add-part-submit');
                $('ul.page-part-tabs li.part-tab').filter(':last').after(
                        '<li id="part-' + partIndex + '-tab" class="tab part-tab">' +
                        '<a href="#part-' + partIndex + '-content" data-toggle="tab">' +
                        newPartName +
                        '</a></li>'
                        );

                $('div#part-tabs ul.page-part-tabs li#part-' + partIndex + '-tab a').click();
                $('div#add-part-dialog input#part-index-field').val(partIndex);

                $.post('<?php echo get_url('page/addPart'); ?>',
                        $('div#add-part-dialog form').serialize(),
                        function(data) {
                            $('div#page-part-contents').append(data);
                        });

                partIndex++;

                // Make sure users save changes
                setConfirmUnload(true);
                $('#add-part-dialog').modal('hide');
            }

            return false;
        });

        // Do the delete part button thing
        $('a.delete-part').click(function() {
            // Delete the tab
            var partRegEx = /part-(\d+)-tab/i;
            var myRegEx = new RegExp(partRegEx);
            var matched = myRegEx.exec($('ul.page-part-tabs li.active').attr('id'));
            var removePart = matched[1];

            if (!confirm('<?php echo __('Delete the current tab?'); ?>' + ' ' + removePart)) {
                return;
            }

            $('ul.page-part-tabs li.active').remove();
            $('ul.page-part-tabs li a').filter(':first').click();

            // Delete the content section
            $('div#part-' + removePart + '-content').remove();

            // Make sure users save changes
            setConfirmUnload(true);
        });

    });
// ]]>
</script>
