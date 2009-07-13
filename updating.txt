# FROG CMS - INFORMATION ABOUT UPDATING


## General ##

Please be aware that we only support single version upgrades. That means you
upgrade from 0.9.3 to 0.9.4 and only then to 0.9.5.

If you're upgrading from a pre-0.9.4 release, consider installing from scratch.

Please be aware that all Frog 0.9.4 plugins except the core plugins will require
some minor updates to work with Frog 0.9.5. Please check with the plugin authors
for updated plugins.


## UPDATING FROM 0.9.4 to 0.9.5 ##

WARNING - The split between frontend and backend in the Frog code has been
          removed in this version. This has led to significant code and path
          changes.

          You will need to update *ALL* files.

Please remember to MAKE BACKUPS of Frog and your DB before trying to update!
The upgrade sequence described below will remove all the old files. Be sure to
have a full backup!


## UPGRADE STEPS ##

Note: consider testing this upgrade process on a local test system before
      upgrading your production system.

PLEASE READ THROUGH ALL OF THE STEPS BEFORE STARTING THE PROCEDURE.

1. Create a FULL backup of your database. (structure and data)

2. Create a FULL backup of your all your files. (including config.php)

3. Download and unzip Frog 0.9.5 to a temporary directory.

4. Remove the following files/directories from your temporary 0.9.5 directory:
    - The "public" directory.
    - The config.php file.

5. Login to the admin section of your 0.9.4 installation and disable all plugins.
   (plugins generally shouldn't remove data from the database when being
    disabled, so this shouldn't be a problem.)

6. Remove ALL the files of your old Frog 0.9.4 installation EXCEPT the "public"
   directory. (and except any custom files you may have added of course)

7. Copy/upload ALL files from the temporary directory to your old 0.9.4 Frog
   installation's directory.

8. Copy the original config.php file to your new installation.

9. Run the following SQL commands on your Frog database after adding a prefix to
   the table names if necessary:

    ### NOTE: MYSQL commands, but SQLite commands should be almost the same.

    CREATE TABLE `plugin_settings` (
      `plugin_id` varchar(40) NOT NULL,
      `name` varchar(40) NOT NULL,
      `value` varchar(255) NOT NULL,
      UNIQUE KEY `plugin_setting_id` (plugin_id,name)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    ALTER TABLE `page`
        ADD `needs_login` tinyint(1) NOT NULL DEFAULT '0' COMMENT '' AFTER is_protected;

    ALTER TABLE `setting`
        MODIFY `value` text NOT NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci;

10. Enable the new htaccess file by removing the underscore.

11. Check the new htaccess file settings against your old htaccess file.

12. Lastly, you need to add some options to the config.php file that were added
    in Frog 0.9.5:

    // Should Frog check for updates on Frog itself and the installed plugins?
    define('CHECK_UPDATES', true);

    // The number of seconds before the check for a new Frog version times out in case of problems.
    define('CHECK_TIMEOUT', 3);

13. Re-install any old plugins that your Frog installation were using.

14. Re-enable the plugins one-by-one, each time checking your site remains working.

15. That should be it! Test out your new Frog 0.9.5 system.
    Remember to remove the write permissions from config.php, otherwise, Frog
    will complain.

## TROUBLESHOOTING / NOTES ##

- If you don't see your old comments in the administration area, try disabling and
  then re-enabling the comment plugin.

- The plugin system was changed in Frog 0.9.5 so try to download and install
  plugins that are compatible with Frog 0.9.5. Older plugins may or may not work.

- The public directory now contains a themes subdirectory in a default installation.
  It is advised that all themes for Frog follow the same approach as the
  Normal theme.

- It is advised to always use the htaccess file. You can dis/enable the url
  rewriting by setting RewriteEngine to On/Off.

- Please be very aware that this upgrade procedure DID NOT TOUCH or upgrade your
  "public" directory. It also DID NOT ALTER or upgrade any data in the database.
  This means you're still using a pre-0.9.5 Normal theme.
