<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages, settings and uploaded files to an XML file.
 *
 * @package Plugins
 * @subpackage backup-restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Frank Edelhaeuser <mrpace2@gmail.com>
 * @copyright Martijn van der Kleijn, 2009
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<?php 
    $max_file_size = trim(ini_get('upload_max_filesize'));
    switch (strtolower(substr($max_file_size, -1, 1))) {
        case 'g':
            $max_file_size *= 1024;
        case 'm':
            $max_file_size *= 1024;
        case 'k':
            $max_file_size *= 1024;
    }
?>
<h1><?php echo __('Restore a backup'); ?></h1>

<form action="<?php echo get_url('plugin/backup_restore/restore'); ?>" method="post" enctype="multipart/form-data">
        <fieldset style="padding: 0.5em;">
        <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Warning!'); ?></legend>
        <p>
            <?php echo __('When restoring a backup, please make sure that the backup file was generated from the same Wolf CMS <em>version</em> as you are restoring it to.'); ?>
        </p>
        <p>
            <?php echo __('Please be aware that <strong>all</strong> the database tables will be truncated when performing a restore. Truncating a table means that all records in that table are deleted.'); ?>
        </p>
        <p>
            <?php echo __('In addition, if enabled in the settings, the contents of your uploaded files directory will be erased and may be overwritten from any files contained in the backup.'); ?>
        </p>
        <p>
            <?php echo __('As such, the contents of your backup file will replace the contents of your Wolf CMS database.'); ?>
        </p>
        <p style="text-align: center;"><strong>
            <?php echo __('Do NOT upload a zip file, only upload a plain text XML file!'); ?>
        </strong></p>
    </fieldset>
    <p style="text-align: center;">
        <input name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>" type="hidden"/>
        <input name="action" value="restore" type="hidden"/>
        <input name="restoreFile" type="file" size="39"/>
        <input type="submit" value="<?php echo __('Upload plain text XML file'); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to restore?'); ?>');"/>
    </p>
</form>