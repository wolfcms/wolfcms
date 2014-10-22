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
<h2><?php echo __('MSG_SNIPPETS'); ?></h2>

<div id="site-map-def" class="panel panel-default index-def">
    
    <div class="panel-heading">
        <div id="snippet" class="snippet-list-item">
            <div class="snippet-list-name">
                <?php echo __('Snippet'); ?> <span class="btn btn-default btn-xs" id="reorder-toggle"><?php echo __('reorder'); ?></span>
            </div>
            <div class="snippet-list-modify">
                <?php echo __('Modify'); ?>
            </div>
        </div>
    </div>

    <div class="panel-body">
        <ul id="snippets" class="snippet-list list-unstyled">
        <?php foreach ( $snippets as $snippet ) { ?>
            <li id="snippet_<?php echo $snippet->id; ?>" class="snippet-list-item node <?php echo odd_even() ?>">
                <span class="snippet-list-name">
                    <i class="fa fa-file-o"></i>
                    <a href="<?php echo get_url('snippet/edit/' . $snippet->id); ?>"><?php echo $snippet->name; ?></a>
                    <img class="handle" src="<?php echo PATH_PUBLIC; ?>wolf/admin/images/drag.gif" alt="<?php echo __('Drag and Drop'); ?>"/>
                </span>
                <span class="snippet-list-modify">
                    <?php if ( AuthUser::hasPermission('snippet_delete') ): ?>        
                        <a class="remove" href="<?php echo get_url('snippet/delete/' . $snippet->id); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete?'); ?> <?php echo $snippet->name; ?>?');" title="<?php echo __('Delete snippet'); ?>"><i class="fa fa-minus-square"></i></a>
                    <?php endif; ?>
                </span>
            </li>
        <?php } ?>
        </ul>
    </div>

</div>

<style type="text/css" >
    .placeholder {
        height: 2.4em;
        line-height: 1.2em;
        border: 1px solid #fcefa1;
        background-color: #fbf9ee;
        color: #363636;
    }
</style>

<script type="text/javascript">
// <![CDATA[
    jQuery.fn.sortableSetup = function sortableSetup() {
        this.sortable({
            disabled:true,
            tolerance:'intersect',
            containment:'#main',
            placeholder:'placeholder',
            revert: true,
            handle: '.handle',
            cursor:'crosshair',
            distance:'15',
            stop: function(event, ui) {
                var order = $(ui.item.parent()).sortable('serialize', {key: 'layouts[]'});
                $.post('<?php echo get_url('snippet/reorder/'); ?>', {data : order});
            }
        })
        .disableSelection();

        return this;
    };

    $(document).ready(function() {
        $('ul#snippets').sortableSetup();
        $('#reorder-toggle').toggle(
            function(){
                $('ul#snippets').sortable('option', 'disabled', false);
                $('.handle').show();
                $('#reorder-toggle').text('<?php echo __('disable reorder');?>');
            },
            function() {
                $('ul#snippets').sortable('option', 'disabled', true);
                $('.handle').hide();
                $('#reorder-toggle').text('<?php echo __('reorder');?>');
            }
        )
    });

// ]]>
</script>
