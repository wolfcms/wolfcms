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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package frog
 * @subpackage plugin.comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.2.0
 * @since Frog version 0.9.3
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 */

$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));

// Table structure for table: comment ----------------------------------------

if ($driver == 'mysql')
{
	$PDO->exec("CREATE TABLE ".TABLE_PREFIX."comment (
	  id int(11) unsigned NOT NULL auto_increment,
	  page_id int(11) unsigned NOT NULL default '0',
	  body text,
	  author_name varchar(50) default NULL,
	  author_email varchar(100) default NULL,
	  author_link varchar(100) default NULL,
	  is_approved tinyint(1) unsigned NOT NULL default '1',
	  created_on datetime default NULL,
	  PRIMARY KEY  (id),
	  KEY page_id (page_id),
	  KEY created_on (created_on)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
	
	$PDO->exec("ALTER TABLE ".TABLE_PREFIX."page ADD comment_status tinyint(1) NOT NULL default '0' AFTER status_id");
    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."comment ADD ip char(100) NOT NULL default '0' AFTER author_link");
}
else if ($driver == 'sqlite')
{
	$PDO->exec("CREATE TABLE comment (
	    id INTEGER NOT NULL PRIMARY KEY,
	    page_id int(11) NOT NULL default '0',
	    body text ,
	    author_name varchar(50) default NULL ,
	    author_email varchar(100) default NULL ,
	    author_link varchar(100) default NULL , 
	    is_approved tinyint(1) NOT NULL default '1' , 
	    created_on datetime default NULL
	)");
	
	$PDO->exec("CREATE INDEX comment_page_id ON comment (page_id)");
	$PDO->exec("CREATE INDEX comment_created_on ON comment (created_on)");
    
    $PDO->exec("ALTER TABLE page ADD comment_status tinyint(1) NOT NULL default '0'");
    $PDO->exec("ALTER TABLE comment ADD ip char(100) NOT NULL default '0'");	
}


$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (name, filter_id, content, content_html, created_on, created_by_id) VALUES ('comment-form', '', '<form action=\"<?php echo \$this->url(); ?>\" method=\"post\" id=\"comment_form\"> \r\n<p>\r\n	<input class=\"comment-form-name\" type=\"text\" name=\"comment[author_name]\" id=\"comment_form_name\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_name\"> name (require)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-email\" type=\"text\" name=\"comment[author_email]\" id=\"comment_form_email\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_email\"> email (will not be published) (required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-link\" type=\"text\" name=\"comment[author_link]\" id=\"comment_form_link\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_link\"> website</label>\r\n</p>\r\n<p>\r\n	<?php captcha(); ?>\r\n</p>\r\n<p>\r\n	<textarea class=\"comment-form-body\" id=\"comment_form_body\" name=\"comment[body]\" cols=\"100%\" rows=\"10\"></textarea>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-submit\" type=\"submit\" name=\"commit-comment\" id=\"comment_form_submit\" value=\"Submit comment\" />\r\n</p>\r\n</form>', '<form action=\"<?php echo \$this->url(); ?>\" method=\"post\" id=\"comment_form\"> \r\n<p>\r\n	<input class=\"comment-form-name\" type=\"text\" name=\"comment[author_name]\" id=\"comment_form_name\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_name\"> name (require)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-email\" type=\"text\" name=\"comment[author_email]\" id=\"comment_form_email\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_email\"> email (will not be published) (required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-link\" type=\"text\" name=\"comment[author_link]\" id=\"comment_form_link\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_link\"> website</label>\r\n</p>\r\n<p>\r\n <?php captcha(); ?>\r\n</p>\r\n<p>\r\n	<textarea class=\"comment-form-body\" id=\"comment_form_body\" name=\"comment[body]\" cols=\"100%\" rows=\"10\"></textarea>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-submit\" type=\"submit\" name=\"commit-comment\" id=\"comment_form_submit\" value=\"Submit comment\" />\r\n</p>\r\n</form>', '".date('Y-m-d H:i:s')."', 1);");

$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (name, filter_id, content, content_html, created_on, created_by_id) VALUES ('comment-each', '', '<p><strong><?php echo \$num_comments = comments_count(\$this); ?></strong> comment<?php if (\$num_comments != 1) { echo ''s''; } ?></p>\r\n<?php \$comments = comments(\$this); ?>\r\n<ol class=\"comments\">\r\n<?php foreach (\$comments as \$comment): ?>\r\n  <li class=\"comment\">\r\n    <p><?php echo \$comment->body(); ?></p>\r\n    <p> &#8212; <?php echo \$comment->name(); ?> <small class=\"comment-date\"><?php echo \$comment->date(); ?></small></p>\r\n  </li>\r\n<?php endforeach; // comments; ?>\r\n</ol>', '<p><strong><?php echo \$num_comments = comments_count(\$this); ?></strong> comment<?php if (\$num_comments != 1) { echo ''s''; } ?></p>\r\n<?php \$comments = comments(\$this); ?>\r\n<ol class=\"comments\">\r\n<?php foreach (\$comments as \$comment): ?>\r\n  <li class=\"comment\">\r\n    <p><?php echo \$comment->body(); ?></p>\r\n    <p> — <?php echo \$comment->name(); ?> <small class=\"comment-date\"><?php echo \$comment->date(); ?></small></p>\r\n  </li>\r\n<?php endforeach; // comments; ?>\r\n</ol>', '".date('Y-m-d H:i:s')."', 1)");

// Store settings new style
$settings = array('auto_approve_comment' => '0',
                  'use_captcha' => '1',
                  'rowspage' => '15',
                  'numlabel' => '1'
                 );

Plugin::setAllSettings($settings, 'comment');