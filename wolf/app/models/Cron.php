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
