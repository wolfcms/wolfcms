<?php
/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package frog
 * @subpackage plugin.comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.2.0
 * @since Frog version 0.9.3
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 */
?>

<h1><?php echo __('Comments'); ?></h1>
<div id="comments-def">
    <div class="comment"><?php echo __('Comments'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>
<?php
global $__FROG_CONN__;
$sql = "SELECT COUNT(*) FROM ".TABLE_PREFIX."comment WHERE is_approved = 1";
$stmt = $__FROG_CONN__->query($sql);
$comments_count = $stmt->fetchColumn();
$stmt->closeCursor();

if (isset($page)) {
    $CurPage = $page;
} else {
    $CurPage = 0;
}
$rowspage = Plugin::getSetting('rowspage', 'comment');

$start = $CurPage * $rowspage;

$totalrecords = $comments_count;
$sql = "SELECT comment.is_approved, comment.id, comment.page_id, comment.author_name, comment.body, comment.created_on, page.title FROM " .
    TABLE_PREFIX . "comment AS comment, " . TABLE_PREFIX .
    "page AS page WHERE comment.is_approved = 1 AND comment.page_id = page.id LIMIT " . $start . "," . $rowspage;

$stmt = $__FROG_CONN__->prepare($sql);
$stmt->execute();
$lastpage = ceil($totalrecords / $rowspage);
if($comments_count <= $rowspage) { $lastpage = 0; } else { $lastpage = abs($lastpage - 1); }
?>
<?php
if ($comments_count > 0) { ?>
<ol id="comments">
    <?php while ($comment = $stmt->fetchObject()): ?>
    <li class="<?php echo odd_even(); ?> moderate">
          <strong><a href="<?php echo get_url('plugin/comment/edit/' . $comment->id); ?>"><?php echo $comment->author_name.' '.__('about').' "'.$comment->title.'"'; ?></a></strong>
          <p><?php echo $comment->body; ?></p>
          <div class="infos">
              <?php echo date('D, j M Y', strtotime($comment->created_on)); ?> &#8212; 
              <a href="<?php echo get_url('plugin/comment/delete/' . $comment->id); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete it?'); ?>');"><?php echo
__('Delete'); ?></a> | <?php if ($comment->is_approved): ?>
              <a href="<?php echo get_url('plugin/comment/unapprove/' . $comment->id); ?>"><?php echo __('Reject'); ?></a>
<?php else: ?>
              <a href="<?php echo get_url('plugin/comment/approve/' . $comment->id); ?>"><?php echo __('Approve'); ?></a>
<?php endif; ?>
          </div>
      </li>
<?php endwhile; ?>
</ol>
<?php
} else {
    echo '<h3>'.__('No comments found.').'</h3>';
}
?>
<br />
<div class="pagination">
<?php
            if ($CurPage == $lastpage) {
                $next = '<span class="disabled">Next Page</span>';
            } else {
                $nextpage = $CurPage + 1;
                $next = '<a href="' . get_url('plugin/comment/index/') . '' . $nextpage .
                    '">Next Page</a>';

            }
            if ($CurPage == 0) {
               $prev = '<span class="disabled">Previous Page</span>';
            } else {
                $prevpage = $CurPage - 1;
                $prev = '<a href="' . get_url('plugin/comment/index/') . '' . $prevpage .
                    '">Previous Page</a>';
            }
            if ($CurPage != 0) {
                echo "<a href=" . get_url('plugin/comment/index/') . "0>First Page</a>\n ";
            }
            else {
            	echo "<span class=\"disabled\">First Page</span>";
            }
            echo $prev;
            for ($i = 0; $i <= $lastpage; $i++) {
                if ($i == $CurPage)
                    echo '<span class="current">'.$i.'</span';
                else
                    echo " <a href=" . get_url('plugin/comment/index/') . "$i>$i</a>\n";
            }
            echo $next;
            if ($CurPage != $lastpage) {
                echo "\n<a href=" . get_url('plugin/comment/index/') . "$lastpage>Last Page</a>&nbsp&nbsp;";
            }
            else {
            	echo "<span class=\"disabled\">Last Page</span>";
            }
?>
</div>