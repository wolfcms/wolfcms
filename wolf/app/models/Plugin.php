<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008,2009,2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 *
 * @copyright Martijn van der Kleijn 2008-2010
 * @copyright Philippe Archambault 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * class Plugin
 *
 * Provide a Plugin API to make wolf more flexible
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 */
class Plugin {
    static $plugins = array();
    static $plugins_infos = array();
    static $updatefile_cache = array();

    static $controllers = array();
    static $javascripts = array();
    static $stylesheets = array();

    /**
     * Initialize all activated plugin by including is index.php file.
     * Also load all language files for plugins available in plugins directory.
     */
    static function init() {
        $dir = PLUGINS_ROOT.DS;

        if ($handle = opendir($dir)) {
            while (false !== ($plugin_id = readdir($handle))) {
                $file = $dir.$plugin_id.DS.'i18n'.DS.I18n::getLocale().'-message.php';
                $default_file = PLUGINS_ROOT.DS.$plugin_id.DS.'i18n'.DS.DEFAULT_LOCALE.'-message.php';
                
                if (file_exists($file)) {
                    $array = include $file;
                    I18n::add($array);
                }

                if (file_exists($default_file)) {
                    $array = include $default_file;
                    I18n::addDefault($array);
                }
            }
        }
        
        self::$plugins = unserialize(Setting::get('plugins'));
        foreach (self::$plugins as $plugin_id => $tmp) {
            $file = PLUGINS_ROOT.DS.$plugin_id.DS.'index.php';
            if (file_exists($file))
                include $file;
        }
    }

    /**
     * Sets plugin information. Parameters include:
     *
     * Mandatory
     * - id,
     * - title,
     * - description,
     * - author,
     * - version,
     *
     * Optional
     * - license,
     * - update_url,
     * - require_wolf_version,
     * - require_php_extensions,
     * - website
     *
     * @param infos array Assoc array with plugin informations
     */
    static function setInfos($infos) {
        if (!isset($infos['type']) && defined('CMS_BACKEND')) {
            self::$plugins_infos[$infos['id']] = (object) $infos;
            return;
        }
        else if (!isset($infos['type'])) {
            return;
        }

        if (defined('CMS_BACKEND') && ($infos['type'] == 'backend' || $infos['type'] == 'both')) {
            self::$plugins_infos[$infos['id']] = (object) $infos;
            return;
        }

        if (!defined('CMS_BACKEND') && ($infos['type'] == 'frontend' || $infos['type'] == 'both')) {
            self::$plugins_infos[$infos['id']] = (object) $infos;
            return;
        }
    }

    /**
     * Activate a plugin. This will execute the enable.php file of the plugin
     * when found.
     *
     * @param plugin_id string	The plugin name to activate
     */
    static function activate($plugin_id) {
        self::$plugins[$plugin_id] = 1;
        self::save();

        $file = PLUGINS_ROOT.'/'.$plugin_id.'/enable.php';
        if (file_exists($file))
            include $file;

        // TODO Check if we actually need this, gets rid of E_NOTICE for now
        if (isset(self::$controllers[$plugin_id])) {
            $class_name = Inflector::camelize($plugin_id).'Controller';
            AutoLoader::addFile($class_name, self::$controllers[$plugin_id]->file);
        }
    }

    /**
     * Deactivate a plugin
     *
     * @param plugin_id string	The plugin name to deactivate
     */
    static function deactivate($plugin_id) {
        if (isset(self::$plugins[$plugin_id])) {
            unset(self::$plugins[$plugin_id]);
            self::save();

            $file = PLUGINS_ROOT.'/'.$plugin_id.'/disable.php';
            if (file_exists($file))
                include $file;
        }
    }

    /**
     * Uninstall a plugin
     *
     * @param plugin_id string	The plugin name to uninstall
     */
    static function uninstall($plugin_id) {
        if (isset(self::$plugins[$plugin_id])) {
            unset(self::$plugins[$plugin_id]);
            self::save();
        }

        $file = PLUGINS_ROOT.'/'.$plugin_id.'/uninstall.php';
        if (file_exists($file)) {
            include $file;
        }
    }

