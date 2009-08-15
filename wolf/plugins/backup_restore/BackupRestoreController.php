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

    public function __construct() {
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

        $tablenames = array('layout', 'page', 'page_part', 'page_tag', 'permission',
                            'plugin_settings', 'setting', 'snippet', 'tag', 'user', 'user_permission');

        $xmltext = '<?xml version="1.0" encoding="UTF-8"?><wolfcms></wolfcms>';
        $xmlobj = new SimpleXMLExtended($xmltext);
        $xmlobj->addAttribute('version', CMS_VERSION);

        global $__CMS_CONN__;
        Record::connection($__CMS_CONN__);

        $lasttable = '';

        foreach ($tablenames as $tablename) {
            $table = Record::query('SELECT * FROM '.TABLE_PREFIX.$tablename);

            while($entry = $table->fetchObject()) {
                if ($lasttable !== $tablename) {
                    $lasttable = $tablename;
                    $child = $xmlobj->addChild($tablename.'s');
                }
                $subchild = $child->addChild($tablename);
                while (list($key, $val) = each($entry)) {
                    if ($key === 'content' || $key === 'content_html') {
                        $subchild->addCData($key,$val);
                    }
                    else {
                        $subchild->addChild($key,$val);
                    }
                }
            }
        }

        $file = $xmlobj->asXML();
        $filename = 'wolfcms-backup-'.date('Ymd', time());

        // Offer a plain XML file or a zip file?
        if (isset($settings['zip']) && $settings['zip'] == '1') {
            use_helper('Zip');

            $zip = new Zip();
            $zip->clear();
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

    public function export() {
        $this->setLayout('');
        $this->display('backup_restore/views/xmlexport');
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

