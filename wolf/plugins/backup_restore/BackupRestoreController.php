<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The BackupRestore plugin provides administrators with the option of backing
 * up their pages, settings and uploaded files to an XML file.
 *
 * @package Plugins
 * @subpackage backup_restore
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Frank Edelhaeuser <mrpace2@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2011
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/**
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Frank Edelhaeuser <mrpace2@gmail.com>
 * @copyright Martijn van der Kleijn, 2009,2010
 */
class BackupRestoreController extends PluginController {

    private static function _checkPermission() {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
        else if ( ! AuthUser::getId() == 1) {
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
        $tablenames = array();

        if (strpos(DB_DSN,'mysql') !== false) {
            $sql = 'show tables';
        }

        if (strpos(DB_DSN,'sqlite') !== false) {
            $sql = 'SELECT name FROM SQLITE_MASTER WHERE type="table" ORDER BY name';
        }

        if (strpos(DB_DSN,'pgsql') !== false) {
            $sql = "select tablename from pg_tables where schemaname='public'";
        }

        Record::logQuery($sql);

        $pdo = Record::getConnection();
        $result = $pdo->query($sql);

        while ($col = $result->fetchColumn()) {
            $tablenames[] = $col;
        }

        // All fields that should be wrapped as CDATA
        $cdata_fields = array('title', 'content', 'content_html');

        // Setup XML for backup
        $xmltext = '<?xml version="1.0" encoding="UTF-8"?><wolfcms></wolfcms>';
        $xmlobj = new SimpleXMLExtended($xmltext);
        $xmlobj->addAttribute('version', CMS_VERSION);

        // Retrieve all database information for placement in XML backup
        global $__CMS_CONN__;
        Record::connection($__CMS_CONN__);

        // Generate XML file entry for each table
        foreach ($tablenames as $tablename) {
            $table = Record::query('SELECT * FROM '.$tablename);

            $child = $xmlobj->addChild($tablename.'s');
            while ($entry = $table->fetch(PDO::FETCH_ASSOC)) {
                $subchild = $child->addChild($tablename);
                foreach ($entry as $key => $value) {
                    if ($key == 'password' && $settings['pwd'] === '0') {
                        $value = '';
                    }

                    if (in_array($key, $cdata_fields, true)) {
                        $valueChild = $subchild->addCData($key,$value);
                    }
                    else {
                        $valueChild = $subchild->addChild($key,str_replace('&', '&amp;', $value));
                    }
                    if ($value === null)
                        $valueChild->addAttribute('null', true);
                }
            }
        }

        // Add XML files entries for all files in upload directory
        if ($settings['backupfiles'] == '1') {
            $dir = realpath(FILES_DIR);
            $this->_backup_directory($xmlobj->addChild('files'), $dir, $dir);
        }

        // Create the XML file
        $file = $xmlobj->asXML();
        $filename = 'wolfcms-backup-'.date($settings['stamp']).'.'.$settings['extension'];

        // Offer a plain XML file or a zip file for download
        if ($settings['zip'] == '1') {
            // Create a note file
            $note = "---[ NOTES for $filename ]---\n\n";
            $note .= "This backup was created for a specific Wolf CMS version, please only restore it\n";
            $note .= "on the same version.\n\n";
            $note .= "When restoring a backup, upload the UNzipped XML backup file, not this zip file.\n\n";
            $note .= 'Created on '.date('Y-m-d').' at '.date('H:i:s').' GTM '.date('O').".\n";
            $note .= 'Created with BackupRestore plugin version '.BR_VERSION."\n";
            $note .= 'Created for Wolf CMS version '.CMS_VERSION."\n\n";
            $note .= '---[ END NOTES ]---';

            use_helper('Zip');

            $zip = new Zip();
            $zip->clear();
            $zip->addFile($note, 'readme.txt');
            $zip->addFile($file, $filename);
            $zip->download($filename.'.zip');
        }
        else {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: text/xml; charset=UTF-8');
            header('Content-Disposition: attachment; filename='.$filename.';');
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
        $tablenames = array();

        if (strpos(DB_DSN,'mysql') !== false) {
            $sql = 'show tables';
        }

        if (strpos(DB_DSN,'sqlite') !== false) {
            $sql = 'SELECT name FROM SQLITE_MASTER WHERE type="table" ORDER BY name';
        }

        if (strpos(DB_DSN,'pgsql') !== false) {
            $sql = "select tablename from pg_tables where schemaname='public'";
        }

        Record::logQuery($sql);

        $pdo = Record::getConnection();
        $result = $pdo->query($sql);

        while ($col = $result->fetchColumn()) {
            $tablenames[] = $col;
        }

        // All fields that should be wrapped as CDATA
        $cdata_fields = array('title', 'content', 'content_html');

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
                if (strpos(DB_DSN,'sqlite') !== false) {
                    $sql = 'DELETE FROM '.$tablename;
                }
                else {
                    $sql = 'TRUNCATE '.$tablename;
                }

                Record::logQuery($sql);

                if (false === $__CMS_CONN__->exec($sql)) {
                    Flash::set('error', __('Unable to truncate current table :tablename.', array(':tablename' => $tablename)));
                    redirect(get_url('plugin/backup_restore'));
                }

                foreach ($xml->$container->$tablename as $element) {
                    $keys = array();
                    $values = array();
                    $delete_salt = false;
                    foreach ($element as $key => $value) {
                        $keys[] = $key;
                        if ($key === 'password' && empty($value)) {
                            $delete_salt = true;
                            if (isset($settings['default_pwd']) && $settings['default_pwd'] !== '') {
                                $value = sha1($settings['default_pwd']);
                            }
                            else {
                                $value = sha1('pswpsw123');
                            }
                            $values[] = $__CMS_CONN__->quote($value); 
                        } else {
                            $attributes = (array)$value->attributes();
                            $values[] = (isset($attributes['@attributes']) and $attributes['@attributes']['null']) ?
                                   'NULL' :
                                   $__CMS_CONN__->quote($value);
                        }
                    }
                    if ($delete_salt and isset($keys['salt'])) {
                        unset($keys['salt']);
                    }
                    $sql = 'INSERT INTO '.$tablename.' ('.join(', ', $keys).') VALUES ('.join(', ', $values).')'."\r";
                    if ($__CMS_CONN__->exec($sql) === false) {
                        Flash::set('error', __('Unable to reconstruct table :tablename.', array(':tablename' => $tablename)));
                        redirect(get_url('plugin/backup_restore'));
                    }
                }
            }
        }

        // Erase all uploaded files?
        if ($settings['erasefiles'] == '1') {
            $this->_cleanup_directory(realpath(FILES_DIR));
        }

        // Restore directories and files from XML
        if (($settings['restorefiles'] == '1') && array_key_exists('files', $xml)) {
            // First directories
            foreach ($xml->files->directory as $obj) {
                $name = realpath(FILES_DIR).'/'.$obj->name;
                if (!file_exists($name)) {
                    if (mkdir($name, 0777, true) === false) {
                        Flash::set('error', __('Unable to create directory :name.', array(':name' => dirname($obj->name))));
                        redirect(get_url('plugin/backup_restore'));
                    }
                }
                $this->_restore_attributes($name, $obj);
            }

            // Then files
            foreach ($xml->files->file as $obj) {
                $name = realpath(FILES_DIR).'/'.$obj->name;
                if (file_put_contents($name, base64_decode($obj->content)) === false) {
                    Flash::set('error', __('Unable to restore file :name.', array(':name' => $obj->name)));
                    redirect(get_url('plugin/backup_restore'));
                }
                $this->_restore_attributes($name, $obj);
            }
        }

        Flash::set('success', __('Succesfully restored backup.'));

        redirect(get_url('plugin/backup_restore'));
    }

