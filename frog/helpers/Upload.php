<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
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
 * Simple upload library
 *
 * @package frog
 * @subpackage helpers
 *
 * @author unknown
 * @version 0.1
 * @since Frog version beta 1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright unknown
 */

class Upload {
    
    public $max_size       = 0;
    public $max_width      = 0;
    public $max_height     = 0;
    public $allowed_types  = "";
    public $file_temp      = "";
    public $file_name      = "";
    public $orig_name      = "";
    public $file_type      = "";
    public $file_size      = "";
    public $file_ext       = "";
    public $upload_path    = "";
    public $overwrite      = false;
    public $encrypt_name   = false;
    public $is_image       = false;
    public $image_width    = '';
    public $image_height   = '';
    public $image_type     = '';
    public $image_size_str = '';
    public $error_msg      = array();
    public $mimes          = array();
    public $remove_spaces  = true;
    public $xss_clean      = false;
    public $temp_prefix    = "temp_file_";
        
    /**
     * Constructor
     *
     * @access  public
     */
    function __construct($props = array())
    {
        if (count($props) > 0)
        {
            $this->initialize($props);
        }
        
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Initialize preferences
     *
     * @access  public
     * @param   array
     * @return  void
     */ 
    function initialize($config = array())
    {
        $defaults = array(
                            'max_size'          => 0,
                            'max_width'         => 0,
                            'max_height'        => 0,
                            'allowed_types'     => "",
                            'file_temp'         => "",
                            'file_name'         => "",
                            'orig_name'         => "",
                            'file_type'         => "",
                            'file_size'         => "",
                            'file_ext'          => "",
                            'upload_path'       => "",
                            'overwrite'         => false,
                            'encrypt_name'      => false,
                            'is_image'          => false,
                            'image_width'       => '',
                            'image_height'      => '',
                            'image_type'        => '',
                            'image_size_str'    => '',
                            'error_msg'         => array(),
                            'mimes'             => array(),
                            'remove_spaces'     => true,
                            'xss_clean'         => false,
                            'temp_prefix'       => "temp_file_"
                        );  
    
    
        foreach ($defaults as $key => $val) {
            if (isset($config[$key])) {
                $method = 'set_'.$key;
                if (method_exists($this, $method)) {
                    $this->$method($config[$key]);
                } else {
                    $this->$key = $config[$key];
                }           
            } else {
                $this->$key = $val;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Perform the file upload
     *
     * @access  public
     * @return  bool
     */ 
    function doUpload($field = 'userfile')
    {
        // Is $_FILES[$field] set? If not, no reason to continue.
        if ( ! isset($_FILES[$field])) {
            return false;
        }
        
        // Is the upload path valid?
        if ( ! $this->validateUploadPath()) {
            return false;
        }
                        
        // Was the file able to be uploaded? If not, determine the reason why.
        if ( ! is_uploaded_file($_FILES[$field]['tmp_name'])) {
            $error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

            switch($error) {
                case 1:
                    $this->setError('upload_file_exceeds_limit');
                    break;
                case 3:
                    $this->setError('upload_file_partial');
                    break;
                case 4:
                    $this->setError('upload_no_file_selected');
                    break;
                default:
                    $this->setError('upload_no_file_selected');
                    break;
            }

            return false;
        }

        // Set the uploaded data as class variables
        $this->file_temp = $_FILES[$field]['tmp_name'];     
        $this->file_name = $_FILES[$field]['name'];
        $this->file_size = $_FILES[$field]['size'];     
        $this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $_FILES[$field]['type']);
        $this->file_type = strtolower($this->file_type);
        $this->file_ext  = $this->getExtension($_FILES[$field]['name']);
        
        // Convert the file size to kilobytes
        if ($this->file_size > 0) {
            $this->file_size = round($this->file_size/1024, 2);
        }

        // Is the file type allowed to be uploaded?
        if ( ! $this->isAllowedFiletype()) {
            $this->setError('upload_invalid_filetype');
            return false;
        }

        // Is the file size within the allowed maximum?
        if ( ! $this->isAllowedFilesize()) {
            $this->setError('upload_invalid_filesize');
            return false;
        }

        // Are the image dimensions within the allowed size?
        // Note: This can fail if the server has an open_basdir restriction.
        if ( ! $this->isAllowedDimensions()) {
            $this->setError('upload_invalid_dimensions');
            return false;
        }

        // Sanitize the file name for security
        $this->file_name = $this->cleanFileName($this->file_name);

        // Remove white spaces in the name
        if ($this->remove_spaces == true) {
            $this->file_name = preg_replace("/\s+/", "_", $this->file_name);
        }

        /*
         * Validate the file name
         * This function appends an number onto the end of
         * the file if one with the same name already exists.
         * If it returns false there was a problem.
         */
        $this->orig_name = $this->file_name;

        if ($this->overwrite == false) {
            $this->file_name = $this->setFilename($this->upload_path, $this->file_name);
            
            if ($this->file_name === false) {
                return false;
            }
        }

        /*
         * Move the file to the final destination
         * To deal with different server configurations
         * we'll attempt to use copy() first.  If that fails
         * we'll use move_uploaded_file().  One of the two should
         * reliably work in most environments
         */
        if ( ! @copy($this->file_temp, $this->upload_path . $this->file_name)) {
            if ( ! @move_uploaded_file($this->file_temp, $this->upload_path . $this->file_name)) {
                 $this->setError('upload_destination_error');
                 return false;
            }
        }
        
        /*
         * Run the file through the XSS hacking filter
         * This helps prevent malicious code from being
         * embedded within a file.  Scripts can easily
         * be disguised as images or other file types.
         */
        if ($this->xss_clean == true)
        {
            $this->doXssClean();
        }

        /*
         * Set the finalized image dimensions
         * This sets the image width/height (assuming the
         * file was an image).  We use this information
         * in the "data" function.
         */
        $this->setImageProperties($this->upload_path . $this->file_name);

        return true;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Finalized Data Array
     *  
     * Returns an associative array containing all of the information
     * related to the upload, allowing the developer easy access in one array.
     *
     * @access  public
     * @return  array
     */ 
    function data()
    {
        return array (
                        'file_name'         => $this->file_name,
                        'file_type'         => $this->file_type,
                        'file_path'         => $this->upload_path,
                        'full_path'         => $this->upload_path.$this->file_name,
                        'raw_name'          => str_replace($this->file_ext, '', $this->file_name),
                        'orig_name'         => $this->orig_name,
                        'file_ext'          => $this->file_ext,
                        'file_size'         => $this->file_size,
                        'is_image'          => $this->isImage(),
                        'image_width'       => $this->image_width,
                        'image_height'      => $this->image_height,
                        'image_type'        => $this->image_type,
                        'image_size_str'    => $this->image_size_str,
                    );
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Upload Path
     *
     * @access  public
     * @param   string
     * @return  void
     */ 
    function setUploadPath($path)
    {
        $this->upload_path = $path;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set the file name
     *
     * This function takes a filename/path as input and looks for the
     * existence of a file with the same name. If found, it will append a
     * number to the end of the filename to avoid overwriting a pre-existing file.
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  string
     */ 
    function setFilename($path, $filename)
    {
        if ($this->encrypt_name == true) {       
            mt_srand();
            $filename = md5(uniqid(mt_rand())).$this->file_ext;             
        }
    
        if ( ! file_exists($path.$filename)) {
            return $filename;
        }
    
        $filename = str_replace($this->file_ext, '', $filename);
        
        $new_filename = '';
        for ($i = 1; $i < 100; $i++) {           
            if ( ! file_exists($path.$filename.$i.$this->file_ext)) {
                $new_filename = $filename.$i.$this->file_ext;
                break;
            }
        }

        if ($new_filename == '') {
            $this->setError('upload_bad_filename');
            return false;
        } else {
            return $new_filename;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Maximum File Size
     *
     * @access  public
     * @param   integer
     * @return  void
     */ 
    function setMaxFilesize($n)
    {
        $this->max_size = ( ! eregi("^[[:digit:]]+$", $n)) ? 0 : $n;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Maximum Image Width
     *
     * @access  public
     * @param   integer
     * @return  void
     */ 
    function setMaxWidth($n)
    {
        $this->max_width = ( ! eregi("^[[:digit:]]+$", $n)) ? 0 : $n;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Maximum Image Height
     *
     * @access  public
     * @param   integer
     * @return  void
     */ 
    function setMaxHeight($n)
    {
        $this->max_height = ( ! eregi("^[[:digit:]]+$", $n)) ? 0 : $n;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Allowed File Types
     *
     * @access  public
     * @param   string
     * @return  void
     */ 
    function setAllowedTypes($types)
    {
        $this->allowed_types = explode('|', $types);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Image Properties
     *
     * Uses GD to determine the width/height/type of image
     *
     * @access  public
     * @param   string
     * @return  void
     */ 
    function setImageProperties($path = '')
    {
        if ( ! $this->is_image()) {
            return;
        }

        if (function_exists('getimagesize')) {
            if (false !== ($D = @getimagesize($path))) {   
                $types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

                $this->image_width      = $D['0'];
                $this->image_height     = $D['1'];
                $this->image_type       = ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
                $this->image_size_str   = $D['3'];  // string containing height and width
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set XSS Clean
     *
     * Enables the XSS flag so that the file that was uploaded
     * will be run through the XSS filter.
     *
     * @access  public
     * @param   bool
     * @return  void
     */
    function setXssClean($flag = false)
    {
        $this->xss_clean = ($flag == true) ? true : false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validate the image
     *
     * @access  public
     * @return  bool
     */ 
    function isImage()
    {
        $img_mimes = array(
                            'image/gif',
                            'image/jpg',
                            'image/jpe',
                            'image/jpeg',
                            'image/pjpeg',
                            'image/png',
                            'image/x-png'
                           );


        return (in_array($this->file_type, $img_mimes, true)) ? true : false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Verify that the filetype is allowed
     *
     * @access  public
     * @return  bool
     */ 
    function isAllowedFiletype()
    {
        if (count($this->allowed_types) == 0) {
            $this->setError('upload_no_file_types');
            return false;
        }
                
        foreach ($this->allowed_types as $val) {
            $mime = $this->mimesTypes(strtolower($val));
        
            if (is_array($mime)) {
                if (in_array($this->file_type, $mime, true)) {
                    return true;
                }
            } else {
                if ($mime == $this->file_type) {
                    return true;
                }   
            }       
        }
        
        return false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Verify that the file is within the allowed size
     *
     * @access  public
     * @return  bool
     */ 
    function isAllowedFilesize()
    {
        if ($this->max_size != 0  AND  $this->file_size > $this->max_size) {
            return false;
        } else {
            return true;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Verify that the image is within the allowed width/height
     *
     * @access  public
     * @return  bool
     */ 
    function isAllowedDimensions()
    {
        if ( ! $this->isImage()) {
            return true;
        }

        if (function_exists('getimagesize')) {
            $D = @getimagesize($this->file_temp);

            if ($this->max_width > 0 AND $D['0'] > $this->max_width) {
                return false;
            }

            if ($this->max_height > 0 AND $D['1'] > $this->max_height) {
                return false;
            }

            return true;
        }

        return true;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validate Upload Path
     *
     * Verifies that it is a valid upload path with proper permissions.
     *
     *
     * @access  public
     * @return  bool
     */ 
    function validateUploadPath()
    {
        if ($this->upload_path == '') {
            $this->setError('upload_no_filepath');
            return false;
        }
        
        if (function_exists('realpath') AND @realpath($this->upload_path) !== false) {
            $this->upload_path = str_replace("\\", "/", realpath($this->upload_path));
        }

        if ( ! @is_dir($this->upload_path)) {
            $this->setError('upload_no_filepath');
            return false;
        }

        if ( ! is_writable($this->upload_path)) {
            $this->setError('upload_not_writable');
            return false;
        }

        $this->upload_path = preg_replace("/(.+?)\/*$/", "\\1/",  $this->upload_path);
        return true;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Extract the file extension
     *
     * @access  public
     * @param   string
     * @return  string
     */ 
    function getExtension($filename)
    {
        $x = explode('.', $filename);
        return '.'.end($x);
    }   
    
    // --------------------------------------------------------------------
    
    /**
     * Clean the file name for security
     *
     * @access  public
     * @param   string
     * @return  string
     */     
    function cleanFileName($filename)
    {
        $bad = array(
                        "<!--",
                        "-->",
                        "'",
                        "<",
                        ">",
                        '"',
                        '&',
                        '$',
                        '=',
                        ';',
                        '?',
                        '/',
                        "%20",
                        "%22",
                        "%3c",      // <
                        "%253c",    // <
                        "%3e",      // >
                        "%0e",      // >
                        "%28",      // (
                        "%29",      // )
                        "%2528",    // (
                        "%26",      // &
                        "%24",      // $
                        "%3f",      // ?
                        "%3b",      // ;
                        "%3d"       // =
                    );
                    
        foreach ($bad as $val) {
            $filename = str_replace($val, '', $filename);
        }

        return $filename;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Runs the file through the XSS clean function
     *
     * This prevents people from embedding malicious code in their files.
     * I'm not sure that it won't negatively affect certain files in unexpected ways,
     * but so far I haven't found that it causes trouble.
     *
     * @access  public
     * @return  void
     */ 
    function doXssClean()
    {       
        $file = $this->upload_path.$this->file_name;
        
        if (filesize($file) == 0) {
            return false;
        }
    
        if ( ! $fp = @fopen($file, 'rb')) {
            return false;
        }
            
        flock($fp, LOCK_EX);

        $data = fread($fp, filesize($file));
        
        $CI =& get_instance();  
        $data = $CI->input->xss_clean($data);

        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set an error message
     *
     * @access  public
     * @param   string
     * @return  void
     */ 
    function setError($msg)
    {
        $CI =& get_instance();  
        $CI->lang->load('upload');
        
        if (is_array($msg)) {
            foreach ($msg as $val) {
                $msg = ($CI->lang->line($val) == false) ? $val : $CI->lang->line($val);             
                $this->error_msg[] = $msg;
            }       
        } else {
            $msg = ($CI->lang->line($msg) == false) ? $msg : $CI->lang->line($msg);
            $this->error_msg[] = $msg;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Display the error message
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  string
     */ 
    function displayErrors($open = '<p>', $close = '</p>')
    {
        $str = '';
        foreach ($this->error_msg as $val) {
            $str .= $open.$val.$close;
        }
    
        return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * List of Mime Types
     *
     * This is a list of mime types.  We use it to validate
     * the "allowed types" set by the developer
     *
     * @access  public
     * @param   string
     * @return  string
     */ 
    function mimesTypes($mime)
    {
        if (count($this->mimes) == 0) {
            if (@include(APP_PATH . 'config/mimes.php')) {
                $this->mimes = $mimes;
                unset($mimes);
            }
        }
    
        return ( ! isset($this->mimes[$mime])) ? false : $this->mimes[$mime];
    }

} // End Upload Class
