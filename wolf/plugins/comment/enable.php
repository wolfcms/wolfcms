<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
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
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));

// Setup table structure
if ($driver == 'mysql') {
	$PDO->exec("CREATE TABLE ".TABLE_PREFIX."comment (
	  id int(11) unsigned NOT NULL auto_increment,
	  page_id int(11) unsigned NOT NULL default '0',
	  body text,
	  author_name varchar(50) default NULL,
	  author_email varchar(100) default NULL,
	  author_link varchar(100) default NULL,
          ip char(100) NOT NULL default '0',
	  is_approved tinyint(1) unsigned NOT NULL default '1',
	  created_on datetime default NULL,
	  PRIMARY KEY  (id),
	  KEY page_id (page_id),
	  KEY created_on (created_on)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
	
	$PDO->exec("ALTER TABLE ".TABLE_PREFIX."page ADD comment_status tinyint(1) NOT NULL default '0' AFTER status_id");
}
else if ($driver == 'sqlite') {
    $PDO->exec("CREATE TABLE comment (
                id INTEGER NOT NULL PRIMARY KEY,
                page_id int(11) NOT NULL default '0',
                body text ,
                author_name varchar(50) default NULL ,
                author_email varchar(100) default NULL ,
                author_link varchar(100) default NULL ,
                ip char(100) NOT NULL default '0' ,
                is_approved tinyint(1) NOT NULL default '1' ,
                created_on datetime default NULL
              )");
	
    $PDO->exec("CREATE INDEX comment_page_id ON comment (page_id)");
    $PDO->exec("CREATE INDEX comment_created_on ON comment (created_on)");
    
    $PDO->exec("ALTER TABLE page ADD comment_status tinyint(1) NOT NULL default '0'");
}
else if ($driver == 'pgsql') {
    $PDO->exec("CREATE TABLE " . TABLE_PREFIX . "comment (
        id serial,
        page_id integer NOT NULL DEFAULT 0,
        body text,
        author_name character varying(50) DEFAULT NULL,
        author_email character varying(100) DEFAULT NULL,
        author_link character varying(100) DEFAULT NULL,
        ip char(100) NOT NULL default '0',
        is_approved integer NOT NULL default 1,
        created_on timestamp DEFAULT NULL,
        PRIMARY KEY (id)
    )");

    $PDO->exec("CREATE INDEX comment_page_id ON comment (page_id)");
    $PDO->exec("CREATE INDEX comment_created_on ON comment (created_on)");

    $PDO->exec("ALTER TABLE ".TABLE_PREFIX."page ADD comment_status integer NOT NULL DEFAULT 0");
}


// Install snippets
$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (name, filter_id, content, content_html, created_on, created_by_id) VALUES ('comment-form', '', '<form action=\"<?php echo \$this->url(); ?>\" method=\"post\" id=\"comment_form\"> \r\n<p>\r\n	<input class=\"comment-form-name\" type=\"text\" name=\"comment[author_name]\" id=\"comment_form_name\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_name\"> Name (required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-email\" type=\"text\" name=\"comment[author_email]\" id=\"comment_form_email\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_email\"> Email (will not be published; required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-link\" type=\"text\" name=\"comment[author_link]\" id=\"comment_form_link\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_link\"> Website</label>\r\n</p>\r\n<p>\r\n	<?php captcha(); ?>\r\n</p>\r\n<p>\r\n	<textarea class=\"comment-form-body\" id=\"comment_form_body\" name=\"comment[body]\" cols=\"100%\" rows=\"10\"></textarea>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-submit\" type=\"submit\" name=\"commit-comment\" id=\"comment_form_submit\" value=\"Submit comment\" />\r\n</p>\r\n</form>', '<form action=\"<?php echo \$this->url(); ?>\" method=\"post\" id=\"comment_form\"> \r\n<p>\r\n	<input class=\"comment-form-name\" type=\"text\" name=\"comment[author_name]\" id=\"comment_form_name\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_name\"> Name (required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-email\" type=\"text\" name=\"comment[author_email]\" id=\"comment_form_email\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_email\"> Email (will not be published; required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-link\" type=\"text\" name=\"comment[author_link]\" id=\"comment_form_link\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_link\"> Website</label>\r\n</p>\r\n<p>\r\n <?php captcha(); ?>\r\n</p>\r\n<p>\r\n	<textarea class=\"comment-form-body\" id=\"comment_form_body\" name=\"comment[body]\" cols=\"100%\" rows=\"10\"></textarea>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-submit\" type=\"submit\" name=\"commit-comment\" id=\"comment_form_submit\" value=\"Submit comment\" />\r\n</p>\r\n</form>', '".date('Y-m-d H:i:s')."', 1);");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (name, filter_id, content, content_html, created_on, created_by_id) VALUES ('comment-each', '', '<p><strong><?php echo \$num_comments = comments_count(\$this); ?></strong> comment<?php if (\$num_comments != 1) { echo ''s''; } ?></p>\r\n<?php \$comments = comments(\$this); ?>\r\n<ol class=\"comments\">\r\n<?php foreach (\$comments as \$comment): ?>\r\n  <li class=\"comment\">\r\n    <p><?php echo nl2br(\$comment->body()); ?></p>\r\n    <p> &#8212; <?php echo \$comment->name(); ?> <small class=\"comment-date\"><?php echo \$comment->date(); ?></small></p>\r\n  </li>\r\n<?php endforeach; // comments; ?>\r\n</ol>', '<p><strong><?php echo \$num_comments = comments_count(\$this); ?></strong> comment<?php if (\$num_comments != 1) { echo ''s''; } ?></p>\r\n<?php \$comments = comments(\$this); ?>\r\n<ol class=\"comments\">\r\n<?php foreach (\$comments as \$comment): ?>\r\n  <li class=\"comment\">\r\n    <p><?php echo nl2br(\$comment->body()); ?></p>\r\n    <p> — <?php echo \$comment->name(); ?> <small class=\"comment-date\"><?php echo \$comment->date(); ?></small></p>\r\n  </li>\r\n<?php endforeach; // comments; ?>\r\n</ol>', '".date('Y-m-d H:i:s')."', 1)");

// Store settings new style
$settings = array('auto_approve_comment' => '0',
                  'use_captcha' => '1',
                  'rowspage' => '15',
                  'numlabel' => '1'
                 );

Plugin::setAllSettings($settings, 'comment');
