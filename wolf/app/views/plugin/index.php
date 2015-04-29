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
?>

<h1><?php echo __('Plugins'); ?></h1>

<table id="plugins">
    <thead>
        <tr>
            <th class="plugin"><?php echo __('Plugin'); ?></th>
            <th class="pluginSettings"><?php echo __('Settings'); ?></th>
            <th class="website"><?php echo __('Website'); ?></th>
            <th class="version"><?php echo __('Version'); ?></th>
            <th class="latest"><?php echo __('Latest'); ?></th>
            <th class="enabled"><?php echo __('Enabled'); ?></th>
            <th class="enabled"><?php echo __('Uninstall'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $loaded_plugins = Plugin::$plugins;
            $loaded_filters = Filter::$filters;
            foreach(Plugin::findAll() as $plugin):
                $errors = array();
                $disabled = !Plugin::hasPrerequisites($plugin, $errors);
        ?>
        <tr<?php if ($disabled === true) echo ' class="disabled"'; ?>>
            <td class="plugin">
                <h4>
                <?php
                    if (isset($loaded_plugins[$plugin->id]) && Plugin::hasDocumentationPage($plugin->id) )
                        echo '<a href="'.get_url('plugin/'.$plugin->id.'/documentation').'">'.$plugin->title.'</a>';
                    else
                        echo $plugin->title;
                ?>
                    <span class="from"><?php if (isset($plugin->author)) echo ' '.__('by').' '.$plugin->author; ?></span>
                </h4>
                <p><?php echo $plugin->description; ?> <?php if ($disabled === true) echo '<span class="notes">'.__('This plugin CANNOT be enabled!<br/>').implode('<br/>', $errors).'</span>'; ?></p>
            </td>
            <td class="pluginSettings">
                <?php
                    if (isset($loaded_plugins[$plugin->id]) && Plugin::hasSettingsPage($plugin->id) )
                        echo '<a href="'.get_url('plugin/'.$plugin->id.'/settings').'">'.__('Settings').'</a>';
                    else
                        echo __('n/a');
                ?>
            </td>
            <td class="website"><a href="<?php echo $plugin->website; ?>" target="_blank"><?php echo __('Website') ?></a></td>
            <td class="version"><?php echo $plugin->version; ?></td>
            <td class="latest"><?php echo Plugin::checkLatest($plugin); ?></td>
            <td class="enabled"><input type="checkbox" name="enabled_<?php echo $plugin->id; ?>" value="<?php echo $plugin->id; ?>"<?php if (isset($loaded_plugins[$plugin->id])) echo ' checked="checked"'; if ($disabled) echo ' disabled="disabled"'; ?> /></td>
            <td class="uninstall"><a href="<?php echo get_url('setting'); ?>" name="uninstall_<?php echo $plugin->id; ?>"><?php echo __('Uninstall'); ?></a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<script>
    $(document).ready(function () {
        // Dynamically change enabled state
        $('.enabled input').change(function() {
            $.get('<?php echo get_url('setting'); ?>'+(this.checked ? '/activate_plugin/':'/deactivate_plugin/')+this.value, function(){
                location.reload(true);
            });
        });

        // Dynamically uninstall
        $('.uninstall a').click(function(e) {
            if (confirm('<?php echo jsEscape(__('Are you sure you wish to uninstall this plugin?')); ?>')) {
                var pluginId = this.name.replace('uninstall_', '');
                $.get('<?php echo get_url('setting/uninstall_plugin/'); ?>'+pluginId, function() {
                    location.reload(true);
                });
            }
            e.preventDefault();
        });
    });
</script>