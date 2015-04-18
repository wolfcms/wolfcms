<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * @package Plugins
 * @subpackage file-manager
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2011
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

if (Dispatcher::getAction() != 'view'): ?>

<a class="button wide large popupLink" href="#create-file-popup"><i class="fa fa-plus"></i> <?php echo __('Create new file'); ?></a>
<a class="button wide large popupLink" href="#create-directory-popup"><i class="fa fa-folder-o"></i> <?php echo __('Create new directory'); ?></a>
<a class="button wide large popupLink" href="#upload-file-popup"><i class="fa fa-upload"></i> <?php echo __('Upload file'); ?></a>

<?php endif; ?>