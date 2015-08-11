# WOLF CMS

## About Wolf CMS

[![Current Version](http://img.shields.io/badge/wolfcms-v0.8.3.1-blue.svg)](http://www.wolfcms.org/download.html)
[![Build Status](http://img.shields.io/travis/wolfcms/wolfcms/master.svg)](http://travis-ci.org/wolfcms/wolfcms)
[![Coverage Status](http://img.shields.io/coveralls/wolfcms/wolfcms/master.svg)](https://coveralls.io/r/wolfcms/wolfcms?branch=master)
[![Gittip Status](http://img.shields.io/gittip/Wolf%20CMS.svg)](https://www.gittip.com/Wolf%20CMS/)

Wolf CMS simplifies content management by offering an elegant user interface,
flexible templating per page, simple user management and permissions, as well
as the tools necessary for file management.

This product has been made available under the terms of the GNU GPL version 3.
Please read the docs/license.txt and docs/exception.txt files for the exact
license details that apply to Wolf CMS.

The official Wolf CMS website can be found at www.wolfcms.org - visit it for
more information and resources.

## Installation & Documentation

You can find all documentation including installation and upgrade instructions
in the docs/ subdirectory. Please read the documentation before asking for help.

IMPORTANT - always check if there is any security advise by viewing security.php
            after installing or updating Wolf CMS!

## Required

- An HTTP server
- PHP 5.3+
    - PHP: magic_quotes_gpc should be turned OFF.
    - PHP: Wolf CMS does *not* run on PHP 4.
- PDO support.
- A database supported by PDO.
    - MySQL 4.1+ with InnoDB support.
    - SQLite 3
    - PostgreSQL (tested against 8.4.5)

## Recommended

- The system is tested on Apache HTTP server.
- Wolf CMS is known to run on these HTTP servers as well:
    - Cherokee
    - Lighttpd
    - Nginx
    - Hiawatha

## More information

PHP        : http://www.php.net/  
MySQL      : http://www.mysql.com/  
SQLite     : http://www.sqlite.org/  
PostgreSQL : http://www.postgresql.org/  
Apache     : http://www.apache.org/  
Cherokee   : http://www.cherokee-project.com/  
Hiawatha   : http://www.hiawatha-webserver.org/  
Lighttpd   : http://www.lighttpd.net/  
Nginx      : http://nginx.org/en/  

If you're running Wolf CMS on a different HTTP server successfully,
please let us know at http://forum.wolfcms.org/

## Notes

Password is in sha512 + salt so you know how to change it manually in the database!

Enjoy,

The Wolf CMS team.
