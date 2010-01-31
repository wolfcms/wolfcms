<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008,2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * @package wolf
 * @subpackage plugin.file_manager
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.0.0
 * @since Wolf version 0.9.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault & Martijn van der Kleijn, 2008
 */

if (Dispatcher::getAction() != 'view'): ?>

<p class="button"><a href="#create-file-popup" class="popupLink"><img src="<?php echo URI_PUBLIC; ?>wolf/plugins/file_manager/images/page.png" align="middle" alt="page icon" /> <?php echo __('Create new file'); ?></a></p>
<p class="button"><a href="#create-directory-popup" class="popupLink"><img src="<?php echo URI_PUBLIC; ?>wolf/plugins/file_manager/images/dir.png" align="middle" alt="dir icon" /> <?php echo __('Create new directory'); ?></a></p>
<p class="button"><a href="#upload-file-popup" class="popupLink"><img src="<?php echo URI_PUBLIC; ?>wolf/plugins/file_manager/images/upload.png" align="middle" alt="upload icon" /><?php echo __('Upload file'); ?></a></p>

<?php endif; ?>