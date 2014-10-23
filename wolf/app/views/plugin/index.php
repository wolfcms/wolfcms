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
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2010
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
$current_language = Setting::get('language');
?>

<h2><?php echo __('Plugins'); ?></h2>

<div class="panel panel-default" id="plugins">
    <div id="plugins" class="page">
        <table class="table table-hover table-striped table-responsive">
            <thead>

            <th class="plugin-list-name"><?php echo __('Plugin'); ?></th>
            <th class="plugin-list-settings"><?php echo __('Settings'); ?></th>
            <th class="plugin-list-website"><?php echo __('Website'); ?></th>
            <th class="plugin-list-version"><?php echo __('Version'); ?></th>
            <th class="plugin-list-latest"><?php echo __('Latest'); ?></th>
            <th class="plugin-list-enabled"><?php echo __('Enabled'); ?></th>
            <th class="plugin-list-uninst"><?php echo __('Uninstall'); ?></th>

            </thead>
            <tbody>
                <?php
                $loaded_plugins   = Plugin::$plugins;
                $loaded_filters   = Filter::$filters;
                foreach ( Plugin::findAll() as $plugin ):
                    $errors   = array();
                    $disabled = !Plugin::hasPrerequisites($plugin, $errors);
                    $rowClass = '';
                    if ( $disabled === true ) {
                        $rowClass = 'danger';
                    } elseif ( isset($loaded_plugins[$plugin->id]) ) {
                        $rowClass = 'success';
                    }
                    ?>
                    <tr<?php echo (!empty($rowClass)) ? ' class="' . $rowClass . '"' : ''; ?>>
                        <td class="plugin-list-name">
                            <h4 class="plugin-list-title">
                                <?php if ( isset($loaded_plugins[$plugin->id]) && Plugin::hasDocumentationPage($plugin->id) ): ?>
                                    <a href="<?php echo get_url('plugin/' . $plugin->id . '/documentation'); ?>"><?php echo $plugin->title; ?></a>
                                <?php else: ?>
                                    <?php echo $plugin->title; ?>
                                <?php endif; ?>
                                <span class="plugin-list-author">
                                    <?php if ( isset($plugin->author) ) echo ' ' . __('by') . ' ' . $plugin->author; ?>
                                </span>
                            </h4>
                            <p class="plugin-list-description">
                                <?php echo $plugin->description; ?>
                            </p>
                            <div class="notes">
                                <?php if ( $disabled === true ) echo '<span class="notes">' . __('This plugin CANNOT be enabled!<br/>') . implode('<br/>', $errors) . '</span>'; ?>
                            </div>
                        </td>
                        <td class="plugin-list-settings">
                            <?php if ( isset($loaded_plugins[$plugin->id]) && Plugin::hasSettingsPage($plugin->id) ): ?>
                                <a href="<?php echo get_url('plugin/' . $plugin->id . '/settings'); ?>"><?php echo __('Settings'); ?></a>
                            <?php else: ?>
                                <?php echo __('n/a'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="plugin-list-website">
                            <a href="<?php echo $plugin->website; ?>" target="_blank"><?php echo __('Website') ?></a>
                        </td>
                        <td class="plugin-list-version">
                            <?php echo $plugin->version; ?>
                        </td>
                        <td class="plugin-list-latest">
                            <?php echo Plugin::checkLatest($plugin); ?>
                        </td>
                        <td class="plugin-list-enabled enabled">
                            <input type="checkbox" name="enabled_<?php echo $plugin->id; ?>" value="<?php echo $plugin->id; ?>"<?php if ( isset($loaded_plugins[$plugin->id]) ) echo ' checked="checked"'; if ( $disabled ) echo ' disabled="disabled"'; ?> />
                        </td>
                        <td class="plugin-list-uninst">
                            <a href="<?php echo get_url('setting'); ?>" name="uninstall_<?php echo $plugin->id; ?>"><?php echo __('Uninstall'); ?></a>
                        </td>

                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
