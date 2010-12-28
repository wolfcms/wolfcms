<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
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
 * Simple Pagination helper based on the CodeIgniter pagination helper.
 *
 * @package Helpers
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author CodeIgniter helper
 * @version 0.1
 * @since Wolf version 0.5.5
 */

/**
 *
 */
class Pagination {

    var $base_url           = ''; // The page we are linking to
    var $total_rows         = ''; // Total number of items (database results)
    var $per_page           = 10; // Max number of items you want shown per page
    var $num_links          =  3; // Number of "digit" links to show before/after the currently viewed page
    var $cur_page           =  1; // The current page being viewed
    var $uri_param          =  0;
    var $first_link         = '&lsaquo; First';
    var $next_link          = '&gt;';
    var $prev_link          = '&lt;';
    var $last_link          = 'Last &rsaquo;';
    var $full_tag_open      = '';
    var $full_tag_close     = '';
    var $first_tag_open     = '';
    var $first_tag_close    = '&nbsp;';
    var $last_tag_open      = '&nbsp;';
    var $last_tag_close     = '';
    var $cur_tag_open       = '&nbsp;<b>';
    var $cur_tag_close      = '</b>';
    var $next_tag_open      = '&nbsp;';
    var $next_tag_close     = '&nbsp;';
    var $prev_tag_open      = '&nbsp;';
    var $prev_tag_close     = '';
    var $num_tag_open       = '&nbsp;';
    var $num_tag_close      = '';

    /**
     * Constructor
     *
     * @param array initialization parameters
     */
    function __construct($params = array()) {
        if (count($params) > 0) {
            $this->initialize($params);
        }
    }

    /**
     * Initialize Preferences
     *
     * @param	array	initialization parameters
     * @return	void
     */
    function initialize($params = array()) {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    } // initialize

    /**
     * Generate the pagination links
     *
     * @return	string
     */
    function createLinks() {
    // If our item count or per-page total is zero there is no need to continue.
        if ($this->total_rows == 0 || $this->per_page == 0) {
            return '';
        }

        // Calculate the total number of pages
        $num_pages = ceil($this->total_rows / $this->per_page);

        // Is there only one page? Hm... nothing more to do here then.
        if ($num_pages == 1) {
            return '';
        }

        // Calculate the start and end numbers. These determine
        // which number to start and end the digit links with
        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
        $end = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

        // Add a trailing slash to the base URL if needed
//        $this->base_url = preg_replace("/(.+?)\/*$/", "\\1/",  $this->base_url);

        // And here we go...
        $output = '';

        // Render the "First" link
        if ($this->cur_page > $this->num_links) {
            $output .= $this->first_tag_open.'<a href="'.$this->base_url.'">'.$this->first_link.'</a>'.$this->first_tag_close;
        }

        // Write the digit links
        for ($page = $start -1; $page <= $end; $page++) {
            if ($page > 0) {
                if ($this->cur_page == $page) {
                    $output .= $this->cur_tag_open.$page.$this->cur_tag_close; // Current page
                } else {
                //$page_offset = ($page-1)*$this->per_page;
                    $output .= $this->num_tag_open.'<a href="'.$this->base_url.$page.'">'.$page.'</a>'.$this->num_tag_close;
                } // if
            } // if
        } // for

        // Render the "Last" link
        if (($this->cur_page + $this->num_links) < $num_pages) {
        //$page = (($num_pages * $this->per_page) - $this->per_page);
            $output .= $this->last_tag_open.'<a href="'.$this->base_url.$num_pages.'">'.$this->last_link.'</a>'.$this->last_tag_close;
        }

        // Kill double slashes.  Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double shashes.
        $output = preg_replace("#([^:])//+#", "\\1/", $output);

        // Add the wrapper HTML if exists
        $output = $this->full_tag_open.$output.$this->full_tag_close;

        return $output;
    } // createLinks

  } // Pagination
