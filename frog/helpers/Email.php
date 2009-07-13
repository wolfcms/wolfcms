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
 * Simple Email library
 *
 * Permits email to be sent using Mail, Sendmail, or SMTP.
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
 * Email Class
 *
 * Permits email to be sent using Mail, Sendmail, or SMTP.
 */
class Email
{

    public $useragent      = "Frog framework";
    public $mailpath       = "/usr/sbin/sendmail"; // Sendmail path
    public $protocol       = "mail";   // mail/sendmail/smtp
    public $smtp_host      = "";       // SMTP Server.  Example: mail.earthlink.net
    public $smtp_user      = "";       // SMTP Username
    public $smtp_pass      = "";       // SMTP Password
    public $smtp_port      = "25";     // SMTP Port
    public $smtp_timeout   = 5;        // SMTP Timeout in seconds
    public $wordwrap       = true;     // true/false  Turns word-wrap on/off
    public $wrapchars      = "76";     // Number of characters to wrap at.
    public $mailtype       = "text";   // text/html  Defines email formatting
    public $charset        = "utf-8";  // Default char set: iso-8859-1 or us-ascii
    public $multipart      = "mixed";  // "mixed" (in the body) or "related" (separate)
    public $alt_message    = '';       // Alternative message for HTML emails
    public $validate       = false;    // true/false.  Enables email validation
    public $priority       = "3";      // Default priority (1 - 5)
    public $newline        = "\n";     // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
    public $bcc_batch_mode = false;    // true/false  Turns on/off Bcc batch feature
    public $bcc_batch_size = 200;      // If bcc_batch_mode = true, sets max number of Bccs in each batch
    private $_subject       = "";
    private $_body          = "";
    private $_finalbody     = "";
    private $_alt_boundary  = "";
    private $_atc_boundary  = "";
    private $_header_str    = "";
    private $_smtp_connect  = "";
    private $_encoding      = "8bit";
    private $_safe_mode     = false;
    private $_IP            = false;
    private $_smtp_auth     = false;
    private $_replyto_flag  = false;
    private $_debug_msg     = array();
    private $_recipients    = array();
    private $_cc_array      = array();
    private $_bcc_array     = array();
    private $_headers       = array();
    private $_attach_name   = array();
    private $_attach_type   = array();
    private $_attach_disp   = array();
    private $_protocols     = array('mail', 'sendmail', 'smtp');
    private $_base_charsets = array('iso-8859-1', 'us-ascii');
    private $_bit_depths    = array('7bit', '8bit');
    private $_priorities    = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');  


    /**
     * Constructor - Sets Email Preferences
     *
     * The constructor can be passed an array of config values
     */ 
    public function __construct($config = array())
    {
        if (count($config) > 0) {
            $this->initialize($config);
        }
    }

    /**
     * Initialize preferences
     *
     * @param   array configs
     *
     * @return  void
     */ 
    public function initialize($configs = array())
    {
        $this->clear();
        foreach ($configs as $key => $val) {
            if (isset($this->$key)) {
                $method = 'set_'.$key;
                
                if (method_exists($this, $method)) {
                    $this->$method($val);
                } else {
                    $this->$key = $val;
                }           
            }
        }
        $this->_smtp_auth = ($this->smtp_user == '' && $this->smtp_pass == '') ? false : true;          
        $this->_safe_mode = (@ini_get("safe_mode") == 0) ? false : true;
    }

    /**
     * Initialize the Email Data
     *
     * @param  boolean clear attachment
     *
     * @return  void
     */ 
    public function clear($clear_attachments = false)
    {
        $this->_subject      = "";
        $this->_body         = "";
        $this->_finalbody    = "";
        $this->_header_str   = "";
        $this->_replyto_flag = false;
        $this->_recipients   = array();
        $this->_headers      = array();
        $this->_debug_msg    = array();
        
        $this->_set_header('User-Agent', $this->useragent);             
        $this->_set_header('Date', $this->_setDate());
        
        if ($clear_attachments !== false) {
            $this->_attach_name = array();
            $this->_attach_type = array();
            $this->_attach_disp = array();
        }           
    }

    /**
     * Set FROM
     *
     * @param   string
     * @param   string
     *
     * @return  void
     */ 
    public function from($from, $name = '')
    {
        if (preg_match( '/\<(.*)\>/', $from, $match))
            $from = $match['1'];

        if ($this->validate) {
            $this->validateEmail($this->_str2array($from));
        }
        
        if ($name != '' && substr($name, 0, 1) != '"') {
            $name = '"'.$name.'"';
        }
    
        $this->_setHeader('From', $name.' <'.$from.'>');
        $this->_setHeader('Return-Path', '<'.$from.'>');
    }

