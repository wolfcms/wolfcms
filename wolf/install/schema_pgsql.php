<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Installer
 * @subpackage Database
 * 
 * @author Age Bosma <agebosma@gmail.com>
 * @since Wolf version 0.6.0
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}


// Table structure for table: secure_token -----------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."secure_token (
  id serial,
  username character varying(40) DEFAULT NULL,
  url character varying(255) DEFAULT NULL,
  time character varying(100) DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT username_url UNIQUE (username, url)
)");


// Table structure for table: cron -----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."cron (
  id serial,
  lastrun text,
  PRIMARY KEY (id)
)");


// Table structure for table: layout -----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."layout (
  id serial,
  name character varying(100) DEFAULT NULL,
  content_type character varying(80) DEFAULT NULL,
  content text,
  created_on timestamp DEFAULT NULL,
  updated_on timestamp DEFAULT NULL,
  created_by_id integer DEFAULT NULL,
  updated_by_id integer DEFAULT NULL,
  position integer DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT layoutname UNIQUE (name)
)");


// Table structure for table: page -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page (
  id serial,
  title character varying(255) DEFAULT NULL,
  slug character varying(100) NOT NULL DEFAULT '',
  breadcrumb character varying(160) DEFAULT NULL,
  keywords character varying(255) DEFAULT NULL,
  description text,
  parent_id integer DEFAULT NULL,
  layout_id integer DEFAULT NULL,
  behavior_id character varying(25) NOT NULL DEFAULT '',
  status_id integer NOT NULL DEFAULT '100',
  created_on timestamp DEFAULT NULL,
  published_on timestamp DEFAULT NULL,
  valid_until timestamp DEFAULT NULL,
  updated_on timestamp DEFAULT NULL,
  created_by_id integer DEFAULT NULL,
  updated_by_id integer DEFAULT NULL,
  position integer DEFAULT '0',
  is_protected smallint NOT NULL DEFAULT '0',
  needs_login smallint NOT NULL DEFAULT '2',
  PRIMARY KEY (id)
)");


// Table structure for table: page_part --------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page_part (
  id serial,
  name character varying(100) DEFAULT NULL,
  filter_id character varying(25) DEFAULT NULL,
  content text,
  content_html text,
  page_id integer DEFAULT NULL,
  PRIMARY KEY (id)
)");


// Table structure for table: page_tag ---------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."page_tag (
  page_id integer NOT NULL,
  tag_id integer NOT NULL,
  CONSTRAINT page_id UNIQUE (page_id, tag_id)
)");


// Table structure for table: permission -------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."permission (
  id serial,
  name character varying(25) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT permissionname UNIQUE (name)
)");


// Table structure for table: role -------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."role (
  id serial,
  name character varying(25) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT rolename UNIQUE (name)
)");


// Table structure for table: setting ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."setting (
  name character varying(40) NOT NULL,
  value text NOT NULL,
  CONSTRAINT id UNIQUE (name)
)");


// Table structure for table: plugin_settings ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."plugin_settings (
  plugin_id character varying(40) NOT NULL,
  name character varying(40) NOT NULL,
  value character varying(255) NOT NULL,
  CONSTRAINT plugin_setting_id UNIQUE (plugin_id, name)
)");


// Table structure for table: snippet ----------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."snippet (
  id serial,
  name character varying(100) NOT NULL DEFAULT '',
  filter_id character varying(25) DEFAULT NULL,
  content text,
  content_html text,
  created_on timestamp DEFAULT NULL,
  updated_on timestamp DEFAULT NULL,
  created_by_id integer DEFAULT NULL,
  updated_by_id integer DEFAULT NULL,
  position integer DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT snippetname UNIQUE (name)
)");


// Table structure for table: tag --------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."tag (
  id serial,
  name character varying(40) NOT NULL,
  count integer NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT tagname UNIQUE (name)
)");


// Table structure for table: user -------------------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."user (
  id serial,
  name character varying(100) DEFAULT NULL,
  email character varying(255) DEFAULT NULL,
  username character varying(40) NOT NULL,
  password character varying(1024) DEFAULT NULL,
  salt character varying(1024) DEFAULT NULL,
  language character varying(40) DEFAULT NULL,
  last_login timestamp DEFAULT NULL,
  last_failure timestamp DEFAULT NULL,
  failure_count integer DEFAULT NULL,
  created_on timestamp DEFAULT NULL,
  updated_on timestamp DEFAULT NULL,
  created_by_id integer DEFAULT NULL,
  updated_by_id integer DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT username UNIQUE (username),
  CONSTRAINT uc_email UNIQUE (email)
)");