    /**
     * Save activated plugins to the setting 'plugins'
     */
    static function save() {
        Setting::saveFromData(array('plugins' => serialize(self::$plugins)));
    }

    /**
     * Find all plugins installed in the plugin folder
     *
     * @return array
     */
    static function findAll() {
        $dir = PLUGINS_ROOT.'/';

        if ($handle = opendir($dir)) {
            while (false !== ($plugin_id = readdir($handle))) {
                if ( ! isset(self::$plugins[$plugin_id]) && is_dir($dir.$plugin_id) && strpos($plugin_id, '.') !== 0) {
                    $file = PLUGINS_ROOT.'/'.$plugin_id.'/index.php';
                    if (file_exists($file))
                        include $file;
                }
            }
            closedir($handle);
        }

        ksort(self::$plugins_infos);
        return self::$plugins_infos;
    }
    
    /**
     * Given a plugin, checks a number of prerequisites as specified in plugin's setInfos().
     *
     * Possible checks:
     *
     * - require_wolf_version (a valid Wolf CMS version number)
     * - require_php_extensions (comma seperated list of required extensions)
     */
    public static function hasPrerequisites($plugin, &$errors=array()) {
        // Check require_wolf_version
        if (isset($plugin->require_wolf_version) && version_compare($plugin->require_wolf_version, CMS_VERSION, '>')) {
            $errors[] = __('The plugin requires a minimum of Wolf CMS version :v.', array(':v' => $plugin->require_wolf_version));
        }
        
        // Check require_php_extension
        if (isset($plugin->require_php_extensions)) {
            $exts = explode(',', $plugin->require_php_extensions);
            if(!empty($exts)) {
                foreach ($exts as $ext) {
                    if (trim($ext) !== '' && !extension_loaded($ext)) {
                        $errors[] = __('One or more required PHP extension is missing: :exts', array(':exts', $plugin->require_php_extentions));
                    }
                }
            }
        }
        
        if (count($errors) > 0)
            return false;
        else return true;
    }

    /**
     * Check the file mentioned as update_url for the latest plugin version available.
     * Messages that can be returned:
     * unknown - returned if the plugin doesn't provide an update url
     * latest  - returned if the plugin version matches the version number registerd at the url
     * error   - returned if the update url could not be reached or for any other reason
     *
     * @param plugin     object A plugin object.
     *
     * @return           string The latest version number or a localized message.
     */
    static function checkLatest($plugin) {
        $data = null;

        if (!defined('CHECK_UPDATES') || !CHECK_UPDATES)
            return __('unknown');

        // Check if plugin has update file url set
        if ( ! isset($plugin->update_url) )
            return __('unknown');

        // Check if update file was already cached and is no older than 30 minutes
        if (array_key_exists($plugin->update_url, Plugin::$updatefile_cache) && (Plugin::$updatefile_cache[$plugin->update_url]['time'] + 30 * 60) < time()) {
            unset(Plugin::$updatefile_cache[$plugin->update_url]);
        }

        if (!array_key_exists($plugin->update_url, Plugin::$updatefile_cache)) {
            // Read and cache the update file
            if ( ! $data = getContentFromUrl($plugin->update_url)) {
                return __('error');
            }
            Plugin::$updatefile_cache[$plugin->update_url] = array('time' => time(), 'data' => $data);
        }

        $xml = simplexml_load_string(Plugin::$updatefile_cache[$plugin->update_url]['data']);

        foreach($xml as $node) {
            if ($plugin->id == $node->id)
                if ($plugin->version == $node->version)
                    return __('latest');
                else
                    return (string) $node->version;
        }

        return __('error');
    }


