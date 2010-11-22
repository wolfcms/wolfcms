# WOLF CMS - INFORMATION

## About Wolf CMS

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
in the docs/ subdirectory.

IMPORTANT - always check your security by viewing security.php post update!

## Required

- An HTTP server
- PHP 5
    - PHP: magic_quotes_gpc should be turned OFF.
    - PHP: Wolf CMS does *not* run on PHP 4.
- PDO support.
- MySQL 4.1.x or above with InnoDB support. -OR-
- SQLite 3 -OR-
- PostgreSQL (tested against 8.4.5)

## Recommended

- The Apache HTTP server is recommended.
- Wolf CMS is known to run on these HTTP servers as well:
    - Lighttpd
    - Hiawatha

PHP        : http://www.php.net/
MySQL      : http://www.mysql.com/
SQLite     : http://www.sqlite.org/
PostgreSQL : http://www.postgresql.org/
Apache     : http://www.apache.org/
Lighttpd   : http://www.lighttpd.net/
Hiawatha   : http://www.hiawatha-webserver.org/
Nginx      : http://nginx.org/en/

## Notes

Password is in sha512 + salt so you know how to change it manually in the database!

Enjoy,

The Wolf CMS team.