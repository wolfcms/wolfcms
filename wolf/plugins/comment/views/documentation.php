<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<h3>How to use this plugin</h3>
<p>
    By default, the comments plugin tab displays for example: "Comments (2/5)". This means that two comments are
    waiting for approval in the moderation list out of five total comments.
</p>
<p>
  On each page edit screen, you will have a drop-down box available called "Comments".
  From this, you can choose between three options:
</p>
<ul>
  <li>none: if you do not want comments displayed on the page.</li>
  <li>open: if you want comments displayed and want people to be able to post comments.</li>
  <li>close: if you want to display comments, but do not want people do be able to post new comments.</li>
</ul>
<p>
  You will need to add this code to your layout:
</p>
<pre>
&lt;?php
    if (Plugin::isEnabled('comment'))
    {
        if ($this->comment_status != Comment::NONE)
            $this->includeSnippet('comment-each');
        if ($this->comment_status == Comment::OPEN)
            $this->includeSnippet('comment-form');
    }
?&gt;
</pre>

<h3>Notes</h3>
<p>
  When you disable the comments plugin, the database table, snippets and page.comment_status stay available.
</p>
<p>
  If you disable the comments plugin, you can leave the code you added to the layout if you want. Use of the isEnabled() function prevents any PHP errors.
</p>

<h3>License</h3>
<p>
  This Wolf plugin has been made available under the GNU GPL3 or later.
</p>
<p>
  Copyright (C) 2008 Philippe Archambault &lt;philippe.archambault@gmail.com&gt;<br/>
  Copyright (C) 2008 Bebliuc George &lt;bebliuc.george@gmail.com&gt;<br/>
  Copyright (C) 2008 Martijn van der Kleijn &lt;martijn.niji@gmail.com&gt;
</p>
<p>
  Please see the full license statement in this plugin's readme.txt file.
</p>
