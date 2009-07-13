<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
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
 * @package frog
 * @subpackage views
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2008
 */

if (Dispatcher::getAction() == 'index'):
?>

<p class="button"><a href="<?php echo get_url('translate/core'); ?>"><img src="images/file.png" align="middle" alt="document icon" /> <?php echo __('Create Core template'); ?></a></p>
<p class="button"><a href="<?php echo get_url('translate/plugins'); ?>"><img src="images/file.png" align="middle" alt="document icon" /> <?php echo __('Create Plugin templates'); ?></a></p>

<div class="box">
    <h2>What is the difference...</h2>
    <p>between the core translation template and the plugin translation template?</p>
    <p>Easy. If you select the plugin translation template, the output that is generated will be one file containing all template files for all plugins that are installed.</p>
    <p>Provided that the plugins support tranlations offcourse.</p>
    <p>You will have to manually copy-paste the various plugin translation templates to their own files.</p>
</div>

<?php endif; ?>