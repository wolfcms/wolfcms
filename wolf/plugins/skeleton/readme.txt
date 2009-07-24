== WHAT IT IS ==

The skeleton plugin serves as a basic plugin template.

This skeleton plugin makes use/provides the following features:
- A controller without a tab
- Three views (sidebar, documentation and settings)
- A documentation page
- A sidebar
- A settings page (that does nothing except display some text)
- Code that gets run when the plugin is enabled (enable.php)

== HOW TO USE IT ==

* To use the settings and documentation pages, you will first need to enable the
  plugin!
* Use the readme.txt file to explain to plugin users how to install the plugin!
* Use this example layout as a basis.
* Apart from enable.php, you can also create a disable.php
* The settings() function in the SkeletonController.php file demonstrates the
  use of getAllSettings(). The API also provides getSetting(), setSetting() and
  setAllSettings().

== NOTES ==

* To use the settings and documentation pages, you will first need to enable
  the plugin!

* In index.php, change the value of require_wolf_version into a non-existant
  Wolf version and look at the plugins list (in the administration section)
  again.

* Did you know you can have the controller make a visible tab (or not)? Change
    Plugin::addController('skeleton', 'Skeleton', 'administrator', false);
  to
    Plugin::addController('skeleton', 'Skeleton', 'administrator', true);
  to have the plugin controller create a tab.

== LICENSE ==

Copyright 2008-2009, Martijn van der Kleijn. <martijn.niji@gmail.com>
This demo plugin is licensed under the GPLv3 License.
<http://www.gnu.org/licenses/gpl.html>