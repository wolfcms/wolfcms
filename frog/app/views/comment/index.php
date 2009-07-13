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
<h1><?php echo __('Comments'); ?></h1>

<div id="comments-def">
    <div class="comment"><?php echo __('Comments'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>

<?php if (count($comments)): ?>
<ol id="comments">
<?php foreach($comments as $comment): ?>

    <li class="<?php echo odd_even(); ?> <?php echo ($comment->is_approved) ? 'approve': 'reject'; ?>">
          <a href="<?php echo get_url('comment/edit/'.$comment->id); ?>"><b><?php echo $comment->page_title; ?></b></a>
          <p><?php echo $comment->body; ?></p>
          <div class="infos">
              <?php echo date('D, j M Y', strtotime($comment->created_on)); ?> &#8212; 
              <a href="<?php echo get_url('comment/delete/'.$comment->id); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete it?'); ?>');"><?php echo __('Delete'); ?></a> | 
<?php if ($comment->is_approved): ?>
              <a href="<?php echo get_url('comment/unapprove/'.$comment->id); ?>"><?php echo __('Reject'); ?></a>
<?php else: ?>
              <a href="<?php echo get_url('comment/approve/'.$comment->id); ?>"><?php echo __('Approve'); ?></a>
<?php endif; ?>
          </div>
      </li>
<?php endforeach; ?>
</ol>
<?php else: ?>
<h3><?php echo __('No comments found.'); ?></h3>
<?php endif; ?>
