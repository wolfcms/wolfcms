<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2009,2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/**
 * Keeps track of cron related information.
 *
 * Also generates the webbug needed for use of the poor man's cron functionality.
 */
final class Cron extends Record {
    const TABLE_NAME = 'cron';

    protected $id = '1';
    protected $lastrun;

    public function __construct($data=null) {
        if ($data !== null)
            $this->lastrun = $data->lastrun;
    }

    public function getLastRunTime() {
        return $this->lastrun;
    }

    public function beforeUpdate() {
        $this->lastrun = time();
        return true;
    }

    public function generateWebBug() {
            $cronbug = '<!-- About the image below: the website owner chose to have Wolf CMS start a (cron) job on the server by displaying the following image. -->'."\n";
            $cronbug .= '<img id="wolf-cron-webbug" style="display: none;" src="'.URL_PUBLIC.'/wolf/app/cron.php" width="1" height="1" border="0" />'."\n";
            return $cronbug;
    }

}

?>
