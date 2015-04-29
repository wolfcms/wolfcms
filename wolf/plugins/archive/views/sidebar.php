<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2012-2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage archive
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2012-2013
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }
?>
<a class="button" href="<?php echo get_url('plugin/archive/settings'); ?>"><i class="fa fa-cog"></i><?php echo __('Settings'); ?></a>