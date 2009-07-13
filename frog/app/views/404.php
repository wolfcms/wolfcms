<?php
/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package frog
 * @subpackage views
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>Content Not Found</title>
  <meta name="description" content="The content you requested was not found." />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="generator" content="frog-cms" />
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