<?php

/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
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
 * Provides Page not found page types.
 *
 * @package wolf
 * @subpackage plugin.page_not_found
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 1.0
 * @since Wolf version 0.9.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

Plugin::setInfos(array(
    'id'          => 'page_not_found',
    'title'       => __('Page not found'),
    'description' => __('Provides Page not found page types.'),
    'version'     => '1.0.0', 
    'website'     => 'http://www.wolfcms.org/',
    'update_url'  => 'http://www.wolfcms.org/plugin-versions.xml'
));

Behavior::add('page_not_found', '');
Observer::observe('page_not_found', 'behavior_page_not_found');

/**
 *
 * @global <type> $__CMS_CONN__ 
 */
function behavior_page_not_found()
{
    global $__CMS_CONN__;
    
    $sql = 'SELECT * FROM '.TABLE_PREFIX."page WHERE behavior_id='page_not_found'";
    $stmt = $__CMS_CONN__->prepare($sql);
    $stmt->execute();
    
    if ($page = $stmt->fetchObject())
    {
        $page = Page::find_page_by_uri($page->slug);
        
        // if we fund it, display it!
        if (is_object($page))
        {
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
              
            $page->_executeLayout();
            exit(); // need to exit here otherwise the true error page will be sent
        }
    }
}