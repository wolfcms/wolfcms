<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) {
    exit();
}

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * Note - Mostly rewritten since Wolf CMS 0.6.0
 *
 * @package plugins
 * @subpackage file_manager
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since Wolf version 0.5.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2008-2010
 *
 * @todo Starting from PHP 5.3, use FileInfo
 */

/**
 * 
 */
class FileManagerController extends PluginController {

    var $path;
    var $fullpath;


    public static function _checkPermission() {
        AuthUser::load();
        if (!AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
        else if (!AuthUser::hasPermission('file_manager_view')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
    }


    public function __construct() {
        self::_checkPermission();

        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/file_manager/views/sidebar'));
    }


    public function index() {
        $this->browse();
    }


    public function browse() {
        $params = func_get_args();

        $this->path = join('/', $params);
        // make sure there's a / at the end
        if (substr($this->path, -1, 1) != '/')
            $this->path .= '/';

        //security
        // we dont allow back link
        if (strpos($this->path, '..') !== false) {
            if (Plugin::isEnabled('statistics_api')) {
                $user = null;
                if (AuthUser::isLoggedIn())
                    $user = AuthUser::getUserName();
                $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : ($_SERVER['REMOTE_ADDR']);
                $event = array('event_type' => 'hack_attempt', // simple event type identifier
                    'description' => __('A possible hack attempt was detected.'), // translatable description
                    'ipaddress' => $ip,
                    'username' => $user);
                Observer::notify('stats_file_manager_hack_attempt', $event);
            }
        }
        $this->path = str_replace('..', '', $this->path);

        // clean up nicely
        $this->path = str_replace('//', '', $this->path);

        // we dont allow leading slashes
        $this->path = preg_replace('/^\//', '', $this->path);

        $this->fullpath = FILES_DIR.'/'.$this->path;

        // clean up nicely
        $this->fullpath = preg_replace('/\/\//', '/', $this->fullpath);

        $this->display('file_manager/views/index', array(
            'dir' => $this->path,
            //'files' => $this->_getListFiles()
            'files' => $this->_listFiles()
        ));
    }


// browse


    public function view() {
        $params = func_get_args();
        $content = '';

        $filename = urldecode(join('/', $params));

        // Sanitize filename for securtiy
        // We don't allow backlinks
        if (strpos($filename, '..') !== false) {
            if (Plugin::isEnabled('statistics_api')) {
                $user = null;
                if (AuthUser::isLoggedIn())
                    $user = AuthUser::getUserName();
                $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : ($_SERVER['REMOTE_ADDR']);
                $event = array('event_type' => 'hack_attempt', // simple event type identifier
                    'description' => __('A possible hack attempt was detected.'), // translatable description
                    'ipaddress' => $ip,
                    'username' => $user);
                Observer::notify('stats_file_manager_hack_attempt', $event);
            }
        }
        $filename = str_replace('..', '', $filename);

        // Clean up nicely
        $filename = str_replace('//', '', $filename);

        // We don't allow leading slashes
        $filename = preg_replace('/^\//', '', $filename);

        $file = FILES_DIR.'/'.$filename;
        if (!$this->_isImage($file) && file_exists($file)) {
            $content = file_get_contents($file);
        }

        $this->display('file_manager/views/view', array(
            'is_image' => $this->_isImage($file),
            'filename' => $filename,
            'content' => $content
        ));
    }


    public function save() {
        $data = $_POST['file'];

        // security (remove all ..)
        $data['name'] = str_replace('..', '', $data['name']);
        $file = FILES_DIR.DS.$data['name'];
        if (file_exists($file)) {
            if (file_put_contents($file, $data['content'])) {
                Flash::set('success', __('File has been saved with success!'));
            }
            else {
                Flash::set('error', __('File is not writable! File has not been saved!'));
            }
        }
        else {
            if (file_put_contents($file, $data['content'])) {
                Flash::set('success', __('File :name has been created with success!', array(':name' => $data['name'])));
            }
            else {
                Flash::set('error', __('Directory is not writable! File has not been saved!'));
            }
        }

        // save and quit or save and continue editing ?
        if (isset($_POST['commit'])) {
            redirect(get_url('plugin/file_manager/browse/'.substr($data['name'], 0, strrpos($data['name'], '/'))));
        }
        else {
            redirect(get_url('plugin/file_manager/view/'.$data['name']));
        }
    }


    public function create_file() {
        if (!AuthUser::hasPermission('file_manager_mkfile')) {
            Flash::set('error', __('You do not have sufficient permissions to create a file.'));
            redirect(get_url('plugin/file_manager/browse/'));
        }

        $data = $_POST['file'];

        $path = str_replace('..', '', $data['path']);
        $filename = str_replace('..', '', $data['name']);
        $file = FILES_DIR.DS.$path.DS.$filename;

        if (file_put_contents($file, '') !== false) {
            chmod($file, 0644);
        }
        else {
            Flash::set('error', __('File :name has not been created!', array(':name' => $filename)));
        }
        redirect(get_url('plugin/file_manager/browse/'.$path));
    }


    public function create_directory() {
        if (!AuthUser::hasPermission('file_manager_mkdir')) {
            Flash::set('error', __('You do not have sufficient permissions to create a directory.'));
            redirect(get_url('plugin/file_manager/browse/'));
        }

        $data = $_POST['directory'];

        $path = str_replace('..', '', $data['path']);
        $dirname = str_replace('..', '', $data['name']);
        $dir = FILES_DIR."/{$path}/{$dirname}";

        if (mkdir($dir)) {
            chmod($dir, 0755);
        }
        else {
            Flash::set('error', __('Directory :name has not been created!', array(':name' => $dirname)));
        }
        redirect(get_url('plugin/file_manager/browse/'.$path));
    }


    public function delete() {
        if (!AuthUser::hasPermission('file_manager_delete')) {
            Flash::set('error', __('You do not have sufficient permissions to delete a file or directory.'));
            redirect(get_url('plugin/file_manager/browse/'));
        }

        $paths = func_get_args();

        $file = urldecode(join('/', $paths));

        $file = FILES_DIR.'/'.str_replace('..', '', $file);
        $filename = array_pop($paths);
        $paths = join('/', $paths);

        if (is_file($file)) {
            if (!unlink($file))
                Flash::set('error', __('Permission denied!'));
        }
        else {
            if (!_rrmdir($file))
                Flash::set('error', __('Permission denied!'));
        }

        redirect(get_url('plugin/file_manager/browse/'.$paths));
    }


    public function upload() {
        if (!AuthUser::hasPermission('file_manager_upload')) {
            Flash::set('error', __('You do not have sufficient permissions to upload a file.'));
            redirect(get_url('plugin/file_manager/browse/'));
        }

        $data = $_POST['upload'];
        $path = str_replace('..', '', $data['path']);
        $overwrite = isset($data['overwrite']) ? true : false;

        // Clean filenames
        $filename = preg_replace('/ /', '_', $_FILES['upload_file']['name']);
        $filename = preg_replace('/[^a-z0-9_\-\.]/i', '', $filename);

        if (isset($_FILES)) {
            $file = $this->_upload_file($filename, FILES_DIR.'/'.$path.'/', $_FILES['upload_file']['tmp_name'], $overwrite);

            if ($file === false)
                Flash::set('error', __('File has not been uploaded!'));
        }
        redirect(get_url('plugin/file_manager/browse/'.$path));
    }


    public function chmod() {
        if (!AuthUser::hasPermission('file_manager_chmod')) {
            Flash::set('error', __('You do not have sufficient permissions to change the permissions on a file or directory.'));
            redirect(get_url('plugin/file_manager/browse/'));
        }

        $data = $_POST['file'];
        $data['name'] = str_replace('..', '', $data['name']);
        $file = FILES_DIR.'/'.$data['name'];

        if (file_exists($file)) {
            if (@!chmod($file, octdec($data['mode'])))
                Flash::set('error', __('Permission denied!'));
        }
        else {
            Flash::set('error', __('File or directory not found!'));
        }

        $path = substr($data['name'], 0, strrpos($data['name'], '/'));
        redirect(get_url('plugin/file_manager/browse/'.$path));
    }


    public function rename() {
        if (!AuthUser::hasPermission('file_manager_rename')) {
            Flash::set('error', __('You do not have sufficient permissions to rename this file or directory.'));
            redirect(get_url('plugin/file_manager/browse/'));
        }

        $data = $_POST['file'];

        $data['current_name'] = str_replace('..', '', $data['current_name']);
        $data['new_name'] = str_replace('..', '', $data['new_name']);

        // Clean filenames
        $data['new_name'] = preg_replace('/ /', '_', $data['new_name']);
        $data['new_name'] = preg_replace('/[^a-z0-9_\-\.]/i', '', $data['new_name']);

        $path = substr($data['current_name'], 0, strrpos($data['current_name'], '/'));
        $file = FILES_DIR.'/'.$data['current_name'];

        // Check another file doesn't already exist with same name
        if (file_exists(FILES_DIR.'/'.$path.'/'.$data['new_name'])) {
            Flash::set('error', __('A file or directory with that name already exists!'));
            redirect(get_url('plugin/file_manager/browse/'.$path));
        }

        if (file_exists($file)) {
            if (!rename($file, FILES_DIR.'/'.$path.'/'.$data['new_name']))
                Flash::set('error', __('Permission denied!'));
        }
        else {
            Flash::set('error', __('File or directory not found!'.$file));
        }

        redirect(get_url('plugin/file_manager/browse/'.$path));
    }


    //
    // Privates
    //

    public function _getPath() {
        $path = join('/', get_params());
        return str_replace('..', '', $path);
    }


    private function _listFiles() {
        if (is_dir($this->fullpath)) {
            $files = array();
            $root = new DirectoryIterator($this->fullpath);

            foreach ($root as $cur) {
                if ($cur->isDot() || $cur->isLink())
                    continue;

                $object = new stdClass;
                $object->name = $cur->getFilename();
                $object->is_dir = $cur->isDir();
                $object->is_file = $cur->isFile();
                $object->size = convert_size($cur->getSize());
                $object->mtime = date('D, j M, Y', $cur->getMTime());
                list($object->perms, $object->chmod) = $this->_getPermissions($cur->getPerms());

                // make the link depending on if it's a file or a dir
                if ($cur->isDir()) {
                    $object->link = '<a href="'.get_url('plugin/file_manager/browse/'.$this->path.$object->name).'">'.$object->name.'</a>';
                }
                else {
                    $object->link = '<a href="'.get_url('plugin/file_manager/view/'.$this->path.$object->name).'">'.$object->name.'</a>';
                }

                $files[$object->name] = $object;
            }

            uksort($files, 'strnatcmp');
            return $files;
        }

        return array();
    }


    private function _getPermissions($perms) {
        //$perms = fileperms($file);

        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        }
        elseif (($perms & 0xA000) == 0xA000) {
            // Symbolic Link
            $info = 'l';
        }
        elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        }
        elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        }
        elseif (($perms & 0x4000) == 0x4000) {
            // Directory
            $info = 'd';
        }
        elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        }
        elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        }
        else {
            // Unknown
            $info = 'u';
        }

        // Owner
        $info .= ( ($perms & 0x0100) ? 'r' : '-');
        $info .= ( ($perms & 0x0080) ? 'w' : '-');
        $info .= ( ($perms & 0x0040) ?
                        (($perms & 0x0800) ? 's' : 'x' ) :
                        (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= ( ($perms & 0x0020) ? 'r' : '-');
        $info .= ( ($perms & 0x0010) ? 'w' : '-');
        $info .= ( ($perms & 0x0008) ?
                        (($perms & 0x0400) ? 's' : 'x' ) :
                        (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= ( ($perms & 0x0004) ? 'r' : '-');
        $info .= ( ($perms & 0x0002) ? 'w' : '-');
        $info .= ( ($perms & 0x0001) ?
                        (($perms & 0x0200) ? 't' : 'x' ) :
                        (($perms & 0x0200) ? 'T' : '-'));

        return array($info, substr(sprintf('%o', $perms), -4, 4)); // (perm, chmod)
    }

    
    public function _isImage($file) {
        if (!@is_file($file))
            return false;
        else if (!preg_match('/^(.*).(jpe?g|gif|png)$/i', $file))
            return false;

        return true;
    }


    // Usage: upload_file($_FILE['file']['name'],'temp/',$_FILE['file']['tmp_name'])
    private function _upload_file($origin, $dest, $tmp_name, $overwrite=false) {
        FileManagerController::_checkPermission();
        AuthUser::load();
        if (!AuthUser::hasPermission('file_manager_upload')) {
            return false;
        }

        $origin = basename($origin);
        $full_dest = $dest.$origin;
        $file_name = $origin;
        for ($i = 1; file_exists($full_dest); $i++) {
            if ($overwrite) {
                unlink($full_dest);
                continue;
            }

            $file_ext = (strpos($origin, '.') === false ? '' : '.'.substr(strrchr($origin, '.'), 1));
            $file_name = substr($origin, 0, strlen($origin) - strlen($file_ext)).'_'.$i.$file_ext;
            $full_dest = $dest.$file_name;
        }

        if (move_uploaded_file($tmp_name, $full_dest)) {
            // change mode of the dire to 0644 by default
            chmod($full_dest, 0644);
            return $file_name;
        }

        return false;
    }


    // recursiv rmdir
    private function _rrmdir($dirname) {
        FileManagerController::_checkPermission();
        AuthUser::load();
        if (!AuthUser::hasPermission('file_manager_delete')) {
            return false;
        }

        if (is_dir($dirname)) {
            // Append slash if necessary
            if (substr($dirname, -1) != '/')
                $dirname.='/';

            $handle = opendir($dirname);
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path = $dirname.$file;
                    if (is_dir($path)) {
                        _rrmdir($path);
                    }
                    else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            rmdir($dirname); // Remove dir
            return true; // Return array of deleted items
        }
        else {
            return false; // Return false if attempting to operate on a file
        }
    }
}