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

/**
 * 
 */
class BackupRestoreController extends PluginController {

    private static function _checkPermission() {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
        else if ( ! AuthUser::hasPermission('administrator')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
    }

    public function __construct() {
        self::_checkPermission();
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/backup_restore/views/sidebar'));
    }

    public function index() {
        $this->documentation();
    }

    public function documentation() {
        $this->display('backup_restore/views/documentation');
    }

    function settings() {
        $this->display('backup_restore/views/settings', array('settings' => Plugin::getAllSettings('backup_restore')));
    }

    function save() {
        if (isset($_POST['settings'])) {
            $settings = $_POST['settings'];
            foreach ($settings as $key => $value) {
                $settings[$key] = mysql_escape_string($value);
            }
            
            $ret = Plugin::setAllSettings($settings, 'backup_restore');

            if ($ret) {
                Flash::set('success', __('The settings have been saved.'));
            }
            else {
                Flash::set('error', 'An error occured trying to save the settings.');
            }
        }
        else {
            Flash::set('error', 'Could not save settings, no settings found.');
        }

        redirect(get_url('plugin/backup_restore/settings'));
    }

    function backup() {
        $settings = Plugin::getAllSettings('backup_restore');

        // All of the tablesnames that belong to Wolf CMS core.
        $tablenames = array('layout', 'page', 'page_part', 'page_tag', 'permission',
                            'plugin_settings', 'setting', 'snippet', 'tag', 'user',
                            'user_permission'
                           );

        // All fields that should be wrapped as CDATA
        $cdata_fields = array('content', 'content_html');

        // Setup XML for backup
        $xmltext = '<?xml version="1.0" encoding="UTF-8"?><wolfcms></wolfcms>';
        $xmlobj = new SimpleXMLExtended($xmltext);
        $xmlobj->addAttribute('version', CMS_VERSION);

        // Retrieve all database information for placement in XML backup
        global $__CMS_CONN__;
        Record::connection($__CMS_CONN__);

        $lasttable = '';

        // Generate XML file entry for each table
        foreach ($tablenames as $tablename) {
            $table = Record::query('SELECT * FROM '.TABLE_PREFIX.$tablename);

            while($entry = $table->fetchObject()) {
                if ($lasttable !== $tablename) {
                    $lasttable = $tablename;
                    $child = $xmlobj->addChild($tablename.'s');
                }
                $subchild = $child->addChild($tablename);
                while (list($key, $value) = each($entry)) {
                    if ($key === 'password' && $settings['pwd'] === '0') {
                        $value = '';
                    }

                    if (in_array($key, $cdata_fields, true)) {
                        $subchild->addCData($key,$value);
                    }
                    else {
                        $subchild->addChild($key,$value);
                    }
                }
            }
        }

        // Create the XML file
        $file = $xmlobj->asXML();
        $filename = 'wolfcms-backup-'.date($settings['stamp']);

        // Offer a plain XML file or a zip file for download
        if ($settings['zip'] == '1') {
            // Create a note file
            $note = "---[ NOTES for $filename.xml ]---\n\n";
            $note .= "This backup was created for a specific Wolf CMS version, please only restore it\n";
            $note .= "on the same version.\n\n";
            $note .= "When restoring a backup, upload the UNzipped XML file, not this zip file.\n\n";
            $note .= 'Created on '.date('Y-m-d').' at '.date('H:i:s').' GTM '.date('O').".\n";
            $note .= 'Created with BackupRestore plugin version '.BR_VERSION."\n";
            $note .= 'Created for Wolf CMS version '.CMS_VERSION."\n\n";
            $note .= '---[ END NOTES ]---';

            use_helper('Zip');

            $zip = new Zip();
            $zip->clear();
            $zip->addFile($note, 'readme.txt');
            $zip->addFile($file, $filename.'.xml');
            $zip->download($filename.'.zip');
        }
        else {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: text/xml; charset=UTF-8');
            header('Content-Disposition: attachment; filename='.$filename.'.xml;');
            header('Content-Transfer-Encoding: 8bit');
            header('Content-Length: '.strlen($file));
            echo $file;
        }
    }

    function restore() {
        if (isset($_POST['action']) && $_POST['action'] == 'restore') {
            if (is_uploaded_file($_FILES['restoreFile']['tmp_name'])) {
                $fileData = file_get_contents($_FILES['restoreFile']['tmp_name']);

                if (isset($fileData) && $fileData !== null && $fileData !== false && $fileData !== '' && strlen($fileData) > 70 ) {
                    $this->_restore($fileData);
                }
                else {
                    Flash::set('error', __('Backup file was not uploaded correctly/completely or is broken.'));
                    $this->display('backup_restore/views/restore');
                }
            }
        }
        else {
            $this->display('backup_restore/views/restore', array('settings' => Plugin::getAllSettings('backup_restore')));
        }
    }

    function _restore($fileData) {
        global $__CMS_CONN__;

        $settings = Plugin::getAllSettings('backup_restore');

        // All of the tablesnames that belong to Wolf CMS core.
        $tablenames = array('layout', 'page', 'page_part', 'page_tag', 'permission',
                            'plugin_settings', 'setting', 'snippet', 'tag', 'user',
                            'user_permission'
                           );

        // All fields that should be wrapped as CDATA
        $cdata_fields = array('content', 'content_html');

        $xml = simplexml_load_string($fileData);

        if (false === $xml) {
            $errors = '';

            foreach(libxml_get_errors() as $error) {
                $errors .= $error->message;
                $errors .= "<br/>\n";
            }

            Flash::set('error', 'An error occurred with the XML backup file: :error', array(':error' => $errors));
            redirect(get_url('plugin/backup_restore'));
        }

        // Import each table and table entry
        foreach($tablenames as $tablename) {
            $container = $tablename.'s';

            if (array_key_exists($container, $xml) && count($xml->$container->$tablename) > 0) {
                if (false === $__CMS_CONN__->exec('TRUNCATE '.TABLE_PREFIX.$tablename)) {
                    Flash::set('error', __('Unable to truncate current table :tablename.', array(':tablename' => TABLE_PREFIX.$tablename)));
                    redirect(get_url('plugin/backup_restore'));
                }

                foreach ($xml->$container->$tablename as $element) {
                    $keys = array();
                    $values = array();
                    foreach ($element as $key => $value) {
                        if ($key === 'password' && (!isset($value) || empty($value) || $value === '' || $value === null)) {
                            if (isset($settings['default_pwd']) && $settings['default_pwd'] !== '') {
                                $value = sha1($settings['default_pwd']);
                            }
                            else {
                                $value = sha1('pswpsw123');
                            }
                        }
                        $keys[] = $key;
                        $values[] = $__CMS_CONN__->quote($value);
                    }
                    $sql = 'INSERT INTO '.TABLE_PREFIX.$tablename.' ('.join(', ', $keys).') VALUES ('.join(', ', $values).')'."\r";

                    if ($__CMS_CONN__->exec($sql) === false) {
                        Flash::set('error', __('Unable to reconstruct table :tablename.', array(':tablename' => TABLE_PREFIX.$tablename)));
                        redirect(get_url('plugin/backup_restore'));
                    }
                }
            }
        }

        Flash::set('success', __('Succesfully restored backup.'));

        redirect(get_url('plugin/backup_restore'));
    }
}

class SimpleXMLExtended extends SimpleXMLElement {
    public function addCData($nodename,$cdata_text) {
        $node = $this->addChild($nodename); //Added a nodename to create inside the function
        $node = dom_import_simplexml($node);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}

