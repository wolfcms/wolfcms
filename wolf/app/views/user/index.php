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

use_helper('Gravatar');
?>
<h2><?php echo __('Users'); ?></h2>

<div class="panel panel-default">
    <div class="panel-body">
        <table id="users" class="table table-striped">
            <thead>
                <th class="user-list-name"><?php echo __('Name'); ?> / <?php echo __('Username'); ?></th>
                <th class="user-list-email"><?php echo __('Email'); ?></th>
                <th class="user-list-role"><?php echo __('Roles'); ?></th>
                <th class="user-list-modify"><?php echo __('Modify'); ?></th>
            </thead>
            <tbody>
            <?php foreach($users as $user): ?> 
                <tr class="node <?php echo odd_even(); ?>">
                  <td class="user">
                    <?php
                    use_helper('Gravatar');
                    echo Gravatar::img($user->email, $attr = array(), '32', URL_PUBLIC.'wolf/admin/images/user.png', 'g', USE_HTTPS); 
                    //echo Gravatar::img($user->email, array('align' => 'middle', 'alt' => 'user icon'), '32', URL_PUBLIC.'wolf/admin/images/user.png', 'g', USE_HTTPS); 
                    ?>
                    <a href="<?php echo get_url('user/edit/'.$user->id); ?>"><?php echo $user->name; ?></a>
                    <small><?php echo $user->username; ?></small>
                  </td>
                  <td><?php echo $user->email; ?></td>
                  <td><?php echo implode(', ', $user->roles()); ?></td>
                  <td>
            <?php if ($user->id > 1): ?>
                    <a href="<?php echo get_url('user/delete/'.$user->id.'?csrf_token='.SecureToken::generateToken(BASE_URL.'user/delete/'.$user->id)); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete').' '.$user->name.'?'; ?>');" title="<?php echo __('Delete user'); ?>"><i class="fa fa-minus-square"></i></a>
            <?php else: ?>
                    <!--<a href="#" class="disabled" title="<?php echo __('Delete user unavailable'); ?>"><i class="fa fa-minus-square"></i></a>-->
                    <i class="fa fa-minus-square"></i>
            <?php endif; ?>
                  </td>
                </tr>
            <?php endforeach; ?> 
              </tbody>

        </table>
    </div>
</div>
