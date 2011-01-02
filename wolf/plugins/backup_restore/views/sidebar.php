<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages and settings to an XML file.
 *
 * @package Plugins
 * @subpackage backup_restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2009
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<p class="button"><a href="<?php echo get_url('plugin/backup_restore/documentation'); ?>"><img src="<?php echo BACKUPRESTORE_ROOT;?>/images/page.png" align="middle" alt="documentation icon" /> <?php echo __('Documentation'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/backup_restore/backup'); ?>"><img src="<?php echo BACKUPRESTORE_ROOT;?>/images/snippet.png" align="middle" alt="xml icon" /> <?php echo __('Create a backup'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/backup_restore/restore'); ?>"><img src="<?php echo BACKUPRESTORE_ROOT;?>/images/upload.png" align="middle" alt="xml icon" /> <?php echo __('Restore a backup'); ?></a></p>
<p class="button"><a href="<?php echo get_url('plugin/backup_restore/settings'); ?>"><img src="<?php echo BACKUPRESTORE_ROOT;?>/images/settings.png" align="middle" alt="settings icon" /> <?php echo __('Settings'); ?></a></p>
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