    /**
     * Add a controller (tab) to the administration
     *
     * @param plugin_id     string  The folder name of the plugin
     * @param label         string  The tab label
     * @param permissions   string  List of roles that will have the tab displayed
     *                              separate by coma ie: 'administrator,developer'
     * @param show_tab      boolean Either 'true' or 'false'. Defaults to true.
     *
     * @return void
     */
    static function addController($plugin_id, $label, $permissions=false, $show_tab=true) {
        if (!isset(self::$plugins_infos[$plugin_id])) return;

        $class_name = Inflector::camelize($plugin_id).'Controller';
        $file = PLUGINS_ROOT.'/'.$plugin_id.'/'.$class_name.'.php';

        if (!file_exists($file)) {
            if (defined('DEBUG') && DEBUG)
                throw new Exception('Plugin controller file not found: '.$file);
            return false;
        }

        self::$controllers[$plugin_id] = (object) array(
            'label' => ucfirst($label),
            'class_name' => $class_name,
            'file'	=> $file,
            'permissions' => $permissions,
            'show_tab' => $show_tab
        );

        AutoLoader::addFile($class_name, self::$controllers[$plugin_id]->file);

        return true;
    }


    /**
     * Add a javascript file to be added to the html page for a plugin.
     * Backend only right now.
     *
     * @param $plugin_id    string  The folder name of the plugin
     * @param $file         string  The path to the javascript file relative to plugin root
     */
    static function addJavascript($plugin_id, $file) {
        if (file_exists(PLUGINS_ROOT.'/' . $plugin_id . '/' . $file)) {
            self::$javascripts[] = $plugin_id.'/'.$file;
        }
    }
    
    /**
     * Add a stylesheet file to be added to the html page for a plugin.
     * Backend only right now.
     *
     * @param $plugin_id    string  The folder name of the plugin
     * @param $file         string  The path to the stylesheet file relative to plugin root
     */
    static function addStylesheet($plugin_id, $file) {
        if (file_exists(PLUGINS_ROOT.'/' . $plugin_id . '/' . $file)) {
            self::$stylesheets[] = $plugin_id.'/'.$file;
        }
    }


    static function hasSettingsPage($plugin_id) {
        $class_name = Inflector::camelize($plugin_id).'Controller';

        return (array_key_exists($plugin_id, Plugin::$controllers) && method_exists($class_name, 'settings'));
    }


    static function hasDocumentationPage($plugin_id) {
        $class_name = Inflector::camelize($plugin_id).'Controller';

        return (array_key_exists($plugin_id, Plugin::$controllers) && method_exists($class_name, 'documentation'));
    }

    /**
     * Returns true if a plugin is enabled for use.
     *
     * @param string $plugin_id
     */
    static function isEnabled($plugin_id) {
        if (array_key_exists($plugin_id, Plugin::$plugins) && Plugin::$plugins[$plugin_id] == 1)
            return true;
        else
            return false;
    }

    /**
     * Returns true when all settings for $plugin_id where deleted.
     *
     * @global constant $__CMS_CONN__
     * @param string    $plugin_id
     * @return boolean  True when successful
     */
    static function deleteAllSettings($plugin_id) {
        if ($plugin_id === null || $plugin_id === '') return false;

        $tablename = TABLE_PREFIX.'plugin_settings';

        $sql = "DELETE FROM $tablename WHERE plugin_id=:pluginid";

        Record::logQuery($sql);

        $stmt = Record::getConnection()->prepare($sql);

        return $stmt->execute(array(':pluginid' => $plugin_id));
    }


