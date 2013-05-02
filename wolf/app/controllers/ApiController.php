<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2013 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * @package Controllers
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2013
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/*
 * steps for controller
 * 
 * check for authentication status
 * if authenticated, allow API call
 * if not authenticated, start OAUTH
 * 
 * allow for registration of new api methods
 * allow for de-registration of api methods
 * make sure that availability is check before calling custom api method
 * 
 * provide these api calls by the core
 * 
 * userExists?
 * userAuthenticated?
 * authenticateUser?
 * getSnippet
 * getPage
 * getPart
 */
class ApiController extends Controller {

    function __construct() {
        
    }

    /**
     * 
     * @param string $call
     * @return json object
     */
    private static final function call(string $call) {        
        $response = array(
            'status'    => '500',
            'timestamp' => time(),
            'data'      => '',
        );
        
        $parsedCall = json_decode($call, true);
        
        if ($parsedCall === false || $parsedCall === null) {
            return false;
        }
        
        if (array_key_exists($parsedCall['method'], self::registeredMethods)) {
            switch($parsedCall['method']):
                case 'getSnippet':
                    $data = self::getSnippet($name);
                    
                    if ($data !== false) {
                        $response['status'] = '200';
                        $response['data']   = $data;
                    }
                    
                    break;
                case 'getPart':
                    $data = self::getPart($parsedCall['name']);
                    
                    if ($data !== false) {
                        $response['status'] = '200';
                        $response['data']   = $data;
                    }
                    
                    break;
                case 'getPage':
                    $data = self::getPage($parsedCall['uri']);
                    
                    if ($data !== false) {
                        $response['status'] = '200';
                        $response['data']   = $data;
                    }
                    
                    break;
                default:
                    return false;
            endswitch;
        }
        
        return json_encode($response);
    }
    
    private static final function getSnippet(string $name) {
        use_helper('Validate');
        
        // check if $name is valid string
        if (!Validate::slug($name)) {
            return false;
        }
        
        return Snippet::findByName($name);
    }
    
    private static final function getPart (string $name) {
        use_helper('Validate');
        
        // Check if $name is a valid string
        if (!Validate::slug($name)) {
            return false;
        }
        
        return PagePart::findOneFrom('PagePart', 'name=:name', array(':name' => $name));
    }
    
    private static final function getPage(string $uri) {
        use_helper('Validate');
        
        // Check if $url is a valid string
        if (!Validate::url($uri)) {
            return false;
        }
        
        return Page::findByUri($uri);
    }
}