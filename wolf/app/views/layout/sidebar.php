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
?>
<?php if (Dispatcher::getAction() == 'index'): ?>

<p class="button"><a href="<?php echo get_url('layout/add'); ?>"><img src="<?php echo PATH_PUBLIC;?>wolf/admin/images/layout.png" align="middle" alt="layout icon" /> <?php echo __('New Layout'); ?></a></p>

<div class="box">
<h2><?php echo __('What is a Layout?'); ?></h2>
<p><?php echo __('Use layouts to apply a visual look to a Web page. Layouts can contain special tags to include page content and other elements such as the header or footer. Click on a layout name below to edit it or click <strong>Remove</strong> to delete it.'); ?></p>
</div>

<?php endif; ?>