<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Installer
 * @subpackage Database
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

// Make sure we clean out any previous installs. We're supposed to be doing a
// clean install after all.
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'secure_token');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'cron');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'layout');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'page');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'page_part');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'snippet');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'page_tag');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'role');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'permission');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'setting');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'plugin_settings');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'tag');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'user');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'user_role');
$PDO->exec('DROP TABLE IF EXISTS '.TABLE_PREFIX.'role_permission');

// Table structure for table: secure_token -----------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."secure_token (
  id int(11) unsigned NOT NULL auto_increment,
  username varchar(40) default NULL,
  url varchar(255) default NULL,
  time varchar(100) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY uniqueUsernameUrl (username,url)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: cron -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."cron (
  id int(11) unsigned NOT NULL auto_increment,
  lastrun text,
  PRIMARY KEY  (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


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
  UNIQUE KEY uniqueLayoutName (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: page -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page (
  id int(11) unsigned NOT NULL auto_increment,
  title varchar(255) default NULL,
  slug varchar(100) NOT NULL default '',
  breadcrumb varchar(160) default NULL,
  keywords varchar(255) default NULL,
  description text,
  parent_id int(11) unsigned default NULL,
  layout_id int(11) unsigned default NULL,
  behavior_id varchar(25) NOT NULL default '',
  status_id int(11) unsigned NOT NULL default '100',
  created_on datetime default NULL,
  published_on datetime default NULL,
  valid_until datetime default NULL,
  updated_on datetime default NULL,
  created_by_id int(11) default NULL,
  updated_by_id int(11) default NULL,
  position mediumint(6) unsigned default '0',
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
  UNIQUE KEY uniquePageIdTagId (page_id,tag_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: permission -------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."permission (
  id int(11) NOT NULL auto_increment,
  name varchar(25) NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY uniquePermissionName (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: role -------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."role (
  id int(11) NOT NULL auto_increment,
  name varchar(25) NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY uniqueRoleName (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: setting ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."setting (
  name varchar(40) NOT NULL,
  value text NOT NULL,
  UNIQUE KEY uniqueSettingName (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: plugin_settings --------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."plugin_settings (
  plugin_id varchar(40) NOT NULL,
  name varchar(40) NOT NULL,
  value varchar(255) NOT NULL,
  UNIQUE KEY uniquePluginIdSettingName (plugin_id,name)
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
  UNIQUE KEY uniqueSnippetName (name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: tag --------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."tag (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(40) NOT NULL,
  count int(11) unsigned NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY uniqueTagName (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: user -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."user (
  id int(11) unsigned NOT NULL auto_increment,
  name varchar(100) default NULL,
  email varchar(255) default NULL,
  username varchar(40) NOT NULL,
  password varchar(1024) default NULL,
  salt varchar(1024) default NULL,
  language varchar(5) default NULL,
  last_login datetime default NULL,
  last_failure datetime default NULL,
  failure_count int(11) default NULL,
  created_on datetime default NULL,
  updated_on datetime default NULL,
  created_by_id int(11) default NULL,
  updated_by_id int(11) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY uniqueUsername (username),
  CONSTRAINT uniqueUserEmail UNIQUE (email)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");


// Table structure for table: user_role --------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."user_role (
  user_id int(11) NOT NULL,
  role_id int(11) NOT NULL,
  UNIQUE KEY uniqueUserIdRoleId (user_id,role_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


// Table structure for table: role_permission --------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."role_permission (
  role_id int(11) NOT NULL,
  permission_id int(11) NOT NULL,
  UNIQUE KEY uniqueRoleIdPermissionId (role_id,permission_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");