    /**
     * Stores all settings from a name<->value pair array in the database.
     *
     * @param array $settings Array of name-value pairs
     * @param string $plugin_id     The folder name of the plugin
     * @return bool Returns true if successful otherwise returns false.
     */
    static function setAllSettings($array, $plugin_id) {

        // Perform sanity checks
        if (!is_array($array) || !is_string($plugin_id)) return false;
        if (empty($array) || empty($plugin_id)) return false;

        $tablename = TABLE_PREFIX.'plugin_settings';

        $existingSettings = array();

        $sql = "SELECT name FROM $tablename WHERE plugin_id=:pluginid";

        Record::logQuery($sql);

        $stmt = Record::getConnection()->prepare($sql);

        $stmt->execute(array(':pluginid' => $plugin_id));

        while ($settingname = $stmt->fetchColumn())
            $existingSettings[$settingname] = $settingname;

        $ret = false;

        foreach ($array as $name => $value) {
            if (array_key_exists($name, $existingSettings)) {
                $sql = "UPDATE $tablename SET value=:value WHERE name=:name AND plugin_id=:pluginid";
            }
            else {
                $sql = "INSERT INTO $tablename (value, name, plugin_id) VALUES (:value, :name, :pluginid)";
            }

            Record::logQuery($sql);

            $stmt = Record::getConnection()->prepare($sql);

            $ret = $stmt->execute(array(':pluginid' => $plugin_id, ':name' => $name, ':value' => $value));
        }

        return $ret;
    }

    /**
     * Allows you to store a single setting in the database.
     *
     * @param string $name          Setting name
     * @param string $value         Setting value
     * @param string $plugin_id     Plugin folder name
     * @return bool Returns true upon success otherwise false.
     */
    static function setSetting($name, $value, $plugin_id) {

        // Perform sanity checks
        if (!is_string($name) || !is_string($value) || !is_string($plugin_id)) return false;
        if (empty($name) || empty($value) || empty($plugin_id)) return false;

        $tablename = TABLE_PREFIX.'plugin_settings';

        $existingSettings = array();

        $sql = "SELECT name FROM $tablename WHERE plugin_id=:pluginid";

        Record::logQuery($sql);

        $stmt = Record::getConnection()->prepare($sql);

        $stmt->execute(array(':pluginid' => $plugin_id));

        while ($settingname = $stmt->fetchColumn())
            $existingSettings[$settingname] = $settingname;

        if (in_array($name, $existingSettings)) {
            $sql = "UPDATE $tablename SET value=:value WHERE name=:name AND plugin_id=:pluginid";
        }
        else {
            $sql = "INSERT INTO $tablename (value, name, plugin_id) VALUES (:value, :name, :pluginid)";
        }


        Record::logQuery($sql);

        $stmt = Record::getConnection()->prepare($sql);

        return $stmt->execute(array(':pluginid' => $plugin_id, ':name' => $name, ':value' => $value));
    }

    /**
     * Retrieves all settings for a plugin and returns an array of name-value pairs.
     * Returns empty array when unsuccessful in retrieving the settings.
     *
     * @param <type> $plugin_id
     */
    static function getAllSettings($plugin_id=null) {
        if ($plugin_id == null) return false;

        $tablename = TABLE_PREFIX.'plugin_settings';

        $settings = array();

        $sql = "SELECT name,value FROM $tablename WHERE plugin_id=:pluginid";

        Record::logQuery($sql);

        $stmt = Record::getConnection()->prepare($sql);

        $stmt->execute(array(':pluginid' => $plugin_id));

        while ($obj = $stmt->fetchObject()) {
            $settings[$obj->name] = $obj->value;
        }

        return $settings;
    }

    /**
     * Returns the value for a specified setting.
     * Returns false when unsuccessful in retrieving the setting.
     *
     * @param <type> $name
     * @param <type> $plugin_id
     */
    static function getSetting($name=null, $plugin_id=null) {
        if ($name == null || $plugin_id == null) return false;

        $tablename = TABLE_PREFIX.'plugin_settings';

        $existingSettings = array();

        $sql = "SELECT value FROM $tablename WHERE plugin_id=:pluginid AND name=:name LIMIT 1";

        Record::logQuery($sql);

        $stmt = Record::getConnection()->prepare($sql);

        $stmt->execute(array(':pluginid' => $plugin_id, ':name' => $name));

        return $stmt->fetchColumn();
    }
} // end Plugin class