    /**
     * Set Reply-to
     *
     * @param   string
     * @param   string
     * @return  void
     */ 
    public function replyTo($replyto, $name = '')
    {
        if (preg_match( '/\<(.*)\>/', $replyto, $match))
            $replyto = $match['1'];

        if ($this->validate)
            $this->validateEmail($this->_str2array($replyto));  

        if ($name == '') {
            $name = $replyto;
        }

        if (substr($name, 0, 1) != '"') {
            $name = '"'.$name.'"';
        }

        $this->_setHeader('Reply-To', $name.' <'.$replyto.'>');
        $this->_replyto_flag = true;
    }

    /**
     * Set Recipients
     *
     * @param   string
     *
     * @return  void
     */ 
    public function to($to)
    {
        $to = $this->_str2array($to);
        $to = $this->cleanEmail($to);
    
        if ($this->validate) {
            $this->validateEmail($to);
        }
        
        if ($this->_getProtocol() != 'mail') {
            $this->_setHeader('To', implode(", ", $to));
        }
        
        switch ($this->_getProtocol()) {
            case 'smtp':
                $this->_recipients = $to;
                break;
            case 'sendmail':
                $this->_recipients = implode(", ", $to);
                break;
            case 'mail':
                $this->_recipients = implode(", ", $to);
                break;
        }   
    }

    /**
     * Set CC
     *
     * @param   string
     * @return  void
     */ 
    function cc($cc)
    {   
        $cc = $this->_str2array($cc);
        $cc = $this->cleanEmail($cc);

        if ($this->validate) {
            $this->validateEmail($cc);
        }
        
        $this->_setHeader('Cc', implode(", ", $cc));
        
        if ($this->_getProtocol() == "smtp") {
            $this->_cc_array = $cc;
        }
    }

    /**
     * Set BCC
     *
     * @param string bcc
     * @param string limit
     *
     * @return void
     */ 
    public function bcc($bcc, $limit = '')
    {
        if ($limit != '' && is_numeric($limit)) {
            $this->bcc_batch_mode = true;
            $this->bcc_batch_size = $limit;
        }

        $bcc = $this->_str2array($bcc);
        $bcc = $this->cleanEmail($bcc);
        
        if ($this->validate) {
            $this->validateEmail($bcc);
        }
        
        if (($this->_getProtocol() == "smtp") || ($this->bcc_batch_mode && count($bcc) > $this->bcc_batch_size)) {
            $this->_bcc_array = $bcc;
        } else {
            $this->_setHeader('Bcc', implode(", ", $bcc));
        }
    }

    /**
     * Set Email Subject
     *
     * @param string subject
     *
     * @return void
     */ 
    public function subject($subject)
    {
        $subject = preg_replace("/(\r\n)|(\r)|(\n)/", "", $subject);
        $subject = preg_replace("/(\t)/", " ", $subject);
        
        $this->_setHeader('Subject', trim($subject));      
    }

    /**
     * Set Body
     *
     * @param   string
     * @return  void
     */ 
    public function message($body)
    {
        $this->_body = stripslashes(rtrim(str_replace("\r", "", $body)));   
    }   

    /**
     * Assign file attachments
     *
     * @param   string filename
     * @param  string disposition
     *
     * @return void
     */     
    public function attach($filename, $disposition = 'attachment')
    {           
        $this->_attach_name[] = $filename;
        $this->_attach_type[] = $this->_mimeTypes(next(explode('.', basename($filename))));
        $this->_attach_disp[] = $disposition; // Can also be 'inline'  Not sure if it matters
    }

    /**
     * Add a Header Item
     *
     * @param   string
     * @param   string
     *
     * @return  void
     */ 
    private function _setHeader($header, $value)
    {
        $this->_headers[$header] = $value;
    }

    /**
     * Convert a String to an Array
     *
     * @param   string
     *
     * @return  array
     */ 
    private function _str2array($email)
    {
        if ( ! is_array($email)) {
            
            if (ereg(',$', $email)) {
                $email = substr($email, 0, -1);
            }
            
            if (ereg('^,', $email)) {
                $email = substr($email, 1); 
            }

            if (ereg(',', $email)) {                   
                $x = explode(',', $email);
                $email = array();
                
                for ($i = 0; $i < count($x); $i ++)
                    $email[] = trim($x[$i]);
            } else {               
                $email = trim($email);
                settype($email, "array");
            }
        }
        return $email;
    }

    /**
     * Set Multipart Value
     *
     * @param   string
     *
     * @return  void
     */ 
    public function setAltMessage($str = '')
    {
        $this->alt_message = ($str == '') ? '' : $str;
    }

    /**
     * Set Mailtype
     *
     * @param   string
     *
     * @return  void
     */ 
    public function setMailtype($type = 'text')
    {
        $this->mailtype = ($type == 'html') ? 'html' : 'text';
    }

