<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Views
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

if (Dispatcher::getAction() == 'index'):
?>

<p class="button"><a href="<?php echo get_url('translate/core'); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/admin/images/file.png" align="middle" alt="document icon" /> <?php echo __('Create Core template'); ?></a></p>
<p class="button"><a href="<?php echo get_url('translate/plugins'); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/admin/images/file.png" align="middle" alt="document icon" /> <?php echo __('Create Plugin templates'); ?></a></p>

<div class="box">
    <h2>What is the difference...</h2>
    <p>between the core translation template and the plugin translation template?</p>
    <p>Easy. If you select the plugin translation template, the output that is generated will be one file containing all template files for all plugins that are installed.</p>
    <p>Provided that the plugins support tranlations offcourse.</p>
    <p>You will have to manually copy-paste the various plugin translation templates to their own files.</p>
</div>

<?php endif; ?>