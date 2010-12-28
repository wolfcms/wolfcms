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
 * @package Views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>Content Not Found</title>
  <meta name="description" content="The content you requested was not found." />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="generator" content="wolf-cms" />
</head>
<body>
  <h1>Content Not Found (404)</h1>
  <p>The content you requested was not found. It may have been deleted or you may have entered an incorrect address.</p>
  <p>Please return to the <a href="<?php echo URL_PUBLIC; ?>">home page</a> to view the main navigation.</p>
</body>
</html>

<!--
   - Unfortunately, Microsoft added a clever 'feature' to Internet Explorer. 
   - If the text of an error's message is 'too small', specifically less than 512 bytes, 
   - Internet Explorer returns its own error message. You can turn that off, but it's 
   - tricky to find. This comment serves as padding to prevent that behaviour in IE.
-->