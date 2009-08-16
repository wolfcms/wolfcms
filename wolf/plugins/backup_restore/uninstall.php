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

if (Plugin::deleteAllSettings('backup_restore') === false) {
    Flash::set('error', __('Unable to remove plugin settings.'));
    redirect(get_url('setting'));
}
else {
    Flash::set('success', __('Successfully uninstalled plugin.'));
    redirect(get_url('setting'));
}