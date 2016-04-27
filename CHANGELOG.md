# Unreleased

- Block common php extensions in filemanager upload. Fixes #636 - thanks @narendrabhati
- Fix issue with url for retrieving profile. Fixes #639 - thanks @SamBrishes
- Always generate a new salt for passwords. Fixes #640 - thanks @felberj
- Don't exit flow when disabling plugin. Fixes #637
- Fixed XSS issues in filemanager plugin. thanks @ravindra008
- Added new xssClean() function to Framework
- Fixed missing CSRF token for Layout delete action - thanks @ravindra008
- Fixed XSS issue in forgot password form - thanks @tigerboy07
- Fixed email helper for HTML mails (malformed newlines) - thanks @blondak
- Fixed installer issue with switching to SQLite causing table prefixes to remain - thanks @korbeljak
- Fixed Textile filter causing deprecated warnings
- Fixed bug confusing x0b and x0d - thanks @phplaber
- Fixed bug #520: childrenCount() and children() not working on PostgreSQLplaber - thanks @korbeljak
- Fix rewrite issue with Apache 2.4 - thanks @macropin

# 0.8.3.1

- Fixes a redirect vulnerability (thanks AKA-Clay)
- Fixes a xss issue in file manager (thanks sumitingole)
- No longer allows upload of php files in filemanager (thanks narendrabhati)
- No longer allows rename to php extension in filemanager (thanks narendrabhati)

# 0.8.2

A patch release that patches a single file to fix an SQLInjection issue with the Archive plugin.

# 0.8.1

- Fix view variables not being set when passing array
- Make sure dev stuff doesn't end up in GitHub (etc) downloads
- Minor bug in filemanagerview
- Fix invalid params bug in Page.php
- Cleanup Page::find()
- Fix missing values
- Fix remaining where/values issues
- Fix reorder pages in Chrome
- Refer to http://docs.wolfcms.org from backend footer for docs
- Update jquery version
- Fix deprecated jQuery reference in install.php
- Add events so page comment_status is saved.
- Fix where Pages couldn’t be reordered in some browsers.
- Add a column in Pages where a Layout can be glanced.
- Update Framework.php
- Fixed images overflow in file magager
- Fixed erroneously missing "how to apply" section to GPL license text
- Fix #585 - wrong check for php version
- Fixes #567 - use non-existing ```_send_data()``` replaced by ```_sendData()```

# 0.8.0

* Added BETA feature: automatic find methods
* Added Record::find()
* Added Record::findById()
* Added Record::findOne()
* Added AutoLoader::register() to register the autoloader on the SPL stack
* Fixed bugs related to Node::registerMethod()
* Fixed input validation on Gravatar helper
* Fixed duplicate username issue
* Fixed Snippet add, delete, edit permissions issue
* Fixed Snippet redirect url issue
* Fixed issue allowing non-admins to set 'is_protected' setting on Page
* Fixed issue with editing .html files using FileManager plugin
* Fixed issue clearing cookies after logout
* Fixed issue with unused Tags lingering in DB
* Fixed use of deprecated pref_replace in Kses helper
* Changed children() and childrenOf() page ordering to 'page.position ASC, page.id DESC'
* Updated jQuery to 1.8.3
* Updated MarkItUp to 1.1.14
* Updated translations to latest versions
* Removed legacy (Prototype) JS functions
* Minor fixes & cleanups

## New find..() methods to core's Record class

In this release we've been changing the Record class quite a bit, including introducing a new, more generic Record::find() method and adding two new default methods: findById() and findOne(). The latter two are fairly self-explanatory but with regards to the first we'd like to give a short insight:

```
// give it a path
$page = Page::find('/the/path/to/your/page');

// or an array of options...
$page = Page::find(array('where' => 'created_by_id=12'));
```
The some of the basic options that can be supplied in the array include "where", "order", "offset" and "limit" among others.

## Automatic find..By..() methods

This release introduces a BETA feature in the core's Framework that can be of interest: automatic find..By..() methods. This feature introduces a Finder class which extends Record. Developers who are tired of writing custom find..By..() methods for their models can now extend the Finder class instead of the Record class. This allows them to use find..By..() methods without writing them.

For example:

```
<?php

class MyModel extends Finder {
    public $id;
    public $email;
    public $name;
}

$obj = MyModel::findByName('Peter');
echo $obj->name;

// echo's "Peter"

// Less trivial example: find users with same name
$objects = MyModel::findIdNameEmailByNameOrderedByIdAsc('mike');
```

Simply use a non-existing find..By..() method and it'll respond. Try it out and let us know what you think! Please keep in mind that the implementation still needs some improvements, including performance, and once we start requiring PHP 5.4, this will likely switch to being a Trait.
