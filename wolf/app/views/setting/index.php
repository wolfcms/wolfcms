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

<h1><?php echo __('Administration'); ?></h1>

<div id="admin-area" class="form-area">
    <div class="content tabs">
        <ul class="tabNavigation">
            <li class="tab"><a href="#plugins"><?php echo __('Plugins'); ?></a></li>
            <li class="tab"><a href="#settings"><?php echo __('Settings'); ?></a></li>
        </ul>

        <div class="pages">
            <div id="plugins" class="page">
                <table class="index">
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
            </div>

            <div id="settings" class="page">
                <form action="<?php echo get_url('setting'); ?>" method="post">
                    <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />
                    <table class="fieldset">
                        <tr>
                            <td class="label"><label for="setting_admin_title"><?php echo __('Admin Site title'); ?></label></td>
                            <td class="field"><input class="textbox" id="setting_admin_title" maxlength="255" name="setting[admin_title]" size="255" type="text" value="<?php echo htmlentities(Setting::get('admin_title'), ENT_COMPAT, 'UTF-8'); ?>" /></td>
                            <td class="help"><?php echo __('By using <strong>&lt;img src="img_path" /&gt;</strong> you can set your company logo instead of a title.'); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_admin_email"><?php echo __('Site email'); ?></label></td>
                            <td class="field"><input class="textbox" id="setting_admin_email" maxlength="255" name="setting[admin_email]" size="255" type="text" value="<?php echo Setting::get('admin_email'); ?>" /></td>
                            <td class="help"><?php echo __('When emails are sent by Wolf CMS, this email address will be used as the sender. Default: do-not-reply@wolfcms.org'); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_language"><?php echo __('Language'); ?></label></td>
                            <td class="field">
                                <select class="select" id="setting_language" name="setting[language]">
                                    <?php
                                        $current_language = Setting::get('language');
                                        foreach (Setting::getLanguages() as $code => $label): ?>
                                    <option value="<?php echo $code; ?>"<?php if ($code == $current_language) echo ' selected="selected"'; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="help"><?php echo __('This will set your language for the backend.'); ?><br /><?php echo __('Help us <a href=":url">translate Wolf</a>!', array(':url' => 'http://www.wolfcms.org/wiki/translator_notes')); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_theme"><?php echo __('Administration Theme'); ?></label></td>
                            <td class="field">
                                <select class="select" id="setting_theme" name="setting[theme]">
                                    <?php
                                        $current_theme = Setting::get('theme');
                                        foreach (Setting::getThemes() as $code => $label): ?>
                                    <option value="<?php echo $code; ?>"<?php if ($code == $current_theme) echo ' selected="selected"'; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="help"><?php echo __('This will change your Administration theme.'); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_default_tab"><?php echo __('Default tab'); ?></label></td>
                            <td class="field">
                                <select class="select" id="setting_default_tab" name="setting[default_tab]">
                                    <?php $current_default_tab = Setting::get('default_tab');?>
                                    <option value="page"<?php if ($current_default_tab == 'page') echo ' selected="selected"'; ?>><?php echo __('Pages'); ?></option>
                                    <option value="snippet"<?php if ($current_default_tab == 'snippet') echo ' selected="selected"'; ?>><?php echo __('MSG_SNIPPETS'); ?></option>
                                    <option value="layout"<?php if ($current_default_tab == 'layout') echo ' selected="selected"'; ?>><?php echo __('Layouts'); ?></option>
                                    <option value="user"<?php if ($current_default_tab == 'user') echo ' selected="selected"'; ?>><?php echo __('Users'); ?></option>
                                    <option value="setting"<?php if ($current_default_tab == 'setting') echo ' selected="selected"'; ?>><?php echo __('Administration'); ?></option>
                                    <?php
                                        foreach(Plugin::$controllers as $key=>$controller):
                                            if (Plugin::isEnabled($key) && $controller->show_tab === true) { ?>
                                    <option value="plugin/<?php echo $key; ?>"<?php if ('plugin/'.$key == $current_default_tab) echo ' selected="selected"'; ?>><?php echo $controller->label; ?></option>
                                    <?php   }
                                        endforeach; ?>
                                </select>
                            </td>
                            <td class="help"><?php echo __('This allows you to specify which tab (controller) you will see by default after login.'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3"><h3><?php echo __('Page options'); ?></h3></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_allow_html_title"><?php echo __('Allow HTML in Title'); ?></label></td>
                            <td class="field">
                                <input type="checkbox" id="setting_allow_html_title" name="setting[allow_html_title]" <?php if (Setting::get('allow_html_title') == 'on') echo ' checked="checked"'; ?> />
                            </td>
                            <td class="help"><?php echo __('Determines whether or not HTML code is allowed in a page\'s title.'); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_default_status_id-draft"><?php echo __('Default Status'); ?></label></td>
                            <td class="field">
                                <input class="radio" id="setting_default_status_id-draft" name="setting[default_status_id]" size="10" type="radio" value="<?php echo Page::STATUS_DRAFT; ?>"<?php if (Setting::get('default_status_id') == Page::STATUS_DRAFT) echo ' checked="checked"'; ?> /><label for="setting_default_status_id-draft"> <?php echo __('Draft'); ?> </label> &nbsp;
                                <input class="radio" id="setting_default_status_id-published" name="setting[default_status_id]" size="10" type="radio" value="<?php echo Page::STATUS_PUBLISHED; ?>"<?php if (Setting::get('default_status_id') == Page::STATUS_PUBLISHED) echo ' checked="checked"'; ?> /><label for="setting_default_status_id-published"> <?php echo __('Published'); ?> </label>
                            </td>
                            <td class="help">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="setting_default_filter_id"><?php echo __('Default Filter'); ?></label></td>
                            <td class="field">
                                <select class="select" id="setting_default_filter_id" name="setting[default_filter_id]">
                                <?php $current_default_filter_id = Setting::get('default_filter_id'); ?>
                                    <option value=""<?php if ($current_default_filter_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
                                <?php
                                    foreach (Filter::findAll() as $filter_id):
                                        if (isset($loaded_filters[$filter_id])): ?>
                                    <option value="<?php echo $filter_id; ?>"<?php if ($filter_id == $current_default_filter_id) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($filter_id); ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="help"><?php echo __('Only for filter in pages, NOT in snippets'); ?></td>
                        </tr>
                    </table>

                    <p class="buttons">
                        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// <![CDATA[

function toSentenceCase(s) {
  return s.toLowerCase().replace(/^(.)|\s(.)/g,
          function($1) { return $1.toUpperCase(); });
}

function toLabelCase(s) {
  return s.toLowerCase().replace(/^(.)|\s(.)|_(.)/g,
          function($1) { return $1.toUpperCase(); });
}


$(document).ready(function() {

    // Setup tabs
    $(function () {
        var tabContainers = $('div.tabs > div.pages > div');

        $('div.tabs ul.tabNavigation a').click(function () {
            tabContainers.hide().filter(this.hash).show();

            $('div.tabs ul.tabNavigation a').removeClass('here');
            $(this).addClass('here');

            return false;
        }).filter(':first').click();
    });

    // Dynamically change look-and-feel
    $('#setting_theme').change(function() {
        $('#css_theme').attr({href : 'wolf/admin/themes/' + this.value + '/styles.css'});
    });

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

// ]]>
</script>