// Table structure for table: user_role --------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."user_role (
  user_id integer NOT NULL,
  role_id integer NOT NULL,
  CONSTRAINT user_id UNIQUE (user_id, role_id)
)");


// Table structure for table: role_permission --------------------------------

$PDO->exec("CREATE TABLE ".TABLE_PREFIX."role_permission (
  role_id integer NOT NULL,
  permission_id integer NOT NULL,
  CONSTRAINT role_id UNIQUE (role_id, permission_id)
)");


// Simulate MySQL DATE_FORMAT()
// From: MySQL Compatibility Functions (http://pgfoundry.org/projects/mysqlcompat/)
// Author: Chris Kings-Lynne
$PDO->exec("CREATE LANGUAGE plpgsql");
$PDO->exec("
    -- DATE_FORMAT()
    -- Note: Doesn't handle weeks of years yet
    CREATE OR REPLACE FUNCTION date_format(timestamp without time zone, text)
    RETURNS text AS $$
      DECLARE
        i int := 1;
        temp text := '';
        c text;
        n text;
        res text;
      BEGIN
        WHILE i <= pg_catalog.length($2) LOOP
          -- Look at current character
          c := SUBSTRING ($2 FROM i FOR 1);
          -- If it's a '%' and not the last character then process it as a placeholder
          IF c = '%' AND i != pg_catalog.length($2) THEN
            n := SUBSTRING ($2 FROM (i + 1) FOR 1);
            SELECT INTO res CASE
              WHEN n = 'a' THEN pg_catalog.to_char($1, 'Dy')
              WHEN n = 'b' THEN pg_catalog.to_char($1, 'Mon')
              WHEN n = 'c' THEN pg_catalog.to_char($1, 'FMMM')
              WHEN n = 'D' THEN pg_catalog.to_char($1, 'FMDDth')
              WHEN n = 'd' THEN pg_catalog.to_char($1, 'DD')
              WHEN n = 'e' THEN pg_catalog.to_char($1, 'FMDD')
              WHEN n = 'f' THEN pg_catalog.to_char($1, 'US')
              WHEN n = 'H' THEN pg_catalog.to_char($1, 'HH24')
              WHEN n = 'h' THEN pg_catalog.to_char($1, 'HH12')
              WHEN n = 'I' THEN pg_catalog.to_char($1, 'HH12')
              WHEN n = 'i' THEN pg_catalog.to_char($1, 'MI')
              WHEN n = 'j' THEN pg_catalog.to_char($1, 'DDD')
              WHEN n = 'k' THEN pg_catalog.to_char($1, 'FMHH24')
              WHEN n = 'l' THEN pg_catalog.to_char($1, 'FMHH12')
              WHEN n = 'M' THEN pg_catalog.to_char($1, 'FMMonth')
              WHEN n = 'm' THEN pg_catalog.to_char($1, 'MM')
              WHEN n = 'p' THEN pg_catalog.to_char($1, 'AM')
              WHEN n = 'r' THEN pg_catalog.to_char($1, 'HH12:MI:SS AM')
              WHEN n = 'S' THEN pg_catalog.to_char($1, 'SS')
              WHEN n = 's' THEN pg_catalog.to_char($1, 'SS')
              WHEN n = 'T' THEN pg_catalog.to_char($1, 'HH24:MI:SS')
              WHEN n = 'U' THEN pg_catalog.to_char($1, '?')
              WHEN n = 'u' THEN pg_catalog.to_char($1, '?')
              WHEN n = 'V' THEN pg_catalog.to_char($1, '?')
              WHEN n = 'v' THEN pg_catalog.to_char($1, '?')
              WHEN n = 'W' THEN pg_catalog.to_char($1, 'FMDay')
              WHEN n = 'w' THEN EXTRACT(DOW FROM $1)::text
              WHEN n = 'X' THEN pg_catalog.to_char($1, '?')
              WHEN n = 'x' THEN pg_catalog.to_char($1, '?')
              WHEN n = 'Y' THEN pg_catalog.to_char($1, 'YYYY')
              WHEN n = 'y' THEN pg_catalog.to_char($1, 'YY')
              WHEN n = '%' THEN pg_catalog.to_char($1, '%')
              ELSE NULL
            END;
            temp := temp operator(pg_catalog.||) res;
            i := i + 2;
          ELSE
            -- Otherwise just append the character to the string
            temp = temp operator(pg_catalog.||) c;
            i := i + 1;
          END IF;
        END LOOP;
        RETURN temp;
      END
    $$ IMMUTABLE STRICT LANGUAGE PLPGSQL;
");
