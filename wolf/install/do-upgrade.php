<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010-2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * This upgrade file is for single 0.7.7 stable to 0.7.8 stable upgrades!
 * It does NOT take into account any custom changes that were made to the DB.
 *
 * ALWAY MAKE A BACKUP OF THE DB BEFORE UPGRADING!
 *
 * @version 0.7.8
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

/* Define some helper methods - TODO make proper upgrade wizardly thingie in future*/

function singleUpgradeStatement($PDO, $sql, $okMessage) {
    try {
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $PDO->exec($sql);
        echo "<li>$okMessage</li>";
        ob_flush(); flush(); sleep(1);
    }
    catch (Exception $e) {
        echo 'Automated database upgrade failed.<br/>Error message was: ' . $e->getMessage();
    }    
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
$sql = 'SELECT name FROM '.TABLE_PREFIX.'permission AS p, '.TABLE_PREFIX.'user_role AS ur, '.TABLE_PREFIX.'role_permission AS rp'
     . ' WHERE p.id=rp.permission_id AND rp.role_id=ur.role_id AND ur.user_id='.$user->id;

$PDO = Record::getConnection();
$stmt = $PDO->prepare($sql);
$stmt->execute();

while ($perm = $stmt->fetchObject()) {
    $perms[] = $perm->name;
}

if (!in_array('admin_edit', $perms)) {
    die('<strong>Upgrade failed!</strong> Administrative permissions not correct.');
}

echo '<li>Administrative user has appropriate permissions...</li>';

// Check administrative user's password
if (!AuthUser::validatePassword($user, $data['pwd'])) {
    die('<strong>Upgrade failed!</strong> Administrative password not correct.');
}

echo '<li>Administrative password correct...</li>';


/***** SAFETY CHECKS DONE, CONTINUE WITH ACTUAL UPGRADE ******/
echo '<li>Starting database upgrade...<ul>';

// MYSQL
if ($driver == 'mysql') {
    singleUpgradeStatement($PDO, 
            "ALTER TABLE ".TABLE_PREFIX."user ADD CONSTRAINT uc_email UNIQUE (email)",
            'Added constraints to user table...');
}

// SQLITE
if ($driver == 'sqlite') {
    singleUpgradeStatement($PDO,
            "CREATE UNIQUE INDEX uc_email ON ".TABLE_PREFIX."user (email)",
            'Added constraints to user table...');
}

// POSTGRESQL
if ($driver == 'pgsql') {
    singleUpgradeStatement($PDO,
            "ALTER TABLE ".TABLE_PREFIX." ADD CONSTRAINT uc_email UNIQUE (email)",
            'Added constraints to user table...');
}

?>
</ul>
</li>
<li><strong>Upgrade finished!</strong></li>
</ul>
<p>
    Please remember to check the <a href="../../security.php">security advisory</a>.
</p>