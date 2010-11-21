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
 * @todo STILL NEED TO TEST THIS!
 *
 * @version 0.7.0
 * @since   0.7.0
 * @author  Martijn van der Kleijn <martijn.niji@gmail.com>
 * 
 * @package wolf
 * @subpackage installer
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}
?>

<!--p>Thank you for your interest. This script is still unfinished. It will be finished before Wolf CMS 0.7.0 RC1 is released.</p-->
<p>
    Upgrading:
</p>
<ul>
    
<?php
// Check passwords
$data = $_POST['upgrade'];
if ($data['pwd'] != $data['pwd_check']) {
    die('Passwords do not match each other.');
}

// SETUP BASIC WOLF ENVIRONMENT
try {
    $__CMS_CONN__ = new PDO(DB_DSN, DB_USER, DB_PASS);
}
catch (PDOException $error) {
    die('DB Connection failed: '.$error->getMessage());
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

// Get the user from the DB
$user = Record::findOneFrom('User', 'username=?', array($data['username']));

if (!$user) {
    die('Administrative user not correct...');
}

echo '<li>Administrative user found.</li>';

// Get the user's permissions from the DB
$perms = array();
$sql = 'SELECT name FROM '.TABLE_PREFIX.' permission AS permission, '.TABLE_PREFIX.'user_permission'
     . ' WHERE permission_id = permission.id AND user_id='.$user->id;

$PDO = Record::getConnection();
$stmt = $PDO->prepare($sql);
$stmt->execute();

while ($perm = $stmt->fetchObject())
    $perms[] = $perm->name;

if (!in_array('administrator', $perms)) {
    die('Administrative permissions not correct.');
}

echo '<li>Administrative user has appropriate permissions...</li>';

// Check administrative user's password
if ($user->password != sha1($data['pwd'])) {
    die('Administrative password not correct.');
}

echo '<li>Administrative password correct...</li>';

// SCRIPT UNFINISHED, exiting...
//exit();


/***** SAFETY CHECKS DONE, CONTINUE WITH ACTUAL UPGRADE ******/
echo '<li>Starting database upgrade...<ul>';

// MYSQL
if ($driver == 'mysql') {
    // ADDING NEW FIELDS
    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."user
                ADD COLUMN salt varchar(1024) default NULL,
                ADD COLUMN last_login datetime default NULL,
                ADD COLUMN last_failure datetime default NULL,
                ADD COLUMN failure_count int(11) default NULL
               ");
    echo '<li>Added fields to user table...</li>';

    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."page
                ADD COLUMN valid_until datetime default NULL
               ");
    echo '<li>Added fields to page table...</li>';

    // CHANGING FIELDS
    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."user
                MODFIY COLUMN password varchar(1024) default NULL,
                MODFIY COLUMN language varchar(5) default NULL
               ");
    echo '<li>Modified fields for user table...</li>';

    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."page
                MODFIY COLUMN behavior_id varchar(25) NOT NULL default ''
               ");

    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."page
                MODFIY COLUMN position mediumint(6) unsigned default '0'
               ");
    echo '<li>Modified fields for page table...</li>';

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

    $PDO->exec("CREATE TABLE ".TABLE_PREFIX."role (
                    id int(11) NOT NULL auto_increment,
                    name varchar(25) NOT NULL,
                    PRIMARY KEY  (id),
                    UNIQUE KEY name (name)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
               ");
    echo '<li>Added table: role...</li>';

    $PDO->exec("CREATE TABLE ".TABLE_PREFIX."user_role (
                    user_id int(11) NOT NULL,
                    role_id int(11) NOT NULL,
                    UNIQUE KEY user_id (user_id,role_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8
               ");
    echo '<li>Added table: user_role...</li>';

    $PDO->exec("CREATE TABLE ".TABLE_PREFIX."role_permission (
                    role_id int(11) NOT NULL,
                    permission_id int(11) NOT NULL,
                    UNIQUE KEY user_id (role_id,permission_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8
               ");
    echo '<li>Added table: role_permission...</li>';

    // DELETING TABLES
    $PDO->exec("DROP TABLE ".TABLE_PREFIX."user_permission");
    echo '<li>Removed table: user_permission...</li>';
}

// SQLITE
if ($driver == 'sqlite') {
}

// POSTGRESQL
if ($driver == 'pgsql') {
    // Nothing to do for PostgreSQL since support
    // for it is introduced with this version of Wolf CMS.
}

?>
</ul></li></ul>