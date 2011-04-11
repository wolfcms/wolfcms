<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * This upgrade file is for single 0.6.0 stable to 0.7.0 stable upgrades!
 * It does NOT take into account any custom changes that were made to the DB.
 *
 * ALWAY MAKE A BACKUP OF THE DB BEFORE UPGRADING!
 *
 * @version 0.7.0
 * @since   0.7.0
 * @author  Martijn van der Kleijn <martijn.niji@gmail.com>
 * 
 * @package Installer
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}
?>

<p>
    Upgrading:
</p>
<ul>
    
<?php
// Check passwords
$data = $_POST['upgrade'];
if ($data['pwd'] != $data['pwd_check']) {
    die('<strong>Upgrade failed!</strong> Passwords do not match each other.');
}

// SETUP BASIC WOLF ENVIRONMENT
try {
    $__CMS_CONN__ = new PDO(DB_DSN, DB_USER, DB_PASS);
}
catch (PDOException $error) {
    die('<strong>Upgrade failed!</strong> DB Connection failed: '.$error->getMessage());
}

echo '<li>Connection to current database made...</li>';

$driver = $__CMS_CONN__->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($driver === 'mysql') {
    $__CMS_CONN__->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
}

if ($driver === 'sqlite') {
    // Adding date_format function to SQLite 3 'mysql date_format function'
    if (! function_exists('mysql_date_format_function')) {
        function mysql_function_date_format($date, $format) {
            return strftime($format, strtotime($date));
        }
    }
    $__CMS_CONN__->sqliteCreateFunction('date_format', 'mysql_function_date_format', 2);
}

Record::connection($__CMS_CONN__);
Record::getConnection()->exec("set names 'utf8'");

// START PRE-UPGRADE STUFF

// Get the user from the DB
$user = Record::findOneFrom('User', 'username=?', array($data['username']));

if (!$user) {
    die('<strong>Upgrade failed!</strong> Administrative user not correct...');
}

echo '<li>Administrative user found.</li>';

// Get the user's permissions from the DB
$perms = array();
$sql = 'SELECT name FROM '.TABLE_PREFIX.'permission AS permission, '.TABLE_PREFIX.'user_permission'
     . ' WHERE permission_id = permission.id AND user_id='.$user->id;

$PDO = Record::getConnection();
$stmt = $PDO->prepare($sql);
$stmt->execute();

while ($perm = $stmt->fetchObject())
    $perms[] = $perm->name;

if (!in_array('administrator', $perms)) {
    die('<strong>Upgrade failed!</strong> Administrative permissions not correct.');
}

echo '<li>Administrative user has appropriate permissions...</li>';

// Check administrative user's password
if ($user->password != sha1($data['pwd'])) {
    die('<strong>Upgrade failed!</strong> Administrative password not correct.');
}

echo '<li>Administrative password correct...</li>';


/***** SAFETY CHECKS DONE, CONTINUE WITH ACTUAL UPGRADE ******/
echo '<li>Starting database upgrade...<ul>';