    /**
     * Set Wordwrap
     *
     * @param   string
     *
     * @return  void
     */ 
    public function setWordwrap($wordwrap = true)
    {
        $this->wordwrap = ($wordwrap === false) ? false : true;
    }

    /**
     * Set Protocol
     *
     * @param   string
     * @return  void
     */ 
    function setProtocol($protocol = 'mail')
    {
        $this->protocol = ( ! in_array($protocol, $this->_protocols, true)) ? 'mail' : strtolower($protocol);
    }

    /**
     * Set Priority
     *
     * @param   integer
     *
     * @return  void
     */ 
    function setPriority($n = 3)
    {
        if ( ! is_numeric($n)) {
            $this->priority = 3;
            return;
        }
    
        if ($n < 1 || $n > 5) {
            $this->priority = 3;
            return;
        }
    
        $this->priority = $n;
    }

    /**
     * Set Newline Character
     *
     * @param   string
     *
     * @return  void
     */ 
    function setNewline($newline = "\n")
    {
        if ($newline != "\n" || $newline != "\r\n" || $newline != "\r") {
            $this->newline = "\n"; 
            return;
        }
    
        $this->newline = $newline; 
    }

    /**
     * Set Message Boundary
     *
     * @return  void
     */ 
    private function _setBoundaries()
    {
        $this->_alt_boundary = "B_ALT_".uniqid(''); // multipart/alternative
        $this->_atc_boundary = "B_ATC_".uniqid(''); // attachment boundary
    }

    /**
     * Get the Message ID
     *
     * @return  string
     */ 
    private function _getMessageId()
    {
        $from = $this->_headers['Return-Path'];
        $from = str_replace(">", "", $from);
        $from = str_replace("<", "", $from);
    
        return  "<".uniqid('').strstr($from, '@').">";  
    }

    /**
     * Get Mail Protocol
     *
     * @param   bool
     * @return  string
     */ 
    private function _getProtocol($return = true)
    {
        $this->protocol = strtolower($this->protocol);
        $this->protocol = ( ! in_array($this->protocol, $this->_protocols, true)) ? 'mail' : $this->protocol;
        
        if ($return == true) {
            return $this->protocol;
        }
    }

    /**
     * Get Mail Encoding
     *
     * @param   bool
     * @return  string
     */ 
    private function _getEncoding($return = true)
    {       
        $this->_encoding = ( ! in_array($this->_encoding, $this->_bit_depths)) ? '7bit' : $this->_encoding;
        
        if ( ! in_array($this->charset, $this->_base_charsets, true)) {
            $this->_encoding = "8bit";
        }
            
        if ($return == true) {
            return $this->_encoding;
        }
    }

    /**
     * Get content type (text/html/attachment)
     *
     * @return  string
     */ 
    private function _getContentType()
    {   
        if  ($this->mailtype == 'html' &&  count($this->_attach_name) == 0) {
             return 'html';
        } else if ($this->mailtype == 'html' &&  count($this->_attach_name)  > 0) {
            return 'html-attach';                   
        } else if ($this->mailtype == 'text' &&  count($this->_attach_name)  > 0) {
            return 'plain-attach';
        } else {
            return 'plain';
        } 
    }

