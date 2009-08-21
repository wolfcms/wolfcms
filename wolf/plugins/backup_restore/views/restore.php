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
<h1><?php echo __('Restore a backup'); ?></h1>

<form action="<?php echo get_url('plugin/backup_restore/restore'); ?>" method="post" enctype="multipart/form-data">
    <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Warning!'); ?></legend>
        <p>
            When restoring a backup, please make sure that the backup file was generated from the same Wolf CMS
            <em>version</em> as you are restoring it to.
        </p>
        <p>
            Please be aware that <strong>all</strong> Wolf CMS <em>core</em> database tables will be truncated when
            performing a restore. Truncating a table means that all records in that table are deleted.
        </p>
        <p>
            As such, the contents of your backup file will replace the contents of your core Wolf CMS database tables.
        </p>
        <p style="text-align: center;"><strong>
            Do NOT upload a zip file, only upload a plain text XML file!
        </strong></p>
    </fieldset>
    <p style="text-align: center;">
        <input name="MAX_FILE_SIZE" value="1048576" type="hidden"/>
        <input name="action" value="restore" type="hidden"/>
        <input name="restoreFile" type="file" size="39"/>
        <input type="submit" value="<?php echo __('Upload plain text XML file'); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to restore?'); ?>');"/>
    </p>
</form>