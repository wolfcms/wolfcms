<?php
/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package frog
 * @subpackage views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */
?>
<h1><?php echo __('Administration'); ?></h1>

<div class="form-area">
<div id="tab-control-admin" class="tab_control">
    <div id="tabs-admin" class="tabs">
        <div id="tab-admin-toolbar" class="tab_toolbar">&nbsp;</div>
    </div>
    <div id="admin-pages" class="pages">

        <div id="plugin-page" class="page">

<table id="plugins" class="index" cellpadding="0" cellspacing="0" border="0">
  <thead>
    <tr>
      <th class="plugin"><?php echo __('Plugin'); ?></th>
      <th class="pluginSettings"><?php echo __('Settings'); ?></th>
      <th class="website"><?php echo __('Website'); ?></th>
      <th class="version"><?php echo __('Version'); ?></th>
      <th class="latest"><?php echo __('Latest'); ?></th>
      <th class="enabled"><?php echo __('Enabled'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php $loaded_plugins = Plugin::$plugins; ?>
<?php $loaded_filters = Filter::$filters; ?>
<?php foreach(Plugin::findAll() as $plugin): ?>
    <?php $disabled = (isset($plugin->require_frog_version) and $plugin->require_frog_version > FROG_VERSION); ?>
    <tr<?php if ($disabled) echo ' class="disabled"'; ?>>
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
        <p><?php echo $plugin->description; ?> <?php if ($disabled) echo '<span class="notes">'.__('This plugin CANNOT be enabled! It requires Frog version :v.', array(':v' => $plugin->require_frog_version)).'</span>'; ?></p>
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
      <td class="enabled"><input type="checkbox" name="enabled_<?php echo $plugin->id; ?>" value="<?php echo $plugin->id; ?>"<?php if (isset($loaded_plugins[$plugin->id])) echo ' checked="checked"'; if ($disabled) echo ' disabled="disabled"'; ?> onclick="new Ajax.Request('<?php echo get_url('setting'); ?>'+(this.checked ? '/activate_plugin/':'/deactivate_plugin/')+this.value, {method: 'get'});" /></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<script type="text/javascript">
  new RuledTable('plugins');
</script>

        </div>
        <div id="setting-page" class="page">
            
<form action="<?php echo get_url('setting'); ?>" method="post">
  <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td class="label"><label for="setting_admin_title"><?php echo __('Admin Site title'); ?></label></td>
        <td class="field"><input class="textbox" id="setting_admin_title" maxlength="255" name="setting[admin_title]" size="255" type="text" value="<?php echo htmlentities(Setting::get('admin_title'), ENT_COMPAT, 'UTF-8'); ?>" /></td>
        <td class="help"><?php echo __('By using <strong>&lt;img src="img_path" /&gt;</strong> you can set your company logo instead of a title.'); ?></td>
      </tr>
      <tr>
        <td class="label"><label for="setting_language"><?php echo __('Language'); ?></label></td>
        <td class="field">
          <select class="select" id="setting_language" name="setting[language]">
<?php $current_language = Setting::get('language'); ?>
<?php foreach (Setting::getLanguages() as $code => $label): ?>
            <option value="<?php echo $code; ?>"<?php if ($code == $current_language) echo ' selected="selected"'; ?>><?php echo __($label); ?></option>
<?php endforeach; ?>
          </select>
        </td>
        <td class="help"><?php echo __('This will set your language for the backend.'); ?><br /><?php echo __('Help us <a href=":url">translate Frog</a>!', array(':url' => get_url('translate'))); ?></td>
      </tr>
      <tr>
        <td class="label"><label for="setting_theme"><?php echo __('Administration Theme'); ?></label></td>
        <td class="field">
          <select class="select" id="setting_language" name="setting[theme]" onchange="$('css_theme').href = 'themes/' + this[this.selectedIndex].value + '/styles.css';">
<?php $current_theme = Setting::get('theme'); ?>
<?php foreach (Setting::getThemes() as $code => $label): ?>
            <option value="<?php echo $code; ?>"<?php if ($code == $current_theme) echo ' selected="selected"'; ?>><?php echo __($label); ?></option>
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
            <option value="snippet"<?php if ($current_default_tab == 'snippet') echo ' selected="selected"'; ?>><?php echo __('Snippets'); ?></option>
            <option value="layout"<?php if ($current_default_tab == 'layout') echo ' selected="selected"'; ?>><?php echo __('Layouts'); ?></option>
            <option value="user"<?php if ($current_default_tab == 'user') echo ' selected="selected"'; ?>><?php echo __('Users'); ?></option>
            <option value="setting"<?php if ($current_default_tab == 'setting') echo ' selected="selected"'; ?>><?php echo __('Administration'); ?></option>
<?php foreach(Plugin::$controllers as $key=>$controller): ?>
        <?php if ($controller->show_tab === true) { ?>
            <option value="plugin/<?php echo $key; ?>"<?php if ('plugin/'.$key == $current_default_tab) echo ' selected="selected"'; ?>><?php echo $controller->label; ?></option>
        <?php } ?>
<?php endforeach; ?>
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
          <input type="checkbox" name="setting[allow_html_title]" <?php if (Setting::get('allow_html_title') == 'on') echo ' checked="checked"'; ?> />
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
<?php foreach (Filter::findAll() as $filter_id): ?>
<?php if (isset($loaded_filters[$filter_id])): ?>
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

<script type="text/javascript">
  Field.activate('user_name');
</script>

        </div>
    </div>
</div>
</div>
<script type="text/javascript">
  var tabControlMeta = new TabControl('tab-control-admin');
  tabControlMeta.addTab('tab-plugin', '<?php echo __('Plugins'); ?>', 'plugin-page');
  tabControlMeta.addTab('tab-setting', '<?php echo __('Settings'); ?>', 'setting-page');
  tabControlMeta.select(tabControlMeta.firstTab());
</script>