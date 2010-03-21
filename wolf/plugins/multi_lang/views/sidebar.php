<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS.
 *
 * Wolf CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Wolf CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wolf CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Wolf CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The multi lang plugin redirects users to a page with content in their language.
 *
 * The redirect only occurs when a user's indicated preferred language is
 * available. There are multiple methods to determine the desired language.
 * These are:
 *
 * - HTTP_ACCEPT_LANG header
 * - URI based language hint (for example: http://www.example.com/en/page.html
 * - Preferred language setting of logged in users
 *
 * @package plugins
 * @subpackage multi_lang
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.0.0
 * @since Wolf version 0.7.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2010
 */
?>
<p class="button"><a href="<?php echo get_url('plugin/multi_lang/settings'); ?>"><img src="<?php echo PLUGINS_URI;?>/multi_lang/images/settings.png" align="middle" alt="settings icon" /> <?php echo __('Settings'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/multi_lang/documentation/'); ?>"><img src="<?php echo PLUGINS_URI;?>/multi_lang/images/documentation.png" align="middle" alt="documentation icon" /> <?php echo __('Documentation'); ?></a></p>