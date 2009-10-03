<?php
/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages and settings to an XML file.
 *
 * @package wolf
 * @subpackage plugin.backup_restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.0.1
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Martijn van der Kleijn, 2009
 */
?>
<h1><?php echo __('Documentation'); ?></h1>
<p>
    The Backup/Restore plugin allows you to create complete backups of the Wolf
    CMS core database. It generates an XML file that contains all records for each
    of the Wolf CMS core database tables.
</p>
<h2><?php echo __('Creating the backup'); ?></h2>
<p>
    To create and download the backup, simply select the
    "<?php echo __('Create a backup'); ?>" option.
</p>
<p>
    By default, the download is generated in a zip file. If you want to download
    the plain unzipped XML file, go to the settings for this plugin and change
    the option there.
</p>
<h2><?php echo __('Restoring a backup'); ?></h2>
<p>
    To upload and restore a backup, simply select the
    "<?php echo __('Restore a backup'); ?>" option.
</p>
<p>
    You can set a default password to enter into any password fields if the backup
    file does not contain passwords. For this to function, the system expects there
    to be password fields in the backup file with no value.
</p>
<p>
    Example: &lt;password/&gt;
</p>
<h2><?php echo __('Notes'); ?></h2>
<p>
    Database tables that are created by plugins are currently <strong>not</strong>
    backed up through this mechanism. However, plugin settings for plugins that use the settings
    functionality provided by Wolf CMS <strong>are</strong> backed up.
</p>