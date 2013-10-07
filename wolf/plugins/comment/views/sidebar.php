<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<p class="button"><a href="<?php echo get_url('plugin/comment/'); ?>"><img src="<?php echo ICONS_PATH;?>comment-32-ns.png" align="middle" alt="page icon" /> <?php echo __('Comments'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/comment/moderation/'); ?>"><img src="<?php echo ICONS_PATH;?>action-approve-32-ns.png" align="middle" alt="page icon" /> <?php echo __('Moderation'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/comment/settings'); ?>"><img src="<?php echo ICONS_PATH;?>settings-32-ns.png" align="middle" alt="page icon" /> <?php echo __('Settings'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/comment/documentation/'); ?>"><img src="<?php echo ICONS_PATH;?>documentation-32-ns.png" align="middle" alt="page icon" /> <?php echo __('Documentation'); ?></a></p>