// MYSQL
if ($driver == 'mysql') {
    try {
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // ADDING NEW FIELDS
        $PDO->exec("ALTER TABLE ".TABLE_PREFIX."user
                    ADD COLUMN salt varchar(1024) default NULL,
                    ADD COLUMN last_login datetime default NULL,
                    ADD COLUMN last_failure datetime default NULL,
                    ADD COLUMN failure_count int(11) default NULL
                   ");
        echo '<li>Added fields to user table...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("ALTER TABLE ".TABLE_PREFIX."page
                    ADD COLUMN valid_until datetime default NULL
                   ");
        echo '<li>Added fields to page table...</li>';
        ob_flush(); flush();
        sleep(1);

        // CHANGING FIELDS
        $PDO->exec("ALTER TABLE ".TABLE_PREFIX."user
                    MODIFY COLUMN password varchar(1024) default NULL,
                    MODIFY COLUMN language varchar(5) default NULL
                   ");
        echo '<li>Modified fields for user table...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("ALTER TABLE ".TABLE_PREFIX."page
                    MODIFY COLUMN behavior_id varchar(25) NOT NULL default '',
                    MODIFY COLUMN position mediumint(6) unsigned default '0'
                   ");
        echo '<li>Modified fields for page table...</li>';
        ob_flush(); flush();
        sleep(1);

        // ADDING TABLES
        $PDO->exec("CREATE TABLE ".TABLE_PREFIX."secure_token (
                    id int(11) unsigned NOT NULL auto_increment,
                    username varchar(40) default NULL,
                    url varchar(255) default NULL,
                    time varchar(100) default NULL,
                    PRIMARY KEY  (id),
                    UNIQUE KEY username_url (username,url)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
               ");
        echo '<li>Added table: secure_token...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("CREATE TABLE ".TABLE_PREFIX."role (
                        id int(11) NOT NULL auto_increment,
                        name varchar(25) NOT NULL,
                        PRIMARY KEY  (id),
                        UNIQUE KEY name (name)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
                   ");
        echo '<li>Added table: role...</li>';
        ob_flush(); flush();
        sleep(1);
        
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role (id, name) VALUES (1, 'administrator')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role (id, name) VALUES (2, 'developer')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role (id, name) VALUES (3, 'editor')");
        echo '<li>Added default roles...</li>';
        ob_flush(); flush();
        sleep(1);


        $PDO->exec("CREATE TABLE ".TABLE_PREFIX."user_role (
                        user_id int(11) NOT NULL,
                        role_id int(11) NOT NULL,
                        UNIQUE KEY user_id (user_id,role_id)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8
                   ");
        echo '<li>Added table: user_role...</li>';
        ob_flush(); flush();
        sleep(1);

        // Migrate all administrator role mappings
        $sql = 'SELECT user_id FROM '.TABLE_PREFIX.'user_permission'
             . ' WHERE permission_id = 1';

        $stmt = $PDO->prepare($sql);
        $stmt->execute();

        while ($map = $stmt->fetchObject()) {
            $PDO->exec("INSERT INTO ".TABLE_PREFIX."user_role (user_id, role_id) VALUES (".$map->user_id.", 1)");
        }
        echo '<li>Migrated all administrator role mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        // Migrate all developer role mappings
        $sql = 'SELECT user_id FROM '.TABLE_PREFIX.'user_permission'
             . ' WHERE permission_id = 2';

        $stmt = $PDO->prepare($sql);
        $stmt->execute();
        ob_flush(); flush();
        sleep(1);

        while ($map = $stmt->fetchObject()) {
            $PDO->exec("INSERT INTO ".TABLE_PREFIX."user_role (user_id, role_id) VALUES (".$map->user_id.", 2)");
        }
        echo '<li>Migrated all developer role mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        // Migrate all editor role mappings
        $sql = 'SELECT user_id FROM '.TABLE_PREFIX.'user_permission'
             . ' WHERE permission_id = 3';

        $stmt = $PDO->prepare($sql);
        $stmt->execute();
        ob_flush(); flush();
        sleep(1);

        while ($map = $stmt->fetchObject()) {
            $PDO->exec("INSERT INTO ".TABLE_PREFIX."user_role (user_id, role_id) VALUES (".$map->user_id.", 3)");
        }
        echo '<li>Migrated all editor role mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("CREATE TABLE ".TABLE_PREFIX."role_permission (
                        role_id int(11) NOT NULL,
                        permission_id int(11) NOT NULL,
                        UNIQUE KEY user_id (role_id,permission_id)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8
                   ");
        echo '<li>Added table: role_permission...</li>';
        ob_flush(); flush();
        sleep(1);

        // Role 1 = administrator
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 1)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 2)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 3)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 4)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 5)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 6)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 7)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 8)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 9)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 10)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 11)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 12)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 13)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 14)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 15)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 16)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 17)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 18)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 19)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 20)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 21)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 22)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 23)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 24)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 25)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 26)");
        ob_flush(); flush();
        sleep(1);

        // Role 2 = developer
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 1)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 7)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 8)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 9)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 10)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 11)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 12)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 13)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 14)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 15)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 16)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 17)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 18)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 19)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 20)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 21)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 22)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 23)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 24)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 25)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 26)");
        ob_flush(); flush();
        sleep(1);

        // Role 2 = editor
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 1)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 15)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 16)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 17)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 18)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 19)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 20)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 21)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 22)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 23)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 24)");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 25)");
        echo '<li>Added default role permission mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        // Updating permissions table
        $PDO->exec("TRUNCATE TABLE ".TABLE_PREFIX."permission");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (1, 'admin_view')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (2, 'admin_edit')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (3, 'user_view')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (4, 'user_add')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (5, 'user_edit')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (6, 'user_delete')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (7, 'layout_view')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (8, 'layout_add')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (9, 'layout_edit')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (10, 'layout_delete')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (11, 'snippet_view')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (12, 'snippet_add')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (13, 'snippet_edit')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (14, 'snippet_delete')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (15, 'page_view')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (16, 'page_add')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (17, 'page_edit')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (18, 'page_delete')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (19, 'file_manager_view')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (20, 'file_manager_upload')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (21, 'file_manager_mkdir')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (22, 'file_manager_mkfile')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (23, 'file_manager_rename')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (24, 'file_manager_chmod')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (25, 'file_manager_delete')");
        $PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (26, 'backup_restore_view')");
        echo '<li>Emptied permission table and added default permissions...</li>';
        ob_flush(); flush();
        sleep(1);

        // DELETING TABLES
        $PDO->exec("DROP TABLE ".TABLE_PREFIX."user_permission");
        echo '<li>Removed table: user_permission...</li>';
    }
    catch (Exception $e) {
        echo 'Automated database upgrade failed.<br/>Error message was: ' . $e->getMessage();
    }    
}

