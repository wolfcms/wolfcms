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


// Table structure for table: layout -----------------------------------------

$PDO->exec("CREATE TABLE layout (
    id INTEGER NOT NULL PRIMARY KEY,
    name varchar(100) default NULL,
    content_type varchar(80) default NULL,
    content text,
    created_on datetime default NULL,
    updated_on datetime default NULL,
    created_by_id int(11) default NULL,
    updated_by_id int(11) default NULL,
    position mediumint(6) default NULL
)");
$PDO->exec("CREATE UNIQUE INDEX layout_name ON layout (name)");


// Table structure for table: page -------------------------------------------

$PDO->exec("CREATE TABLE page ( 
    id INTEGER NOT NULL PRIMARY KEY,
    title varchar(255) default NULL ,
    slug varchar(100) default NULL , 
    breadcrumb varchar(160) default NULL ,
    keywords varchar(255) default NULL ,
    description text , 
    parent_id int(11) default NULL , 
    layout_id int(11) default NULL , 
    behavior_id varchar(25) NOT NULL , 
    status_id int(11) NOT NULL default '100' , 
    created_on datetime default NULL , 
    published_on datetime default NULL , 
    updated_on datetime default NULL , 
    created_by_id int(11) default NULL , 
    updated_by_id int(11) default NULL , 
    position mediumint(6) default NULL , 
    is_protected tinyint(1) NOT NULL default '0' ,
    needs_login tinyint(1) NOT NULL default '2'
)");


// Table structure for table: page_part --------------------------------------

$PDO->exec("CREATE TABLE page_part ( 
    id INTEGER NOT NULL PRIMARY KEY, 
    name varchar(100) default NULL , 
    filter_id varchar(25) default NULL , 
    content longtext , 
    content_html longtext , 
    page_id int(11) default NULL
)");


// Table structure for table: page_tag ---------------------------------------

$PDO->exec("CREATE TABLE page_tag ( 
    page_id int(11) NOT NULL , 
    tag_id int(11) NOT NULL
)");
$PDO->exec("CREATE UNIQUE INDEX page_tag_page_id ON page_tag (page_id,tag_id)");


// Table structure for table: permission -------------------------------------

$PDO->exec("CREATE TABLE permission ( 
    id INTEGER NOT NULL PRIMARY KEY, 
    name varchar(25) NOT NULL 
)");
$PDO->exec("CREATE UNIQUE INDEX permission_name ON permission (name)");


// Table structure for table: setting ----------------------------------------

$PDO->exec("CREATE TABLE setting (
    name varchar(40) NOT NULL ,
    value text NOT NULL
)");
$PDO->exec("CREATE UNIQUE INDEX setting_id ON setting (name)");


// Table structure for table: plugin_settings ----------------------------------------

$PDO->exec("CREATE TABLE plugin_settings (
    plugin_id varchar(40) NOT NULL ,
    name varchar(40) NOT NULL ,
    value varchar(255) NOT NULL
)");
$PDO->exec("CREATE UNIQUE INDEX plugin_setting_id ON plugin_settings (plugin_id,name)");


// Table structure for table: snippet ----------------------------------------

$PDO->exec("CREATE TABLE snippet ( 
    id INTEGER NOT NULL PRIMARY KEY,
    name varchar(100) NOT NULL default '' , 
    filter_id varchar(25) default NULL , 
    content text , 
    content_html text , 
    created_on datetime default NULL , 
    updated_on datetime default NULL , 
    created_by_id int(11) default NULL , 
    updated_by_id int(11) default NULL,
    position mediumint(6) default NULL
)");
$PDO->exec("CREATE UNIQUE INDEX snippet_name ON snippet (name)");


// Table structure for table: tag --------------------------------------------

$PDO->exec("CREATE TABLE tag (
    id INTEGER NOT NULL PRIMARY KEY,
    name varchar(40) NOT NULL ,
    count int(11) NOT NULL
)");
$PDO->exec("CREATE UNIQUE INDEX tag_name ON tag (name)");


// Table structure for table: user -------------------------------------------

$PDO->exec("CREATE TABLE user (
    id INTEGER NOT NULL PRIMARY KEY,
    name varchar(100) default NULL ,
    email varchar(255) default NULL ,
    username varchar(40) NOT NULL ,
    password varchar(40) default NULL ,
    created_on datetime default NULL ,
    updated_on datetime default NULL ,
    created_by_id int(11) default NULL ,
    updated_by_id int(11) default NULL
)");
$PDO->exec("CREATE UNIQUE INDEX user_username ON user (username)");


// Table structure for table: user_permission --------------------------------

$PDO->exec("CREATE TABLE user_permission (
    user_id int(11) NOT NULL ,
    permission_id int(11) NOT NULL
)");
$PDO->exec("CREATE UNIQUE INDEX user_permission_user_id ON user_permission (user_id,permission_id)");
