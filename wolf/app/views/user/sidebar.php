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

if (Dispatcher::getAction() == 'index'):
?>

<a class="button wide large" href="<?php echo get_url('user/add'); ?>"><i class="fa fa-plus"></i> <?php echo __('New User'); ?></a>

<div class="box">
    <h2><?php echo __('Where do the avatars come from?'); ?></h2>
    <p><?php echo __('The avatars are automatically linked for those with a <a href="http://www.gravatar.com/" target="_blank">Gravatar</a> (a free service) account.'); ?></p>
</div>

<?php endif; ?>