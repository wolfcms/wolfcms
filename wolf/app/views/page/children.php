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
<ul<?php if ($level == 1) echo ' id="site-map" class="sortable tree-root"'; else echo ' class="sortable child"'; ?>>
<?php foreach($childrens as $child): ?> 
    <li id="page_<?php echo $child->id; ?>" class="node level-<?php echo $level; if ( ! $child->has_children) echo ' no-children'; else if ($child->is_expanded) echo ' children-visible'; else echo ' children-hidden'; ?>">
      <div class="content-children">

<div class="page-list-item">
<!--<div class="indent">-->

      <div class="page">
        <span class="w1 indent">
          <?php if ($child->has_children): ?><img align="middle" alt="toggle children" class="expander<?php if($child->is_expanded) echo ' expanded'; ?>" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/<?php echo $child->is_expanded ? 'collapse': 'expand'; ?>.png" title="" /><?php endif; ?>
<?php if (!AuthUser::hasPermission('page_edit') || (!AuthUser::hasPermission('admin_edit') && $child->is_protected)): ?>
    <i class="fa fa-file-o"></i> <span class="title protected"><?php echo $child->title; ?></span> <img class="handle_reorder" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/drag_to_sort.gif" alt="<?php echo __('Drag and Drop'); ?>" align="middle" />
<?php else: ?>
    <i class="fa fa-file-o"></i> <a class="edit-link" href="<?php echo get_url('page/edit/'.$child->id); ?>" title="<?php echo $child->id.' | '.$child->slug; ?>"> <span class="title"><?php echo $child->title; ?></span></a> <img class="handle_reorder" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/drag_to_sort.gif" alt="<?php echo __('Drag and Drop'); ?>" align="middle" />
<?php endif; ?>
          <?php if (! empty($child->behavior_id)): ?> <small class="info">(<?php echo Inflector::humanize($child->behavior_id); ?>)</small><?php endif; ?> 
          <img align="middle" alt="" class="busy" id="busy-<?php echo $child->id; ?>" src="<?php echo PATH_PUBLIC;?>wolf/admin/images/spinner.gif" title="" />
        </span>
      </div>

<!--</div>--><!-- .indent -->

      <div class="page-layout"><?php $layout = Layout::findById($child->layout_id); echo isset($layout->name) ? htmlspecialchars($layout->name) : __('inherit'); ?></div>
<?php switch ($child->status_id) {
      case Page::STATUS_DRAFT: echo '<div class="status draft-status">'.__('Draft').'</div>'; break;
      case Page::STATUS_PREVIEW: echo '<div class="status preview-status">'.__('Preview').'</div>'; break;
      case Page::STATUS_PUBLISHED: echo '<div class="status published-status">'.__('Published').'</div>'; break;
      case Page::STATUS_HIDDEN: echo '<div class="status hidden-status">'.__('Hidden').'</div>'; break;
      case Page::STATUS_ARCHIVED: echo '<div class="status archived-status">'.__('Archived').'</div>'; break;
} ?>
      <div class="view-page"><a class="view-link" href="<?php echo URL_PUBLIC; echo (USE_MOD_REWRITE == false) ? '?' : ''; echo $child->path(); echo ($child->path() != '') ? URL_SUFFIX : ''; ?>" target="_blank"><i class="fa fa-eye"></i></a></div>
      <div class="modify">
        <a class="add-child-link" href="<?php echo get_url('page/add', $child->id); ?>" title="<?php echo __('Add child'); ?>"><i class="fa fa-plus-square"></i></a>&nbsp;
<?php if ( ! $child->is_protected || AuthUser::hasPermission('page_delete') ): ?>
        <a class="remove" href="<?php echo get_url('page/delete/'.$child->id.'?csrf_token='.SecureToken::generateToken(BASE_URL.'page/delete/'.$child->id)); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete'); ?> <?php echo $child->title; ?> <?php echo __('and its underlying pages'); ?>?');" title="<?php echo __('Remove page'); ?>"><i class="fa fa-minus-square"></i></a>&nbsp;
<?php endif; ?>
        <a href="#" id="copy-<?php echo $child->id; ?>" class="copy-page" title="<?php echo __('Copy Page'); ?>"><i class="fa fa-copy"></i></a>
      </div>

</div><!-- .page-list-item -->      
      </div><!-- /.content-children -->
<?php if ($child->is_expanded) echo $child->children_rows; ?>
    </li>
<?php endforeach; ?>
</ul>