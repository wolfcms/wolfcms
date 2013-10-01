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
 * @copyright Martijn van der Kleijn, 2009
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<h1><?php echo __('Documentation'); ?></h1>
<p>
    <?php echo __('The Backup/Restore plugin allows you to create complete backups of the Wolf CMS database. It generates an XML file that contains all records for each of the Wolf CMS database tables, and optionally all uploaded files.'); ?>
</p>
<h2><?php echo __('Creating the backup'); ?></h2>
<p>
    <?php echo __('To create and download the backup, simply select the "Create a backup" option.'); ?>
</p>
<p>
    <?php echo __('By default, the download is generated in a zip file. If you want to download the plain unzipped XML file, go to the settings for this plugin and change the option there.'); ?>
</p>
<h2><?php echo __('Restoring a backup'); ?></h2>
<p>
    <?php echo __('To upload and restore a backup, simply select the "Restore a backup" option.'); ?>
</p>
<p>
    <?php echo __('You can set a default password to enter into any password fields if the backup file does not contain passwords. For this to function, the system expects there to be password fields in the backup file with no value.'); ?>
</p>
<p>
    <?php echo __('Example:'); ?> &lt;password/&gt;
</p>