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
<a href="<?php echo get_url('plugin/backup_restore/documentation'); ?>" class="button wide large"><i class="fa fa-file-text-o"></i> <?php echo __('Documentation'); ?></a>
<a href="<?php echo get_url('plugin/backup_restore/backup'); ?>" class="button wide large"><i class="fa fa-cloud-download"></i> <?php echo __('Create a backup'); ?></a>
<a href="<?php echo get_url('plugin/backup_restore/restore'); ?>" class="button wide large"><i class="fa fa-cloud-upload"> </i> <?php echo __('Restore a backup'); ?></a>
<a href="<?php echo get_url('plugin/backup_restore/settings'); ?>" class="button wide large"><i class="fa fa-gear"></i> <?php echo __('Settings'); ?></a>

<div class="box">
<h2><?php echo __('Backup/Restore plugin');?></h2>
<p>
<?php echo __('The Backup/Restore plugin allows you to create complete backups of the Wolf CMS core database.'); ?><br />
</p>
<p>
<?php echo __('Version'); ?> - <?php echo BR_VERSION; ?><br />
<?php echo __('Designed for Wolf version').' '.Plugin::getSetting('wolfversion', 'backup_restore'); ?> <?php echo __('and upwards.').'<br />'; ?>
</p>
</div>
