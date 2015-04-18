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
<a class="button wide large" href="<?php echo get_url('plugin/comment/'); ?>"><i class="fa fa-comments"></i> <?php echo __('Comments'); ?></a>
<a class="button wide large" href="<?php echo get_url('plugin/comment/moderation/'); ?>"><i class="fa fa-check-square-o"></i> <?php echo __('Moderation'); ?></a>
<a class="button wide large" href="<?php echo get_url('plugin/comment/settings'); ?>"><i class="fa fa-gear"></i> <?php echo __('Settings'); ?></a>
<a class="button wide large" href="<?php echo get_url('plugin/comment/documentation/'); ?>"><i class="fa fa-file-text-o"></i> <?php echo __('Documentation'); ?></a>
