== WHAT IT IS ==

The Comments plugin provides you with the functionality to enable visitors to
leave comments on your pages.

Dependencies:
    - Frog 0.9.5+
    - Statistics API plugin (optional, see NOTES section)

== HOW TO USE IT ==

On each page edition you will have a drop-down available to choose between 3 
options (none, open and close).

 - none:  if you do not want a comment displayed on the page
 - open:  if you want comment and want people to post comment
 - close: if you want to display comment, but do not want people do post other

You will need to add this little code in your layout:

<?php
    if (Plugins::isEnabled('comment'))
    {
        if ($this->comment_status != Comment::NONE)
            $this->includeSnippet('comment-each');
        if ($this->comment_status == Comment::OPEN)
            $this->includeSnippet('comment-form');
    }
?>

== NOTES ==

* When you disable the comment plugin, database table, snippet and
  page.comment_status stay available.

* Do not forget to remove the code you added to your layout if you delete the
  comments plugin from your system. It will do no harm to leave it in, but will
  clutter your layout.

* When the optional statistics_api plugin is enabled, the comments plugin registers
  an event with the statistics_api plugin each time:
    - a comment is added.

* The statistics_api plugin is a plugin by Martijn van der Kleijn and can be
  downloaded from http://www.vanderkleijn.net/frog-cms/plugins.html

== LICENSE ==

Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>

Comments plugin:

Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
and parts are
    Copyright (C) 2008 Bebliuc George <bebliuc.george@gmail.com>

 This file is part of Frog CMS.

 Frog CMS is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Frog CMS is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.

 Frog CMS has made an exception to the GNU General Public License for plugins.
 See exception.txt for details and the full text.