    /**
     * Set RFC 822 Date
     *
     * @return  string
     */ 
    private function _setDate()
    {
        $timezone = date("Z");
        $operator = (substr($timezone, 0, 1) == '-') ? '-' : '+';
        $timezone = abs($timezone);
        $timezone = ($timezone/3600) * 100 + ($timezone % 3600) /60;
        
        return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $timezone);
    }

    /**
     * Mime message
     *
     * @return  string
     */ 
    private function _getMimeMessage()
    {
        return "This is a multi-part message in MIME format.".$this->newline."Your email application may not support this format.";
    }

    /**
     * Validate Email Address
     *
     * @param   string
     * @return  bool
     */ 
    public function validateEmail($email)
    {   
        if ( ! is_array($email)) {
            $email = array($email);
        }

        foreach ($email as $val) {
            if ( ! $this->validEmail($val)) {
                log_error('Email address invalid: "'.$val.'"');   
                return false;
            }
        }
    }   

    /**
     * Email Validation
     *
     * @param   string
     * @return  bool
     */ 
    function validEmail($address)
    {
        return (bool) preg_match(EMAIL_FORMAT, $address);
    }

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @param   string
     * @return  string
     */ 
    function cleanEmail($email)
    {
        if ( ! is_array($email)) {
            if (preg_match('/\<(.*)\>/', $email, $match))
                return $match['1'];
            else
                return $email;
        }
            
        $clean_email = array();

        for ($i=0; $i < count($email); $i++) {
            if (preg_match( '/\<(.*)\>/', $email[$i], $match))
                $clean_email[] = $match['1'];
            else
                $clean_email[] = $email[$i];
        }
        
        return $clean_email;
    }

    /**
     * Build alternative plain text message
     *
     * This function provides the raw message for use
     * in plain-text headers of HTML-formatted emails.
     * If the user hasn't specified his own alternative message
     * it creates one by stripping the HTML
     *
     * @return  string
     */ 
    private function _getAltMessage()
    {
        if ($this->alt_message != "") {
            return $this->_wordwrap($this->alt_message, '76');
        }
    
        if (eregi( '\<body(.*)\</body\>', $this->_body, $match)) {
            $body = $match['1'];
            $body = substr($body, strpos($body, ">") + 1);
        } else {
            $body = $this->_body;
        }
        
        $body = trim(strip_tags($body));
        $body = preg_replace( '#<!--(.*)--\>#', "", $body);
        $body = str_replace("\t", "", $body);
        
        for ($i = 20; $i >= 3; $i--) {
            $n = "";
            
            for ($x = 1; $x <= $i; $x ++) {
                 $n .= "\n";
            }
            $body = str_replace($n, "\n\n", $body); 
        }

        return $this->_wordwrap($body, '76');
    }

    /**
     * Word Wrap
     *
     * @param   string
     * @param   integer
     * @return  string
     */ 
    private function _wordwrap($str, $charlim = '')
    {
        // Se the character limit
        if ($charlim == '') {
            $charlim = ($this->wrapchars == "") ? "76" : $this->wrapchars;
        }
        
        // Reduce multiple spaces
        $str = preg_replace("| +|", " ", $str);
        
        // Standardize newlines
        $str = preg_replace("/\r\n|\r/", "\n", $str);
        
        // If the current word is surrounded by {unwrap} tags we'll 
        // strip the entire chunk and replace it with a marker.
        $unwrap = array();
        if (preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches)) {
            for ($i = 0; $i < count($matches['0']); $i++) {
                $unwrap[] = $matches['1'][$i];              
                $str = str_replace($matches['1'][$i], "{{unwrapped".$i."}}", $str);
            }
        }
        
        // Use PHP's native function to do the initial wordwrap.  
        // We set the cut flag to false so that any individual words that are 
        // too long get left alone.  In the next step we'll deal with them.
        $str = wordwrap($str, $charlim, "\n", false);
        
        // Split the string into individual lines of text and cycle through them
        $output = "";
        foreach (explode("\n", $str) as $line) {
            // Is the line within the allowed character count?
            // If so we'll join it to the output and continue
            if (strlen($line) <= $charlim) {
                $output .= $line.$this->newline;            
                continue;
            }
                
            $temp = '';
            while((strlen($line)) > $charlim) {
                // If the over-length word is a URL we won't wrap it
                if (preg_match("!\[url.+\]|://|wwww.!", $line)) {
                    break;
                }

                // Trim the word down
                $temp .= substr($line, 0, $charlim-1);
                $line = substr($line, $charlim-1);
            }
            
            // If $temp contains data it means we had to split up an over-length 
            // word into smaller chunks so we'll add it back to our current line
            if ($temp != '') {
                $output .= $temp.$this->newline.$line;
            } else {
                $output .= $line;
            }

            $output .= $this->newline;
        }

        // Put our markers back
        if (count($unwrap) > 0) {   
            foreach ($unwrap as $key => $val) {
                $output = str_replace("{{unwrapped".$key."}}", $val, $output);
            }
        }

        return $output; 
    }

    /**
     * Build final headers
     *
     * @param   string
     * @return  string
     */ 
    private function _buildHeaders()
    {
        $this->_setHeader('X-Sender', $this->cleanEmail($this->_headers['From']));
        $this->_setHeader('X-Mailer', $this->useragent);       
        $this->_setHeader('X-Priority', $this->_priorities[$this->priority - 1]);
        $this->_setHeader('Message-ID', $this->_getMessageId());     
        $this->_setHeader('Mime-Version', '1.0');
    }

    /**
     * Write Headers as a string
     *
     * @return  void
     */     
    private function _writeHeaders()
    {
        if ($this->protocol == 'mail') {       
            $this->_subject = $this->_headers['Subject'];
            unset($this->_headers['Subject']);
        }   

        reset($this->_headers);
        $this->_header_str = "";
                
        foreach($this->_headers as $key => $val) {
            $val = trim($val);
        
            if ($val != "") {
                $this->_header_str .= $key.": ".$val.$this->newline;
            }
        }
        
        if ($this->_getProtocol() == 'mail') {
            $this->_header_str = substr($this->_header_str, 0, -1);
        }            
    }

    /**
     * Build Final Body and attachments
     *
     * @return  void
     */ 
    private function _buildMessage()
    {
        if ($this->wordwrap === true  &&  $this->mailtype != 'html') {
            $this->_body = $this->_wordwrap($this->_body);
        }
    
        $this->_setBoundaries();
        $this->_writeHeaders();
        
        $hdr = ($this->_getProtocol() == 'mail') ? $this->newline : '';
            
        switch ($this->_getContentType()) {
            case 'plain':
                            
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_getEncoding();
                
                if ($this->_getProtocol() == 'mail') {
                    $this->_header_str .= $hdr;
                    $this->_finalbody = $this->_body;
                    return;
                }
                
                $hdr .= $this->newline . $this->newline . $this->_body;
                
                $this->_finalbody = $hdr;
                return;
            
            break;
            case 'html' :
                            
                $hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline;
                $hdr .= $this->_getMimeMessage() . $this->newline . $this->newline;
                $hdr .= "--" . $this->_alt_boundary . $this->newline;
                
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_getEncoding() . $this->newline . $this->newline;
                $hdr .= $this->_getAltMessage() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
            
                $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: quoted/printable";
                
                if ($this->_getProtocol() == 'mail') {
                    $this->_header_str .= $hdr;
                    $this->_finalbody = $this->_body . $this->newline . $this->newline . "--" . $this->_alt_boundary . "--";
                    return;
                }
                
                $hdr .= $this->newline . $this->newline;
                $hdr .= $this->_body . $this->newline . $this->newline . "--" . $this->_alt_boundary . "--";

                $this->_finalbody = $hdr;
                return;
        
            break;
            case 'plain-attach' :
    
                $hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"" . $this->_atc_boundary."\"" . $this->newline;
                $hdr .= $this->_getMimeMessage() . $this->newline . $this->newline;
                $hdr .= "--" . $this->_atc_boundary . $this->newline;
    
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_getEncoding();
                
                if ($this->_getProtocol() == 'mail') {
                    $this->_header_str .= $hdr;     
                    
                    $body  = $this->_body . $this->newline . $this->newline;
                }
                
                $hdr .= $this->newline . $this->newline;
                $hdr .= $this->_body . $this->newline . $this->newline;

            break;
            case 'html-attach' :
            
                $hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"" . $this->_atc_boundary."\"" . $this->newline;
                $hdr .= $this->_getMimeMessage() . $this->newline . $this->newline;
                $hdr .= "--" . $this->_atc_boundary . $this->newline;
    
                $hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline .$this->newline;
                $hdr .= "--" . $this->_alt_boundary . $this->newline;
                
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_getEncoding() . $this->newline . $this->newline;
                $hdr .= $this->_getAltMessage() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
    
                $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: quoted/printable";
                
                if ($this->_getProtocol() == 'mail') {
                    $this->_header_str .= $hdr; 
                    
                    $body  = $this->_body . $this->newline . $this->newline;
                    $body .= "--" . $this->_alt_boundary . "--" . $this->newline . $this->newline;              
                }
                
                $hdr .= $this->newline . $this->newline;
                $hdr .= $this->_body . $this->newline . $this->newline;
                $hdr .= "--" . $this->_alt_boundary . "--" . $this->newline . $this->newline;

            break;
        }

        $attachment = array();

        $z = 0;
        
        for ($i=0; $i < count($this->_attach_name); $i++) {
            $filename = $this->_attach_name[$i];
            $basename = basename($filename);
            $ctype = $this->_attach_type[$i];
                        
            if ( ! file_exists($filename)) {
                return;
            }           

            $h  = "--".$this->_atc_boundary.$this->newline;
            $h .= "Content-type: ".$ctype."; ";
            $h .= "name=\"".$basename."\"".$this->newline;
            $h .= "Content-Disposition: ".$this->_attach_disp[$i].";".$this->newline;
            $h .= "Content-Transfer-Encoding: base64".$this->newline;

            $attachment[$z++] = $h;
            $file = filesize($filename) +1;
            
            if ( ! $fp = fopen($filename, 'r')) {
                return;
            }
            
            $attachment[$z++] = chunk_split(base64_encode(fread($fp, $file)));
            fclose($fp);
        }

        if ($this->_getProtocol() == 'mail') {
            $this->_finalbody = $body . implode($this->newline, $attachment).$this->newline."--".$this->_atc_boundary."--";
            return;
        }
        
        $this->_finalbody = $hdr.implode($this->newline, $attachment).$this->newline."--".$this->_atc_boundary."--";
    }

    /**
     * Send Email
     *
     * @return  bool
     */ 
    function send()
    {           
        if ($this->_replyto_flag == false) {
            $this->replyTo($this->_headers['From']);
        }
    
        if (( ! isset($this->_recipients) && ! isset($this->_headers['To']))  &&
            ( ! isset($this->_bcc_array) && ! isset($this->_headers['Bcc'])) &&
            ( ! isset($this->_headers['Cc']))) {
            return false;
        }

        $this->_buildHeaders();
        
        if ($this->bcc_batch_mode  &&  count($this->_bcc_array) > 0) {
            if (count($this->_bcc_array) > $this->bcc_batch_size)
                return $this->batchBccSend();
        }
        
        $this->_buildMessage();
                        
        if ( ! $this->_spoolEmail())
            return false;
        else
            return true;
    }

    /**
     * Batch Bcc Send.  Sends groups of BCCs in batches
     *
     * @access  public
     * @return  bool
     */ 
    function batchBccSend()
    {
        $float = $this->bcc_batch_size -1;
        
        $flag = 0;
        $set = "";
        
        $chunk = array();       
        
        for ($i = 0; $i < count($this->_bcc_array); $i++) {
            if (isset($this->_bcc_array[$i]))
                $set .= ", ".$this->_bcc_array[$i];
        
            if ($i == $float) {   
                $chunk[] = substr($set, 1);
                $float = $float + $this->bcc_batch_size;
                $set = "";
            }
            
            if ($i == count($this->_bcc_array)-1)
                    $chunk[] = substr($set, 1); 
        }

        for ($i = 0; $i < count($chunk); $i++) {
            unset($this->_headers['Bcc']);
            unset($bcc);

            $bcc = $this->_str2array($chunk[$i]);
            $bcc = $this->cleanEmail($bcc);
    
            if ($this->protocol != 'smtp')
                $this->_setHeader('Bcc', implode(", ", $bcc));
            else
                $this->_bcc_array = $bcc;
            
            $this->_buildMessage();
            $this->_spoolEmail();
        }
    }

    /**
     * Unwrap special elements
     *
     * @access  private
     * @return  void
     */ 
    function _unwrapSpecials()
    {
        $this->_finalbody = preg_replace_callback("/\{unwrap\}(.*?)\{\/unwrap\}/si", array($this, '_removeNlCallback'), $this->_finalbody);
    }

    /**
     * Strip line-breaks via callback
     *
     * @access  private
     * @return  string
     */ 
    function _removeNlCallback($matches)
    {
        return preg_replace("/(\r\n)|(\r)|(\n)/", "", $matches['1']);
    }

    /**
     * Spool mail to the mail server
     *
     * @return  bool
     */ 
    private function _spoolEmail()
    {
        $this->_unwrapSpecials();

        switch ($this->_getProtocol()) {
            case 'mail':
                if ( ! $this->_sendWithMail()) {
                    return false;
                }
                break;
            case 'sendmail':
                if ( ! $this->_sendWithSendmail()) {
                    return false;
                }
                break;
            case 'smtp':
                if ( ! $this->_sendWithSmtp()) {
                    return false;
                }
                break;
        }

        return true;
    }   

    /**
     * Send using mail()
     *
     * @return  bool
     */ 
    private function _sendWithMail()
    {   
        if ($this->_safe_mode == true) {
            if ( ! mail($this->_recipients, $this->_subject, $this->_finalbody, $this->_header_str))
                return false;
            else
                return true;
        } else {
            if ( ! mail($this->_recipients, $this->_subject, $this->_finalbody, $this->_header_str, "-f".$this->cleanEmail($this->_headers['From'])))
                return false;
            else
                return true;
        }
    }

    /**
     * Send using Sendmail
     *
     * @return  bool
     */ 
    private function _sendWithSendmail()
    {
        $fp = @popen($this->mailpath . " -oi -f ".$this->cleanEmail($this->_headers['From'])." -t", 'w');
        
        if ( ! is_resource($fp)) {                               
            return false;
        }
        
        fputs($fp, $this->_header_str);     
        fputs($fp, $this->_finalbody);
        pclose($fp) >> 8 & 0xFF;
        
        return true;
    }

    /**
     * Send using SMTP
     *
     * @return  bool
     */ 
    private function _sendWithSmtp()
    {   
        if ($this->smtp_host == '') {   
            return false;
        }

        $this->_smtpConnect();
        $this->_smtpAuthenticate();
        
        $this->_sendCommand('from', $this->cleanEmail($this->_headers['From']));

        foreach($this->_recipients as $val)
            $this->_sendCommand('to', $val);
            
        if (count($this->_cc_array) > 0) {
            foreach($this->_cc_array as $val) {
                if ($val != "")
                $this->_sendCommand('to', $val);
            }
        }

        if (count($this->_bcc_array) > 0) {
            foreach($this->_bcc_array as $val) {
                if ($val != "")
                $this->_sendCommand('to', $val);
            }
        }
        
        $this->_sendCommand('data');

        $this->_sendData($this->_header_str . $this->_finalbody);
        
        $this->_send_data('.');

        $reply = $this->_getSmtpData();
        
        log_error($reply);
        
        if (substr($reply, 0, 3) != '250') {
            return false;
        }

        $this->_sendCommand('quit');
        return true;
    }   

    /**
     * SMTP Connect
     *
     * @param   string
     * @return  string
     */ 
    private function _smtpConnect()
    {
    
        $this->_smtp_connect = fsockopen($this->smtp_host,
                                        $this->smtp_port,
                                        $errno,
                                        $errstr,
                                        $this->smtp_timeout);

        if( ! is_resource($this->_smtp_connect))
        {                               
            return false;
        }

        return $this->_sendCommand('hello');
    }

    /**
     * Send SMTP command
     *
     * @param   string
     * @param   string
     * @return  string
     */ 
    private function _sendCommand($cmd, $data = '')
    {
        switch ($cmd) {
            case 'hello':
                if ($this->_smtp_auth || $this->_getEncoding() == '8bit')
                    $this->_sendData('EHLO '.$this->_getHostname());
                else
                    $this->_sendData('HELO '.$this->_getHostname());
                $resp = 250;
                break;
            case 'from':
                $this->_sendData('MAIL FROM:<'.$data.'>');
                $resp = 250;
                break;
            case 'to':
                $this->_sendData('RCPT TO:<'.$data.'>');
                $resp = 250;            
                break;
            case 'data' :
                $this->_sendData('DATA');
                $resp = 354;            
                break;
            case 'quit' :
                $this->_sendData('QUIT');
                $resp = 221;
                break;
        }
        
        $reply = $this->_getSmtpData();   
        
        $this->_debug_msg[] = "<pre>".$cmd.": ".$reply."</pre>";

        if (substr($reply, 0, 3) != $resp) {
            return false;
        }
            
        if ($cmd == 'quit')
            fclose($this->_smtp_connect);
    
        return true;
    }

    /**
     *  SMTP Authenticate
     *
     * @return  bool
     */ 
    private function _smtpAuthenticate()
    {   
        if ( ! $this->_smtp_auth)
            return true;
            
        if ($this->smtp_user == ""  &&  $this->smtp_pass == "") {
            return false;
        }

        $this->_sendData('AUTH LOGIN');

        $reply = $this->_getSmtpData();           

        if (substr($reply, 0, 3) != '334') {
            return false;
        }

        $this->_sendData(base64_encode($this->smtp_user));

        $reply = $this->_getSmtpData();           

        if (substr($reply, 0, 3) != '334') {
            return false;
        }

        $this->_sendData(base64_encode($this->smtp_pass));

        $reply = $this->_getSmtpData();           

        if (substr($reply, 0, 3) != '235') {
            return false;
        }
    
        return true;
    }

    /**
     * Send SMTP data
     *
     * @return  bool
     */ 
    private function _sendData($data)
    {
        if ( ! fwrite($this->_smtp_connect, $data . $this->newline)) {
            return false;
        } else
            return true;
    }

    /**
     * Get SMTP data
     *
     * @return  string
     */ 
    private function _getSmtpData()
    {
        $data = "";

        while ($str = fgets($this->_smtp_connect, 512)) {
            $data .= $str;
            
            if (substr($str, 3, 1) == " ")
                break;  
        }
        
        return $data;
    }

    /**
     * Get Hostname
     *
     * @return  string
     */     
    private function _getHostname()
    {   
        return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';    
    }

    /**
     * Get IP
     *
     * @return  string
     */     
    private function _getIp()
    {
        if ($this->_IP !== false) {
            return $this->_IP;
        }
    
        $cip = (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : false;
        $rip = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : false;
        $fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;
                    
        if ($cip && $rip)   $this->_IP = $cip;  
        elseif ($rip)       $this->_IP = $rip;
        elseif ($cip)       $this->_IP = $cip;
        elseif ($fip)       $this->_IP = $fip;
        
        if (strstr($this->_IP, ',')) {
            $x = explode(',', $this->_IP);
            $this->_IP = end($x);
        }
        
        if ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $this->_IP))
            $this->_IP = '0.0.0.0';
        
        unset($cip);
        unset($rip);
        unset($fip);
        
        return $this->_IP;
    }

    /**
     * Get Debug Message
     *
     * @return  string
     */ 
    function printDebugger()
    {       
        $msg = '';
        
        if (count($this->_debug_msg) > 0) {
            foreach ($this->_debug_msg as $val) {
                $msg .= $val;
            }
        }
        
        $msg .= "<pre>".$this->_header_str."\n".$this->_subject."\n".$this->_finalbody.'</pre>';    
        return $msg;
    }   

    /**
     * Mime Types
     *
     * @param   string
     * @return  string
     */     
    private function _mimeTypes($ext = "")
    {
        $mimes = array( 'hqx'   =>  'application/mac-binhex40',
                        'cpt'   =>  'application/mac-compactpro',
                        'doc'   =>  'application/msword',
                        'bin'   =>  'application/macbinary',
                        'dms'   =>  'application/octet-stream',
                        'lha'   =>  'application/octet-stream',
                        'lzh'   =>  'application/octet-stream',
                        'exe'   =>  'application/octet-stream',
                        'class' =>  'application/octet-stream',
                        'psd'   =>  'application/octet-stream',
                        'so'    =>  'application/octet-stream',
                        'sea'   =>  'application/octet-stream',
                        'dll'   =>  'application/octet-stream',
                        'oda'   =>  'application/oda',
                        'pdf'   =>  'application/pdf',
                        'ai'    =>  'application/postscript',
                        'eps'   =>  'application/postscript',
                        'ps'    =>  'application/postscript',
                        'smi'   =>  'application/smil',
                        'smil'  =>  'application/smil',
                        'mif'   =>  'application/vnd.mif',
                        'xls'   =>  'application/vnd.ms-excel',
                        'ppt'   =>  'application/vnd.ms-powerpoint',
                        'wbxml' =>  'application/vnd.wap.wbxml',
                        'wmlc'  =>  'application/vnd.wap.wmlc',
                        'dcr'   =>  'application/x-director',
                        'dir'   =>  'application/x-director',
                        'dxr'   =>  'application/x-director',
                        'dvi'   =>  'application/x-dvi',
                        'gtar'  =>  'application/x-gtar',
                        'php'   =>  'application/x-httpd-php',
                        'php4'  =>  'application/x-httpd-php',
                        'php3'  =>  'application/x-httpd-php',
                        'phtml' =>  'application/x-httpd-php',
                        'phps'  =>  'application/x-httpd-php-source',
                        'js'    =>  'application/x-javascript',
                        'swf'   =>  'application/x-shockwave-flash',
                        'sit'   =>  'application/x-stuffit',
                        'tar'   =>  'application/x-tar',
                        'tgz'   =>  'application/x-tar',
                        'xhtml' =>  'application/xhtml+xml',
                        'xht'   =>  'application/xhtml+xml',
                        'zip'   =>  'application/zip',
                        'mid'   =>  'audio/midi',
                        'midi'  =>  'audio/midi',
                        'mpga'  =>  'audio/mpeg',
                        'mp2'   =>  'audio/mpeg',
                        'mp3'   =>  'audio/mpeg',
                        'aif'   =>  'audio/x-aiff',
                        'aiff'  =>  'audio/x-aiff',
                        'aifc'  =>  'audio/x-aiff',
                        'ram'   =>  'audio/x-pn-realaudio',
                        'rm'    =>  'audio/x-pn-realaudio',
                        'rpm'   =>  'audio/x-pn-realaudio-plugin',
                        'ra'    =>  'audio/x-realaudio',
                        'rv'    =>  'video/vnd.rn-realvideo',
                        'wav'   =>  'audio/x-wav',
                        'bmp'   =>  'image/bmp',
                        'gif'   =>  'image/gif',
                        'jpeg'  =>  'image/jpeg',
                        'jpg'   =>  'image/jpeg',
                        'jpe'   =>  'image/jpeg',
                        'png'   =>  'image/png',
                        'tiff'  =>  'image/tiff',
                        'tif'   =>  'image/tiff',
                        'css'   =>  'text/css',
                        'html'  =>  'text/html',
                        'htm'   =>  'text/html',
                        'shtml' =>  'text/html',
                        'txt'   =>  'text/plain',
                        'text'  =>  'text/plain',
                        'log'   =>  'text/plain',
                        'rtx'   =>  'text/richtext',
                        'rtf'   =>  'text/rtf',
                        'xml'   =>  'text/xml',
                        'xsl'   =>  'text/xml',
                        'mpeg'  =>  'video/mpeg',
                        'mpg'   =>  'video/mpeg',
                        'mpe'   =>  'video/mpeg',
                        'qt'    =>  'video/quicktime',
                        'mov'   =>  'video/quicktime',
                        'avi'   =>  'video/x-msvideo',
                        'movie' =>  'video/x-sgi-movie',
                        'doc'   =>  'application/msword',
                        'word'  =>  'application/msword',
                        'xl'    =>  'application/excel',
                        'eml'   =>  'message/rfc822'
                    );

        return ( ! isset($mimes[strtolower($ext)])) ? "application/x-unknown-content-type" : $mimes[strtolower($ext)];
    }

} // End Email class
