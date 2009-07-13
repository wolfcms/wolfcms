# FROG CMS - INFORMATION AND INSTALLATION

## Installation

1. Manually create your database.
   You will need to know the database name, user, and password for installation
   purposes.

   (phpMyAdmin is a good tool for this)

2. Upload the Frog CMS package to your webserver; it is happy to work in a
   subdirectory.

3. Open your browser and go to the frog_path/install/ page.
   (e.g. http://www.mysite.com/install if Frog is in the root;
      or http://www.mysite.com/frog/install if Frog is in a subdirectory)

   Answer all questions after reviewing them carefully!

4. After finishing the installation, you will get a message that includes a
   link to your_frog_dir/admin/ section.

   This page also shows you the administrator's username and password.

5. Delete the /install directory.

6. Remove all write permissions for the config.php file.
   Frog will refuse to execute until you do this.

7. Login with the admin username/password.
   You should change your admin passsword to something private and secure!

## Optional

### To remove the ? in the url

1. Edit file _.htaccess and correct (if necessary) the RewriteBase setting for
   your installation.

2. Rename _.htaccess to .htaccess.

3. Add write permissions to config.php, edit config.php (in Frog's root dir) and
   define USE_MOD_REWRITE to true. Save and don't forget to remove the write
   permissions again.