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
<h1><?php echo __('Users'); ?></h1>

<table id="users" class="index" cellpadding="0" cellspacing="0" border="0">
  <thead>
    <tr>
      <th><?php echo __('Name'); ?> / <?php echo __('Username'); ?></th>
      <th><?php echo __('Email'); ?></th>
      <th><?php echo __('Roles'); ?></th>
      <th><?php echo __('Modify'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php foreach($users as $user): ?> 
    <tr class="node <?php echo odd_even(); ?>">
      <td class="user">
        <img src="http://www.gravatar.com/avatar.php?gravatar_id=<?php echo md5($user->email); ?>&amp;default=<?php echo URL_PUBLIC; ?>admin/images/user.png&amp;size=32" align="middle" alt="user icon" />
        <a href="<?php echo get_url('user/edit/'.$user->id); ?>"><?php echo $user->name; ?></a>
        <small><?php echo $user->username; ?></small>
      </td>
      <td><?php echo $user->email; ?></td>
      <td><?php echo implode(', ', $user->getPermissions()); ?></td>
      <td>
<?php if ($user->id > 1): ?>
        <a href="<?php echo get_url('user/delete/'.$user->id); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete it?'); ?>');"><img src="images/icon-remove.gif" alt="remove icon" /></a>
<?php else: ?>
        <img src="images/icon-remove-disabled.gif" alt="remove icon disabled" />
<?php endif; ?>
      </td>
    </tr>
<?php endforeach; ?> 
  </tbody>
</table>
