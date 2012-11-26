<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Views
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

// Prevent any possible caching
header('Content-type: text/plain');
header("Cache-Control: no-cache, must-revalidate");     // HTTP/1.1
header("Expires: Tue, 05 Dec 2000 00:00:01 GMT");       // Date in the past

// Init
$current = null;
$pluginname = null;

// Do actual work
foreach ($files as $file => $strings) {
    $file = substr($file, strpos($file, '/plugins/') + 9);
    $file = substr($file, 0, strpos($file, '/'));

    if ($current == null) {
        $current = $file;
        $pluginname = $file;
        $tmp = array();
    }

    if ($current == $file) {
        foreach ($strings as $string)
            $tmp[] = $string;
    }
    else {
        writeTemplate($pluginname, $tmp);

        $current = $file;
        $pluginname = $file;
        $tmp = array();
        foreach ($strings as $string)
            $tmp[] = $string;
    }
}

writeTemplate($pluginname, $tmp);

// End work

/**
 * Outputs the plugin template.
 *
 * @param string $pluginname
 * @param array  $strings
 */
function writeTemplate($pluginname, $strings) {
    echo '<?php

    /**
     * YourLanguage file for plugin '.$pluginname.'
     *
     * @package Plugins
     * @subpackage '.$pluginname.'
     *
     * @author Your Name <email@domain.something>
     * @version Wolf x.y.z
     */

    return array(
    ';

    $strings = removeDoubles($strings);
    sort($strings);

    foreach ($strings as $string) {
        echo "    '".$string."' => '".$string."',\n";
    }    

    echo "    );\n\n\n\n\n\n";
}

/**
 * Removes any double entries in the array.
 *
 * @param array $array
 * @return array 
 */
function removeDoubles($array) {
    $result = array();
        
    foreach ($array as $string) {
        if (!in_array($string, $result))
        $result[] = $string;
    }

    return $result;
}