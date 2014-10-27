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

<h2><?php echo __('Administration'); ?></h2>

<div class="panel panel-default" id="settings">
    <form role="form" action="<?php echo get_url('setting'); ?>" method="post">
        <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />

        <div class="panel-body">

            <h3><?php echo __('Settings'); ?></h3>

            <!-- Admin site title -->
            <div class="form-group">
                <label for="setting_admin_title"><?php echo __('Admin Site title'); ?></label>
                <input class="form-control" id="setting_admin_title" name="setting[admin_title]" type="text" value="<?php echo htmlentities(Setting::get('admin_title'), ENT_COMPAT, 'UTF-8'); ?>" />
                <span class="help-block"><?php echo __('By using <strong>&lt;img src="img_path" /&gt;</strong> you can set your company logo instead of a title.'); ?></span>
            </div>
            <!-- Admin site email -->
            <div class="form-group">
                <label for="setting_admin_email"><?php echo __('Site email'); ?></label>
                <input class="form-control" id="setting_admin_email" maxlength="255" name="setting[admin_email]" size="255" type="email" value="<?php echo Setting::get('admin_email'); ?>" />
                <span class="help-block"><?php echo __('When emails are sent by Wolf CMS, this email address will be used as the sender. Default: do-not-reply@wolfcms.org'); ?></span>
            </div>
            <!-- Admin language -->
            <div class="form-group">
                <label for="setting_language"><?php echo __('Language'); ?></label>
                <select class="select form-control" id="setting_language" name="setting[language]">
                    <?php
                        $current_language = Setting::get('language');
                        foreach (Setting::getLanguages() as $code => $label): ?>
                    <option value="<?php echo $code; ?>"<?php if ($code == $current_language) echo ' selected="selected"'; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="help-block"><?php echo __('This will set your language for the backend.'); ?><?php echo __('Help us <a href=":url">translate Wolf</a>!', array(':url' => 'http://www.wolfcms.org/wiki/translator_notes')); ?></span>
            </div>
            <!-- Admin theme -->
            <div class="form-group">
                <label for="setting_theme"><?php echo __('Administration Theme'); ?></label>
                <select class="select form-control" id="setting_theme" name="setting[theme]">
                    <?php
                        $current_theme = Setting::get('theme');
                        foreach (Setting::getThemes() as $code => $label): ?>
                    <option value="<?php echo $code; ?>"<?php if ($code == $current_theme) echo ' selected="selected"'; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="help-block"><?php echo __('This will change your Administration theme.'); ?></span>
            </div>
            <!-- Default tab (controller) -->
            <div class="form-group">
                <label for="setting_default_tab"><?php echo __('Default tab'); ?></label>
                <select class="select form-control" id="setting_default_tab" name="setting[default_tab]">
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
                <span class="help-block"><?php echo __('This allows you to specify which tab (controller) you will see by default after login.'); ?></span>
            </div>
            <!-- Page options -->
            <h3><?php echo __('Page options'); ?></h3>
            <!-- Allow HTML in title -->
            <div class="checkbox">
                <span><?php echo __('Allow HTML in Title'); ?></span>
                <label for="setting_allow_html_title">
                    <input type="checkbox" id="setting_allow_html_title" name="setting[allow_html_title]" <?php if (Setting::get('allow_html_title') == 'on') echo ' checked="checked"'; ?> />
                    <?php echo __('Determines whether or not HTML code is allowed in a page\'s title.'); ?>
                </label>
            </div>
            <!-- Default Page status -->
            <h4><?php echo __('Default Status'); ?></h4>

            <label for="setting_default_status_id-draft" class="radio-inline">
                <input id="setting_default_status_id-draft" name="setting[default_status_id]" size="10" type="radio" value="<?php echo Page::STATUS_DRAFT; ?>"<?php if (Setting::get('default_status_id') == Page::STATUS_DRAFT) echo ' checked="checked"'; ?> />
                <?php echo __('Draft'); ?>
            </label>
            <label for="setting_default_status_id-published" class="radio-inline">
                <input id="setting_default_status_id-published" name="setting[default_status_id]" size="10" type="radio" value="<?php echo Page::STATUS_PUBLISHED; ?>"<?php if (Setting::get('default_status_id') == Page::STATUS_PUBLISHED) echo ' checked="checked"'; ?> />
                <?php echo __('Published'); ?>
            </label>
            <!-- Default text filter -->
            <div class="form-group">
                <label for="setting_default_filter_id"><?php echo __('Default Filter'); ?></label>
                <select class="select form-control" id="setting_default_filter_id" name="setting[default_filter_id]">
                <?php $current_default_filter_id = Setting::get('default_filter_id'); ?>
                    <option value=""<?php if ($current_default_filter_id == '') echo ' selected="selected"'; ?>>&#8212; <?php echo __('none'); ?> &#8212;</option>
                <?php
                    foreach (Filter::findAll() as $filter_id):
                        if (isset($loaded_filters[$filter_id])): ?>
                    <option value="<?php echo $filter_id; ?>"<?php if ($filter_id == $current_default_filter_id) echo ' selected="selected"'; ?>><?php echo Inflector::humanize($filter_id); ?></option>
                <?php endif; ?>
                <?php endforeach; ?>
                </select>
                <span class="help-block"><?php echo __('Only for filter in pages, NOT in snippets'); ?></span>
            </div>
            

            <p>
                <button class="btn btn-primary" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>"><?php echo __('Save'); ?></button>
            </p>


        </div> <!-- # .panel-body -->

    </form>    
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
