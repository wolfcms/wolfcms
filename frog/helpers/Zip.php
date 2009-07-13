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
 * Simple Zip library
 *
 * @package frog
 * @subpackage helpers
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @since Frog version beta 1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2007
 */

/**
 *
 */
class Zip
{
    private $_data   = array();
    private $_dirs   = array();
    private $_offset = 0;

    /**
     * Adds "file" to archive
     *
     * @param  string   file contents or filepath
     * @param  string   name of the file in the archive (may contains the path)
     * @param  integer  the current timestamp
     *
     * @return void
     */
    public function addFile($data, $name, $time=0)
    {
        if (strpos($data, "\n") === false && file_exists($data)) {
            $data = file_get_contents($data);
        }
        
        $name     = str_replace('\\', '/', $name);

        $dtime    = dechex($this->_unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
                  . '\x' . $dtime[4] . $dtime[5]
                  . '\x' . $dtime[2] . $dtime[3]
                  . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');

        // "local file header" segment
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len   = strlen($zdata);

        // add this entry to array
        $this->_data[] = "\x50\x4b\x03\x04"
                       . "\x14\x00"                // ver needed to extract
                       . "\x00\x00"                // gen purpose bit flag
                       . "\x08\x00"                // compression method
                       . $hexdtime                 // last mod time and date
                       . pack('V', $crc)           // crc32
                       . pack('V', $c_len)         // compressed filesize
                       . pack('V', $unc_len)       // uncompressed filesize
                       . pack('v', strlen($name))  // length of filename
                       . pack('v', 0)              // extra field length
                       . $name
                       . $zdata;                   // "file data" segment

        // now add to central directory record
        $this->_dirs[] = "\x50\x4b\x01\x02"
                       . "\x00\x00"                // version made by
                       . "\x14\x00"                // version needed to extract
                       . "\x00\x00"                // gen purpose bit flag
                       . "\x08\x00"                // compression method
                       . $hexdtime                 // last mod time & date
                       . pack('V', $crc)           // crc32
                       . pack('V', $c_len)         // compressed filesize
                       . pack('V', $unc_len)       // uncompressed filesize
                       . pack('v', strlen($name))  // length of filename
                       . pack('v', 0 )             // extra field length
                       . pack('v', 0 )             // file comment length
                       . pack('v', 0 )             // disk number start
                       . pack('v', 0 )             // internal file attributes
                       . pack('V', 32 )            // external file attributes - 'archive' bit set
                       . pack('V', $this->_offset) // relative offset of local header
                       . $name;
        
        $this->_offset += strlen($this->_data[count($this->_data)-1]);
        // optional extra field, file comment goes here ..
    } // end addFile() method


    /**
     * Dumps out file
     *
     * @param none
     *
     * @return string the zipped file
     */
    public function file()
    {
        $data = implode('', $this->_data);
        $dirs = implode('', $this->_dirs);

        return $data
             . $dirs
             . "\x50\x4b\x05\x06\x00\x00\x00\x00"
             . pack('v', sizeof($this->_dirs))  // total # of entries "on this disk"
             . pack('v', sizeof($this->_dirs))  // total # of entries overall
             . pack('V', strlen($dirs))         // size of central dir
             . pack('V', strlen($data))         // offset to start of central dir
             . "\x00\x00";                      // .zip file comment length
    } // end file() method

    /**
     * Save zip file on server side
     *
     * @param string file name (may contains the path)
     *
     * @return void
     */
    public function save($filename='archive.zip')
    {
        file_put_contents($filename, $this->file());
    } // end save() method
    
    /**
     * Send to browser (download) the zip file
     *
     * @param string file name (may contains the path)
     *
     * @return void
     */
    public function download($filename='archive.zip')
    {
        $file = $this->file();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=".$filename.";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".strlen($file));
        
        echo $file;
    } // end download() method
    
    /**
     * Clear all variables, if you need to create a other zip file
     *
     * @param none
     *
     * @return void
     */
    public function clear()
    {
        $this->_data   = array();
        $this->_dirs   = array();
        $this->_offset = 0;
    }
    // -----------------------------------------------------------------------
    
    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param  integer  the current Unix timestamp
     *
     * @return integer  the current date in a four byte DOS format
     */
    private function _unix2DosTime($unixtime=0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
               ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end _unix2DosTime() method

} // end Zip class
