<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008,2009,2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Controllers
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008-2010
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 * Class TranslateController
 *
 * This controller allows users to generate a template for a translation file.
 */
final class TranslateController extends Controller {

    public final function __construct() {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }

        $this->assignToLayout('sidebar', new View('translate/sidebar'));
    }

    public final function index() {
        $this->setLayout('backend');
        $this->display('translate/index');
    }

    public final function core() {
        $complete = array();
        $basedir = CMS_ROOT.'';
        $dirs = $this->listdir($basedir);

        foreach ($dirs as $id => $path) {
            $tmp = array();
            $strings = array();
            $fsize = filesize($path);

            if ($fsize > 0) {
                $fh = fopen($path, 'r');
                $data = fread($fh, $fsize);
                fclose($fh);

                if (strpos($data, '__(\'')) {
                    $data = substr($data, strpos($data, '__(\'')+4);
                    $tmp = explode('__(\'', $data);

                    foreach ($tmp as $string) {
                        $endpos = strpos($string, '\'');
                        while (substr($string, $endpos-1, 1) == "\\") {
                            $endpos = $endpos + strpos(substr($string, $endpos+1, strpos($string, '\'')), '\'') + 1;
                        }
                        $strings[] = substr($string, 0, $endpos);
                    }

                    if (sizeof($strings) > 0) {
                        $complete = array_merge($complete, $strings);
                    }
                }

                if (strpos($data, '__("')) {
                    $data = substr($data, strpos($data, '__("')+4);
                    $tmp = explode('__("', $data);

                    foreach ($tmp as $string) {
                        $endpos = strpos($string, '"');
                        while (substr($string, $endpos-1, 1) == "\\") {
                            $endpos = $endpos + strpos(substr($string, $endpos+1, strpos($string, '"')), '"') + 1;
                        }
                        $strings[] = substr($string, 0, $endpos);
                    }

                    if (sizeof($strings) > 0) {
                        $files[$path] = $strings;
                    }
                }
            }
        }

        // These are a few generated strings which the TranslateController cannot pick out.
        // So we add them manually for now.
        $complete = array_merge($complete, array('Add Page', 'Edit Page', 'Add snippet',
            'Edit snippet', 'Add layout' ,'Edit layout',
            'Add user', 'Edit user'
        ));

        $this->display('translate/core', array('complete' => $complete));
    }

    public final function plugins() {
        $files = array();
        $basedir = PLUGINS_ROOT;
        $dirs = $this->listdir($basedir, true);

        foreach ($dirs as $id => $path) {
            $tmp = array();
            $strings = array();
            $fsize = filesize($path);

            if ($fsize > 0) {
                $fh = fopen($path, 'r');
                $data = fread($fh, $fsize);
                fclose($fh);

                if (strpos($data, '__(\'')) {
                    $data = substr($data, strpos($data, '__(\'')+4);
                    $tmp = explode('__(\'', $data);

                    foreach ($tmp as $string) {
                        $endpos = strpos($string, '\'');
                        while (substr($string, $endpos-1, 1) == "\\") {
                            $endpos = $endpos + strpos(substr($string, $endpos+1, strpos($string, '\'')), '\'') + 1;
                        }
                        $strings[] = substr($string, 0, $endpos);
                    }

                    if (sizeof($strings) > 0) {
                        $files[$path] = $strings;
                    }
                }

                if (strpos($data, '__("')) {
                    $data = substr($data, strpos($data, '__("')+4);
                    $tmp = explode('__("', $data);

                    foreach ($tmp as $string) {
                        $endpos = strpos($string, '"');
                        while (substr($string, $endpos-1, 1) == "\\") {
                            $endpos = $endpos + strpos(substr($string, $endpos+1, strpos($string, '"')), '"') + 1;
                        }
                        $strings[] = substr($string, 0, $endpos);
                    }

                    if (sizeof($strings) > 0) {
                        $files[$path] = $strings;
                    }
                }
            }
        }

        $this->display('translate/plugins', array('files' => $files));
    }

    private final function listdir($start_dir='.', $plugins = false) {
        $files = array();
        if (is_dir($start_dir)) {
            $fh = opendir($start_dir);
            while (($file = readdir($fh)) !== false) {
                # loop through the files, skipping . and .., and recursing if necessary
                if (strcmp($file, '.')==0 || strcmp($file, '..')==0) {
                    continue;
                }
                $filepath = $start_dir . '/' . $file;
                if ($plugins) {
                    if ( is_dir($filepath) && !strpos($filepath, 'i18n') ) {
                        $files = array_merge($files, $this->listdir($filepath, $plugins));
                    }
                    else {
                        if (!strpos($filepath, 'I18n') && strpos($filepath, '.php', strlen($filepath) - 5) || strpos($filepath, '.phtml', strlen($filepath) - 7)) {
                            array_push($files, $filepath);
                        }
                    }
                }
                else {
                    if ( is_dir($filepath) && !strpos($filepath, 'i18n') && !strpos($filepath, 'plugins') ) {
                        $files = array_merge($files, $this->listdir($filepath, $plugins));
                    }
                    else {
                        if (!strpos($filepath, 'I18n') && strpos($filepath, '.php', strlen($filepath) - 5) || strpos($filepath, '.phtml', strlen($filepath) - 7)) {
                            array_push($files, $filepath);
                        }
                    }
                }
            }
            closedir($fh);
        }
        else {
            # false if the function was called with an invalid non-directory argument
            $files = false;
        }

        return $files;
    }

}