    function _backup_directory($parent, $dir, $basedir) {
        foreach (new DirectoryIterator($dir) as $obj) {
            if ($obj->isDot() || $obj->isLink()) {
            }
            else if ($obj->isDir()) {
                $child = $parent->addChild('directory');
                $child->addChild('name', substr($obj->getPathname(), strlen($basedir.'/')));
                $child->addChild('mode', substr(sprintf('%o', $obj->getPerms()), -4));
                $child->addChild('mtime', date(DATE_RFC822, $obj->getMTime()));

                $this->_backup_directory($parent, $obj->getPathname(), $basedir);
            }
            else if ($obj->isFile()) {
                $child = $parent->addChild('file');
                $child->addChild('name', substr($obj->getPathname(), strlen($basedir.'/')));
                $child->addChild('mode', substr(sprintf('%o', $obj->getPerms()), -4));
                $child->addChild('mtime', date(DATE_RFC822, $obj->getMTime()));
                $child->addChild('content', base64_encode(file_get_contents($obj->getPathname())));
            }
        }
    }

    function _cleanup_directory($dir, $basedir) {
        if (is_dir($dir)) {
            foreach (new DirectoryIterator($dir) as $obj) {
                if (!$obj->isDot()) {
                    if ($obj->isDir()) {
                        $this->_cleanup_directory($obj->getPathname(), $basedir);
                        if (rmdir($obj->getPathname()) === false) {
                            Flash::set('error', __('Unable to delete directory :name.', array(':name' => substr($obj->getPathname(), strlen($basedir.'/')))));
                            redirect(get_url('plugin/backup_restore'));
                        }
                    }
                    else {
                        if (unlink($obj->getPathname()) === false) {
                            Flash::set('error', __('Unable to delete file :name.', array(':name' => substr($obj->getPathname(), strlen($basedir.'/')))));
                            redirect(get_url('plugin/backup_restore'));
                        }
                    }
                }
            }
        }
    }

    function _restore_attributes($name, $obj) {

        // Set file attributes
        if (array_key_exists('mode', $obj)) {
            if (chmod($name, $obj->mode) === false) {
                Flash::set('error', __('Unable to restore attributes for :name.', array(':name' => $obj->name)));
                redirect(get_url('plugin/backup_restore'));
            }
        }

        // Set modification time
        if (array_key_exists('mtime', $obj)) {
            $dt = date_parse($obj->mtime);
            $mtime = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']); 
            if (touch($name, $mtime) === false) {
                Flash::set('error', __('Unable to restore modification date for :name.', array(':name' => $obj->name)));
                redirect(get_url('plugin/backup_restore'));
            }
        }
    }
}

class SimpleXMLExtended extends SimpleXMLElement {
    public function addCData($nodename,$cdata_text) {
        $sxe = $this->addChild($nodename); //Added a nodename to create inside the function
        $node = dom_import_simplexml($sxe);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
        return $sxe;
    }
}
