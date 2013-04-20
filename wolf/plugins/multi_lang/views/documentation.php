<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The multi lang plugin redirects users to a page with content in their language.
 *
 * The redirect only occurs when a user's indicated preferred language is
 * available. There are multiple methods to determine the desired language.
 * These are:
 *
 * - HTTP_ACCEPT_LANG header
 * - URI based language hint (for example: http://www.example.com/en/page.html
 * - Preferred language setting of logged in users
 *
 * @package Plugins
 * @subpackage multi-lang
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

?>
<h1><?php echo __('Multiple Language Documentation'); ?></h1>
<p>
    The multiple languages plugin allows you to provide language specific content for your end users.
</p>
<p>
    The plugin allows you to choose a preferred method of storing your translations. It also allows you to
    choose the preferred method of determining what translation (if any) should be used for a particular request.
</p>
<p>
    The plugin can store translations as separate tabs on the page where the name of each tab equals the iso 369-1 language
    code of that translation.
</p>
<p>
    The plugin can also store translations as translated copies of the page in a special language subtree of the root.
</p>
<p>
    When you choose "URI" as the language source, it means the Multi Languages plugin will capture the URL that
    is requested by the user. If the URI of the URL contains a valid language code and a translation of the page
    exists in that language, it will display that language.
</p>
<p>
    As an example, in this case a Japanese version of the page is requested: http://www.example.com/ja/page.html
</p>
<p>
    To add a translation for a page as a tab, just:
</p>
<ul style="list-style-position: inside; list-style-type: disc; margin-left: 1em;">
    <li>create a new tab</li>
    <li>give it a iso 639-1 code as its name (see examples below)</li>
    <li>add the translation to the content of the tab</li>
    <li>save the page</li>
</ul>
<p>
    To add a translation for a page as translated copy of the page, just:
</p>
<ul style="list-style-position: inside; list-style-type: disc; margin-left: 1em;">
    <li>create a new page underneath the root or Home page of the site</li>
    <li>give it a iso 639-1 code as its name (see examples below)</li>
    <li>use the drag-to-copy feature to copy the original page to underneath the new "translation root"</li>
    <li>translate the content of the page</li>
    <li>save the page</li>
    <li><strong>Note:</strong> make sure the translated copy of the page has the same name & same slug as the original.</li>
</ul>
<p>The above instructions should give you a page structure similar to the one below if you translate "My Page" into Dutch (nl).</p>
<pre>
    Home Page
       |- My Page
       |- nl
           |- My Page
</pre>
<p>
    Examples of iso 639-1 codes for languages are:
</p>
<ul style="list-style-position: inside; list-style-type: disc; margin-left: 1em;">
    <li>English: en</li>
    <li>Dutch: nl</li>
    <li>Japanese: ja</li>
    <li>French: fr</li>
</ul>
<p>For the full list of language codes, please refer to <a href="http://www.sil.org/iso639-3/codes.asp?order=639_1&letter=%25">the SIL website</a> or <a href="http://nl.wikipedia.org/wiki/Lijst_van_ISO_639-1-codes">Wikipedia</a></p>