// SQLITE
if ($driver == 'sqlite') {
    try {
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // ADDING NEW FIELDS
        $PDO->exec("ALTER TABLE user ADD COLUMN salt varchar(1024) default NULL");
        $PDO->exec("ALTER TABLE user ADD COLUMN last_login datetime default NULL");
        $PDO->exec("ALTER TABLE user ADD COLUMN last_failure datetime default NULL");
        $PDO->exec("ALTER TABLE user ADD COLUMN failure_count int(11) default NULL");
        echo '<li>Added fields to user table...</li>';

        $PDO->exec("ALTER TABLE page ADD COLUMN valid_until datetime default NULL");
        echo '<li>Added fields to page table...</li>';
        ob_flush(); flush();
        sleep(1);

        // CHANGING FIELDS
        // rename to tmp table
        $PDO->exec("ALTER TABLE user RENAME TO user_tmp");
        // create new table
        $PDO->exec("CREATE TABLE user (
            id INTEGER NOT NULL PRIMARY KEY,
            name varchar(100) default NULL ,
            email varchar(255) default NULL ,
            username varchar(40) NOT NULL ,
            password varchar(1024) default NULL ,
            salt varchar(1024) default NULL ,
            language varchar(5) default NULL ,
            last_login datetime default NULL,
            last_failure datetime default NULL,
            failure_count int(11) default NULL,
            created_on datetime default NULL ,
            updated_on datetime default NULL ,
            created_by_id int(11) default NULL ,
            updated_by_id int(11) default NULL
        )");
        // copy content
        $PDO->exec("INSERT INTO user(id, name, email, username, password, salt,
                                     language, last_login, last_failure, failure_count,
                                     created_on, updated_on, created_by_id, updated_by_id)
                    SELECT id, name, email, username, password, salt, language,
                           last_login, last_failure, failure_count, created_on,
                           updated_on, created_by_id, updated_by_id
                    FROM user_tmp");
        // drop tmp table
        $PDO->exec("DROP TABLE user_tmp");
        $PDO->exec("CREATE UNIQUE INDEX user_username ON user (username)");
        echo '<li>Modified fields for user table...</li>';
        ob_flush(); flush();
        sleep(1);

        // rename to tmp table
        $PDO->exec("ALTER TABLE page RENAME TO page_tmp");
        // create new table
        $PDO->exec("CREATE TABLE page (
            id INTEGER NOT NULL PRIMARY KEY,
            title varchar(255) default NULL ,
            slug varchar(100) default NULL ,
            breadcrumb varchar(160) default NULL ,
            keywords varchar(255) default NULL ,
            description text ,
            parent_id int(11) default NULL ,
            layout_id int(11) default NULL ,
            behavior_id varchar(25) NOT NULL default '' ,
            status_id int(11) NOT NULL default '100' ,
            created_on datetime default NULL ,
            published_on datetime default NULL ,
            valid_until datetime default NULL,
            updated_on datetime default NULL ,
            created_by_id int(11) default NULL ,
            updated_by_id int(11) default NULL ,
            position mediumint(6) default '0' ,
            is_protected tinyint(1) NOT NULL default '0' ,
            needs_login tinyint(1) NOT NULL default '2'
        )");
        // copy content
        $PDO->exec("INSERT INTO page(id, title, slug, breadcrumb, keywords, description,
                                parent_id, layout_id, behavior_id, status_id, created_on,
                                published_on, valid_until, updated_on, created_by_id,
                                updated_by_id, position, is_protected, needs_login)
                    SELECT id, title, slug, breadcrumb, keywords, description,
                                parent_id, layout_id, behavior_id, status_id, created_on,
                                published_on, valid_until, updated_on, created_by_id,
                                updated_by_id, position, is_protected, needs_login
                    FROM page_tmp");
        // drop tmp table
        $PDO->exec("DROP TABLE page_tmp");
        echo '<li>Modified fields for page table...</li>';
        ob_flush(); flush();
        sleep(1);

        // ADDING TABLES
        $PDO->exec("CREATE TABLE secure_token (
            id INTEGER NOT NULL PRIMARY KEY,
            username varchar(40) default NULL ,
            url varchar(255) default NULL ,
            time varchar(100) default NULL
        )");
        $PDO->exec("CREATE UNIQUE INDEX username_url ON secure_token (username,url)");
        echo '<li>Added table: secure_token...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("CREATE TABLE role (
            id INTEGER NOT NULL PRIMARY KEY,
            name varchar(25) NOT NULL
        )");
        $PDO->exec("CREATE UNIQUE INDEX role_name ON role (name)");
        echo '<li>Added table: role...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("INSERT INTO role (id, name) VALUES (1, 'administrator')");
        $PDO->exec("INSERT INTO role (id, name) VALUES (2, 'developer')");
        $PDO->exec("INSERT INTO role (id, name) VALUES (3, 'editor')");
        echo '<li>Added default roles...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("CREATE TABLE user_role (
            user_id int(11) NOT NULL ,
            role_id int(11) NOT NULL
        )");
        $PDO->exec("CREATE UNIQUE INDEX user_role_user_id ON user_role (user_id,role_id)");
        echo '<li>Added table: user_role...</li>';
        ob_flush(); flush();
        sleep(1);

        // Migrate all administrator role mappings
        $sql = 'SELECT user_id FROM user_permission'
             . ' WHERE permission_id = 1';

        $stmt = $PDO->prepare($sql);
        $stmt->execute();

        while ($map = $stmt->fetchObject()) {
            $PDO->exec("INSERT INTO user_role (user_id, role_id) VALUES (".$map->user_id.", 1)");
        }
        echo '<li>Migrated all administrator role mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        // Migrate all developer role mappings
        $sql = 'SELECT user_id FROM user_permission'
             . ' WHERE permission_id = 2';

        $stmt = $PDO->prepare($sql);
        $stmt->execute();

        while ($map = $stmt->fetchObject()) {
            $PDO->exec("INSERT INTO user_role (user_id, role_id) VALUES (".$map->user_id.", 2)");
        }
        echo '<li>Migrated all developer role mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        // Migrate all editor role mappings
        $sql = 'SELECT user_id FROM user_permission'
             . ' WHERE permission_id = 3';

        $stmt = $PDO->prepare($sql);
        $stmt->execute();

        while ($map = $stmt->fetchObject()) {
            $PDO->exec("INSERT INTO user_role (user_id, role_id) VALUES (".$map->user_id.", 3)");
        }
        echo '<li>Migrated all editor role mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        $PDO->exec("CREATE TABLE role_permission (
            role_id int(11) NOT NULL ,
            permission_id int(11) NOT NULL
        )");
        $PDO->exec("CREATE UNIQUE INDEX role_permission_role_id ON role_permission (role_id,permission_id)");
        echo '<li>Added table: role_permission...</li>';
        ob_flush(); flush();
        sleep(1);

        // Role 1 = administrator
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 1)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 2)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 3)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 4)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 5)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 6)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 7)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 8)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 9)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 10)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 11)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 12)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 13)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 14)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 15)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 16)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 17)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 18)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 19)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 20)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 21)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 22)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 23)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 24)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 25)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 26)");
        sleep(1);

        // Role 2 = developer
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 1)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 7)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 8)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 9)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 10)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 11)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 12)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 13)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 14)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 15)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 16)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 17)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 18)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 19)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 20)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 21)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 22)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 23)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 24)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 25)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 26)");
        sleep(1);

        // Role 2 = editor
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 1)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 15)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 16)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 17)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 18)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 19)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 20)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 21)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 22)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 23)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 24)");
        $PDO->exec("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 25)");
        echo '<li>Added default role permission mappings...</li>';
        ob_flush(); flush();
        sleep(1);

        // Updating permissions table
        $PDO->exec("DELETE FROM permission");
        $PDO->exec("DROP INDEX permission_name");
        $PDO->exec("CREATE UNIQUE INDEX permission_name ON permission (name)");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (1, 'admin_view')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (2, 'admin_edit')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (3, 'user_view')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (4, 'user_add')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (5, 'user_edit')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (6, 'user_delete')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (7, 'layout_view')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (8, 'layout_add')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (9, 'layout_edit')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (10, 'layout_delete')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (11, 'snippet_view')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (12, 'snippet_add')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (13, 'snippet_edit')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (14, 'snippet_delete')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (15, 'page_view')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (16, 'page_add')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (17, 'page_edit')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (18, 'page_delete')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (19, 'file_manager_view')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (20, 'file_manager_upload')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (21, 'file_manager_mkdir')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (22, 'file_manager_mkfile')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (23, 'file_manager_rename')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (24, 'file_manager_chmod')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (25, 'file_manager_delete')");
        $PDO->exec("INSERT INTO permission (id, name) VALUES (26, 'backup_restore_view')");
        echo '<li>Emptied permission table and added default permissions...</li>';
        ob_flush(); flush();
        sleep(1);

        // DELETING TABLES
        $PDO->exec("DROP TABLE user_permission");
        echo '<li>Removed table: user_permission...</li>';
    }
    catch (Exception $e) {
        echo '<li>Automated database upgrade failed.<br/>Error message was: ' . $e->getMessage() . '</li>';
    }
}

// POSTGRESQL
if ($driver == 'pgsql') {
    // Nothing to do for PostgreSQL since support
    // for it is introduced with this version of Wolf CMS.
    echo '<li>An impossible situation was detected! Your config file claims you
          are using PostgreSQL which is not possible since it was not supported
          on Wolf CMS 0.6.0</li>';
}

?>
</ul>
</li>
<li><strong>Upgrade finished!</strong></li>
</ul>
<p>
    Please check the <a href="../../security.php">security advisory</a> next.
</p>