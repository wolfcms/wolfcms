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

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."layout (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(100) default NULL,
  content_type varchar(80) default NULL,
  content text,
  created_on datetime default NULL,
  updated_on datetime default NULL,
  created_by_id int(11) default NULL,
  updated_by_id int(11) default NULL,
  position mediumint(6) unsigned default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: page -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page (
  id int(11) unsigned NOT NULL auto_increment,
  title varchar(255) default NULL,
  slug varchar(100) default NULL,
  breadcrumb varchar(160) default NULL,
  keywords varchar(255) default NULL,
  description text,
  parent_id int(11) unsigned default NULL,
  layout_id int(11) unsigned default NULL,
  behavior_id varchar(25) NOT NULL,
  status_id int(11) unsigned NOT NULL default '100',
  created_on datetime default NULL,
  published_on datetime default NULL,
  updated_on datetime default NULL,
  created_by_id int(11) default NULL,
  updated_by_id int(11) default NULL,
  position mediumint(6) unsigned default NULL,
  is_protected tinyint(1) NOT NULL default '0',
  needs_login tinyint(1) NOT NULL default '2',
  PRIMARY KEY  (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: page_part --------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page_part (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(100) default NULL,
  filter_id varchar(25) default NULL,
  content longtext,
  content_html longtext,
  page_id int(11) unsigned default NULL,
  PRIMARY KEY  (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: page_tag ---------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page_tag (
  page_id int(11) unsigned NOT NULL,
  tag_id int(11) unsigned NOT NULL,
  UNIQUE KEY page_id (page_id,tag_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: permission -------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."permission (
  id int(11) NOT NULL auto_increment,
  name varchar(25) NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: setting ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."setting (
  name varchar(40) NOT NULL,
  value text NOT NULL,
  UNIQUE KEY id (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: plugin_settings ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."plugin_settings (
  plugin_id varchar(40) NOT NULL,
  name varchar(40) NOT NULL,
  value varchar(255) NOT NULL,
  UNIQUE KEY plugin_setting_id (plugin_id,name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: snippet ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."snippet (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  filter_id varchar(25) default NULL,
  content text,
  content_html text,
  created_on datetime default NULL,
  updated_on datetime default NULL,
  created_by_id int(11) default NULL,
  updated_by_id int(11) default NULL,
  position mediumint(6) unsigned default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: tag --------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."tag (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(40) NOT NULL,
  count int(11) unsigned NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: user -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."user (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(100) default NULL,
  email varchar(255) default NULL,
  username varchar(40) NOT NULL,
  password varchar(40) default NULL,
  created_on datetime default NULL,
  updated_on datetime default NULL,
  created_by_id int(11) default NULL,
  updated_by_id int(11) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY username (username)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: user_permission --------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."user_permission (
  user_id int(11) NOT NULL,
  permission_id int(11) NOT NULL,
  UNIQUE KEY user_id (user_id,permission_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");
