<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * This file will insert all basic data to database
 *
 * @package Installer
 * @subpackage Database
 */

/* Make sure we've been called using index.php */
if (!defined('INSTALL_SEQUENCE') || !isset($admin_name) || !isset($admin_passwd) || !isset($admin_salt)) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

function wolf_datetime_incrementor() {
    static $cpt=1;
    $cpt++;
    return date('Y-m-d H:i:s', time()+$cpt);
}


//  Dumping data for table: cron -------------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."cron (id, lastrun) VALUES (1, '0')");


//  Dumping data for table: layout -------------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."layout (id, name, content_type, content, created_on, updated_on, created_by_id, updated_by_id) VALUES (1, 'none', 'text/html', '<?php echo \$this->content(); ?>', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."layout (id, name, content_type, content, created_on, updated_on, created_by_id, updated_by_id) VALUES (2, 'Wolf', 'text/html', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-GB\">\r\n\r\n<head>\r\n	<title><?php echo \$this->title(); ?></title>\r\n\r\n  <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=utf-8\" />\r\n  <meta name=\"robots\" content=\"index, follow\" />\r\n  <meta name=\"description\" content=\"<?php echo (\$this->description() != '''') ? \$this->description() : ''Default description goes here''; ?>\" />\r\n  <meta name=\"keywords\" content=\"<?php echo (\$this->keywords() != '''') ? \$this->keywords() : ''default, keywords, here''; ?>\" />\r\n  <meta name=\"author\" content=\"Author Name\" />\r\n\r\n  <link rel=\"favourites icon\" href=\"<?php echo THEMES_PATH; ?>simple/images/favicon.ico\" />\r\n\r\n  <!-- Adapted from Matthew James Taylor''s \"Holy Grail 3 column liquid-layout\" = http://bit.ly/ejfjq -->\r\n  <!-- No snippets used; but snippet blocks for header, secondary nav, and footer are indicated -->\r\n\r\n  <link rel=\"stylesheet\" href=\"<?php echo THEMES_PATH; ?>wolf/screen.css\" media=\"screen\" type=\"text/css\" />\r\n  <link rel=\"stylesheet\" href=\"<?php echo THEMES_PATH; ?>wolf/print.css\" media=\"print\" type=\"text/css\" />\r\n  <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Wolf Default RSS Feed\" href=\"<?php echo URL_PUBLIC.((USE_MOD_REWRITE)?'''':''/?''); ?>rss.xml\" />\r\n\r\n</head>\r\n<body>\r\n\r\n<!-- HEADER - COULD BE SNIPPET / START -->\r\n<div id=\"header\">\r\n	<h1><a href=\"<?php echo URL_PUBLIC; ?>\">Wolf</a><span class=\"tagline\">content management simplified</span></h1>\r\n</div><!-- / #header -->\r\n<div id=\"nav\">\r\n	<ul>\r\n      <li><a<?php echo url_match(''/'') ? '' class=\"current\"'': ''''; ?> href=\"<?php echo URL_PUBLIC; ?>\">Home</a></li>\r\n<?php foreach(\$this->find(''/'')->children() as \$menu): ?>\r\n      <li><?php echo \$menu->link(\$menu->title, (in_array(\$menu->slug, explode(''/'', \$this->path())) ? '' class=\"current\"'': null)); ?></li>\r\n<?php endforeach; ?> \r\n	</ul>\r\n</div><!-- / #nav -->\r\n<!-- HEADER / END -->\r\n\r\n<div id=\"colmask\"><div id=\"colmid\"><div id=\"colright\"><!-- = outer nested divs -->\r\n\r\n	<div id=\"col1wrap\"><div id=\"col1pad\"><!-- = inner/col1 nested divs -->\r\n\r\n		<div id=\"col1\">\r\n		<!-- Column 1 start = main content -->\r\n\r\n<h2><?php echo \$this->title(); ?></h2>\r\n\r\n  <?php echo \$this->content(); ?> \r\n  <?php if (\$this->hasContent(''extended'')) echo \$this->content(''extended''); ?> \r\n\r\n		<!-- Column 1 end -->\r\n		</div><!-- / #col1 -->\r\n	\r\n	<!-- end inner/col1 nested divs -->\r\n	</div><!-- / #col1pad --></div><!-- / #col1wrap -->\r\n\r\n		<div id=\"col2\">\r\n		<!-- Column 2 start = left/running sidebar -->\r\n\r\n  <?php echo \$this->content(''sidebar'', true); ?> \r\n\r\n		<!-- Column 2 end -->\r\n		</div><!-- / #col2 -->\r\n\r\n		<div id=\"col3\">\r\n		<!-- Column 3 start = right/secondary nav sidebar -->\r\n\r\n<!-- THIS CONDITIONAL NAVIGATION COULD GO INTO A SNIPPET / START -->\r\n<?php if (\$this->level() > 0) { \$slugs = explode(''/'', CURRENT_PATH); \$parent = reset(\$slugs); \$topPage = \$this->find(\$parent); } ?>\r\n<?php if(isset(\$topPage) && \$topPage != '''' && \$topPage != null) : ?>\r\n\r\n<?php if (\$this->level() > 0) : ?>\r\n<?php if (count(\$topPage->children()) > 0 && \$topPage->slug() != ''articles'') : ?>\r\n<h2><?php echo \$topPage->title(); ?> Menu</h2>\r\n<ul>\r\n<?php foreach (\$topPage->children() as \$subPage): ?>\r\n    <li><?php echo \$subPage->link(\$subPage->title, (url_start_with(\$subPage->path()) ? '' class=\"current\"'': null)); ?></li>\r\n<?php endforeach; ?>\r\n</ul>\r\n<?php endif; ?>\r\n<?php endif; ?>\r\n<?php endif; ?>\r\n<!-- CONDITIONAL NAVIGATION / END -->\r\n\r\n		<!-- Column 3 end -->\r\n		</div><!-- / #col3 -->\r\n\r\n<!-- end outer nested divs -->\r\n</div><!-- / #colright --></div><!-- /colmid # --></div><!-- / #colmask -->\r\n\r\n<!-- FOOTER - COULD BE SNIPPET / START -->\r\n<div id=\"footer\">\r\n\r\n  <p>&copy; Copyright <?php echo date(''Y''); ?> <a href=\"http://www.wolfcms.org/\" title=\"Wolf\">Your name</a><br />\r\n  <a href=\"http://www.wolfcms.org/\" title=\"Wolf CMS\">Wolf CMS</a> Inside.\r\n  </p>\r\n  \r\n</div><!-- / #footer -->\r\n<!-- FOOTER / END -->\r\n\r\n</body>\r\n</html>', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."layout (id, name, content_type, content, created_on, updated_on, created_by_id, updated_by_id) VALUES (3, 'Simple', 'text/html', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\r\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n  <title><?php echo \$this->title(); ?></title>\r\n\r\n  <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=utf-8\" />\r\n  <meta name=\"robots\" content=\"index, follow\" />\r\n  <meta name=\"description\" content=\"<?php echo (\$this->description() != '''') ? \$this->description() : ''Default description goes here''; ?>\" />\r\n  <meta name=\"keywords\" content=\"<?php echo (\$this->keywords() != '''') ? \$this->keywords() : ''default, keywords, here''; ?>\" />\r\n  <meta name=\"author\" content=\"Author Name\" />\r\n\r\n  <link rel=\"favourites icon\" href=\"<?php echo THEMES_PATH; ?>wolf/images/favicon.ico\" />\r\n    <link rel=\"stylesheet\" href=\"<?php echo THEMES_PATH; ?>simple/screen.css\" media=\"screen\" type=\"text/css\" />\r\n    <link rel=\"stylesheet\" href=\"<?php echo THEMES_PATH; ?>simple/print.css\" media=\"print\" type=\"text/css\" />\r\n    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Wolf Default RSS Feed\" href=\"<?php echo URL_PUBLIC.((USE_MOD_REWRITE)?'''':''/?''); ?>rss.xml\" />\r\n\r\n</head>\r\n<body>\r\n<div id=\"page\">\r\n<?php \$this->includeSnippet(''header''); ?>\r\n<div id=\"content\">\r\n\r\n  <h2><?php echo \$this->title(); ?></h2>\r\n  <?php echo \$this->content(); ?> \r\n  <?php if (\$this->hasContent(''extended'')) echo \$this->content(''extended''); ?> \r\n\r\n</div> <!-- end #content -->\r\n<div id=\"sidebar\">\r\n\r\n  <?php echo \$this->content(''sidebar'', true); ?> \r\n\r\n</div> <!-- end #sidebar -->\r\n<?php \$this->includeSnippet(''footer''); ?>\r\n</div> <!-- end #page -->\r\n</body>\r\n</html>', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."layout (id, name, content_type, content, created_on, updated_on, created_by_id, updated_by_id) VALUES (4, 'RSS XML', 'application/rss+xml', '<?php echo \$this->content(); ?>', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");


//  Dumping data for table: page ---------------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (1, 'Home Page', '', 'Home Page', 0, 2, '', 100, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 0, 1, 0)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (2, 'RSS Feed', 'rss.xml', 'RSS Feed', 1, 4, '', 101, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 2, 1, 0)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (3, 'About us', 'about-us', 'About us', 1, 0, '', 100, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 0, 0, 2)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (4, 'Articles', 'articles', 'Articles', 1, 0, 'archive', 100, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 1, 1, 2)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (5, 'My first article', 'my-first-article', 'My first article', 4, 0, '', 100, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 0, 0, 2)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (6, 'My second article', 'my-second-article', 'My second article', 4, 0, '', 100, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 0, 0, 2)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page (id, title, slug, breadcrumb, parent_id, layout_id, behavior_id, status_id, created_on, published_on, updated_on, created_by_id, updated_by_id, position, is_protected, needs_login) VALUES (7, '%B %Y archive', 'monthly-archive', '%B %Y archive', 4, 0, 'archive_month_index', 101, '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1, 0, 1, 2)");


//  Dumping data for table: page_part ----------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (1, 'body', '', '<?php \r\n\r\n\$page_article = \$this->find(''/articles/'');\r\n\r\nif (\$page_article->childrenCount() > 0) {\r\n    \$last_article = \$page_article->children(array(''limit''=>1, ''order''=>''page.created_on DESC''));\r\n    \$last_articles = \$page_article->children(array(''limit''=>4, ''offset'' => 1, ''order''=>''page.created_on DESC''));\r\n?>\r\n<div class=\"first entry\">\r\n  <h3><?php echo \$last_article->link(); ?></h3>\r\n  <?php echo \$last_article->content(); ?>\r\n  <?php if (\$last_article->hasContent(''extended'')) echo \$last_article->link(''Continue Reading&#8230;''); ?>\r\n  <p class=\"info\">Posted by <?php echo \$last_article->author(); ?> on <?php echo \$last_article->date(); ?></p>\r\n</div>\r\n\r\n<?php foreach (\$last_articles as \$article): ?>\r\n<div class=\"entry\">\r\n  <h3><?php echo \$article->link(); ?></h3>\r\n  <?php echo \$article->content(); ?>\r\n  <?php if (\$article->hasContent(''extended'')) echo \$article->link(''Continue Reading&#8230;''); ?>\r\n  <p class=\"info\">Posted by <?php echo \$article->author(); ?> on <?php echo \$article->date(); ?></p>\r\n</div>\r\n\r\n<?php\r\n    endforeach; \r\n}\r\n?>', '<?php \r\n\r\n\$page_article = \$this->find(''/articles/'');\r\n\r\nif (\$page_article->childrenCount() > 0) {\r\n    \$last_article = \$page_article->children(array(''limit''=>1, ''order''=>''page.created_on DESC''));\r\n    \$last_articles = \$page_article->children(array(''limit''=>4, ''offset'' => 1, ''order''=>''page.created_on DESC''));\r\n?>\r\n<div class=\"first entry\">\r\n  <h3><?php echo \$last_article->link(); ?></h3>\r\n  <?php echo \$last_article->content(); ?>\r\n  <?php if (\$last_article->hasContent(''extended'')) echo \$last_article->link(''Continue Reading&#8230;''); ?>\r\n  <p class=\"info\">Posted by <?php echo \$last_article->author(); ?> on <?php echo \$last_article->date(); ?></p>\r\n</div>\r\n\r\n<?php foreach (\$last_articles as \$article): ?>\r\n<div class=\"entry\">\r\n  <h3><?php echo \$article->link(); ?></h3>\r\n  <?php echo \$article->content(); ?>\r\n  <?php if (\$article->hasContent(''extended'')) echo \$article->link(''Continue Reading&#8230;''); ?>\r\n  <p class=\"info\">Posted by <?php echo \$article->author(); ?> on <?php echo \$article->date(); ?></p>\r\n</div>\r\n\r\n<?php\r\n    endforeach; \r\n}\r\n?>', 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (2, 'body', '', '<?php echo ''<?''; ?>xml version=\"1.0\" encoding=\"UTF-8\"<?php echo ''?>''; ?> \r\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\r\n<channel>\r\n	<title>Wolf CMS</title>\r\n	<link><?php echo BASE_URL ?></link>\r\n	<atom:link href=\"<?php echo BASE_URL ?>rss.xml\" rel=\"self\" type=\"application/rss\+xml\" />\r\n	<language>en-us</language>\r\n	<copyright>Copyright <?php echo date(''Y''); ?>, wolfcms.org</copyright>\r\n	<pubDate><?php echo strftime(''%a, %d %b %Y %H:%M:%S %z''); ?></pubDate>\r\n	<lastBuildDate><?php echo strftime(''%a, %d %b %Y %H:%M:%S %z''); ?></lastBuildDate>\r\n	<category>any</category>\r\n	<generator>Wolf CMS</generator>\r\n	<description>The main news feed from Wolf CMS.</description>\r\n	<docs>http://www.rssboard.org/rss-specification</docs>\r\n	<?php \$articles = \$this->find(''articles''); ?>\r\n	<?php foreach (\$articles->children(array(''limit'' => 10, ''order'' => ''page.created_on DESC'')) as \$article): ?>\r\n	<item>\r\n		<title><?php echo \$article->title(); ?></title>\r\n		<description><?php if (\$article->hasContent(''summary'')) { echo \$article->content(''summary''); } else { echo strip_tags(\$article->content()); } ?></description>\r\n		<pubDate><?php echo \$article->date(''%a, %d %b %Y %H:%M:%S %z''); ?></pubDate>\r\n		<link><?php echo \$article->url(); ?></link>\r\n		<guid><?php echo \$article->url(); ?></guid>\r\n	</item>\r\n	<?php endforeach; ?>\r\n</channel>\r\n</rss>', '<?php echo ''<?''; ?>xml version=\"1.0\" encoding=\"UTF-8\"<?php echo ''?>''; ?> \r\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\r\n<channel>\r\n	<title>Wolf CMS</title>\r\n	<link><?php echo BASE_URL ?></link>\r\n	<atom:link href=\"<?php echo BASE_URL ?>rss.xml\" rel=\"self\" type=\"application/rss\+xml\" />\r\n	<language>en-us</language>\r\n	<copyright>Copyright <?php echo date(''Y''); ?>, wolfcms.org</copyright>\r\n	<pubDate><?php echo strftime(''%a, %d %b %Y %H:%M:%S %z''); ?></pubDate>\r\n	<lastBuildDate><?php echo strftime(''%a, %d %b %Y %H:%M:%S %z''); ?></lastBuildDate>\r\n	<category>any</category>\r\n	<generator>Wolf CMS</generator>\r\n	<description>The main news feed from Wolf CMS.</description>\r\n	<docs>http://www.rssboard.org/rss-specification</docs>\r\n	<?php \$articles = \$this->find(''articles''); ?>\r\n	<?php foreach (\$articles->children(array(''limit'' => 10, ''order'' => ''page.created_on DESC'')) as \$article): ?>\r\n	<item>\r\n		<title><?php echo \$article->title(); ?></title>\r\n		<description><?php if (\$article->hasContent(''summary'')) { echo \$article->content(''summary''); } else { echo strip_tags(\$article->content()); } ?></description>\r\n		<pubDate><?php echo \$article->date(''%a, %d %b %Y %H:%M:%S %z''); ?></pubDate>\r\n		<link><?php echo \$article->url(); ?></link>\r\n		<guid><?php echo \$article->url(); ?></guid>\r\n	</item>\r\n	<?php endforeach; ?>\r\n</channel>\r\n</rss>', 2)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (3, 'body', 'textile', 'This is my site. I live in this city ... I do some nice things, like this and that ...', '<p>This is my site. I live in this city &#8230; I do some nice things, like this and that &#8230;</p>', 3)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (4, 'body', '', '<?php \$last_articles = \$this->children(array(''limit''=>5, ''order''=>''page.created_on DESC'')); ?>\r\n<?php foreach (\$last_articles as \$article): ?>\r\n<div class=\"entry\">\r\n  <h3><?php echo \$article->link(\$article->title); ?></h3>\r\n  <?php echo \$article->content(); ?>\r\n  <p class=\"info\">Posted by <?php echo \$article->author(); ?> on <?php echo \$article->date(); ?>  \r\n     <br />tags: <?php echo join('', '', \$article->tags()); ?>\r\n  </p>\r\n</div>\r\n<?php endforeach; ?>\r\n\r\n', '<?php \$last_articles = \$this->children(array(''limit''=>5, ''order''=>''page.created_on DESC'')); ?>\r\n<?php foreach (\$last_articles as \$article): ?>\r\n<div class=\"entry\">\r\n  <h3><?php echo \$article->link(\$article->title); ?></h3>\r\n  <?php echo \$article->content(); ?>\r\n  <p class=\"info\">Posted by <?php echo \$article->author(); ?> on <?php echo \$article->date(); ?>  \r\n     <br />tags: <?php echo join('', '', \$article->tags()); ?>\r\n  </p>\r\n</div>\r\n<?php endforeach; ?>\r\n\r\n', 4)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (5, 'body', 'markdown', 'My **first** test of my first article that uses *Markdown*.', '<p>My <strong>first</strong> test of my first article that uses <em>Markdown</em>.</p>\n', 5)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (7, 'body', 'markdown', 'This is my second article.', '<p>This is my second article.</p>\n', 6)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (8, 'body', '', '<?php \$archives = \$this->archive->get(); ?>\r\n<?php foreach (\$archives as \$archive): ?>\r\n<div class=\"entry\">\r\n  <h3><?php echo \$archive->link(); ?></h3>\r\n  <p class=\"info\">Posted by <?php echo \$archive->author(); ?> on <?php echo \$archive->date(); ?> \r\n  </p>\r\n</div>\r\n<?php endforeach; ?>', '<?php \$archives = \$this->archive->get(); ?>\r\n<?php foreach (\$archives as \$archive): ?>\r\n<div class=\"entry\">\r\n  <h3><?php echo \$archive->link(); ?></h3>\r\n  <p class=\"info\">Posted by <?php echo \$archive->author(); ?> on <?php echo \$archive->date(); ?> \r\n  </p>\r\n</div>\r\n<?php endforeach; ?>', 7)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (9, 'sidebar', '', '<h3>About Me</h3>\r\n\r\n<p>I''m just a demonstration of how easy it is to use Wolf CMS to power a blog. <a href=\"<?php echo BASE_URL; ?>about-us/\">more ...</a></p>\r\n\r\n<h3>Favorite Sites</h3>\r\n<ul>\r\n  <li><a href=\"http://www.wolfcms.org\">Wolf CMS</a></li>\r\n</ul>\r\n\r\n<?php if(url_match(''/'')): ?>\r\n<h3>Recent Entries</h3>\r\n<?php \$page_article = \$this->find(''/articles/''); ?>\r\n<ul>\r\n<?php foreach (\$page_article->children(array(''limit'' => 10, ''order'' => ''page.created_on DESC'')) as \$article): ?>\r\n  <li><?php echo \$article->link(); ?></li> \r\n<?php endforeach; ?>\r\n</ul>\r\n<?php endif; ?>\r\n\r\n<p><a href=\"<?php echo BASE_URL; ?>articles/\">Archives</a></p>\r\n\r\n<h3>Syndicate</h3>\r\n\r\n<p><a href=\"<?php echo BASE_URL; ?>rss.xml\">Articles RSS Feed</a></p>', '<h3>About Me</h3>\r\n\r\n<p>I''m just a demonstration of how easy it is to use Wolf CMS to power a blog. <a href=\"<?php echo BASE_URL; ?>about-us/\">more ...</a></p>\r\n\r\n<h3>Favorite Sites</h3>\r\n<ul>\r\n  <li><a href=\"http://www.wolfcms.org\">Wolf CMS</a></li>\r\n</ul>\r\n\r\n<?php if(url_match(''/'')): ?>\r\n<h3>Recent Entries</h3>\r\n<?php \$page_article = \$this->find(''/articles/''); ?>\r\n<ul>\r\n<?php foreach (\$page_article->children(array(''limit'' => 10, ''order'' => ''page.created_on DESC'')) as \$article): ?>\r\n  <li><?php echo \$article->link(); ?></li> \r\n<?php endforeach; ?>\r\n</ul>\r\n<?php endif; ?>\r\n\r\n<p><a href=\"<?php echo BASE_URL; ?>articles/\">Archives</a></p>\r\n\r\n<h3>Syndicate</h3>\r\n\r\n<p><a href=\"<?php echo BASE_URL; ?>rss.xml\">Articles RSS Feed</a></p>', 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."page_part (id, name, filter_id, content, content_html, page_id) VALUES (10, 'sidebar', '', '<?php \$article = \$this->find(''articles''); ?>\r\n<?php \$archives = \$article->archive->archivesByMonth(); ?>\r\n\r\n<h3>Archives By Month</h3>\r\n<ul>\r\n<?php foreach (\$archives as \$date): ?>\r\n  <li><a href=\"<?php echo \$this->url(false) .''/''. \$date . URL_SUFFIX; ?>\"><?php echo strftime(''%B %Y'', strtotime(strtr(\$date, ''/'', ''-''))); ?></a></li>\r\n<?php endforeach; ?>\r\n</ul>', '<?php \$article = \$this->find(''articles''); ?>\r\n<?php \$archives = \$article->archive->archivesByMonth(); ?>\r\n\r\n<h3>Archives By Month</h3>\r\n<ul>\r\n<?php foreach (\$archives as \$date): ?>\r\n  <li><a href=\"<?php echo \$this->url(false) .''/''. \$date . URL_SUFFIX; ?>\"><?php echo strftime(''%B %Y'', strtotime(strtr(\$date, ''/'', ''-''))); ?></a></li>\r\n<?php endforeach; ?>\r\n</ul>', 4)");


//  Dumping data for table: permission ---------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (1, 'admin_view')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (2, 'admin_edit')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (3, 'user_view')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (4, 'user_add')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (5, 'user_edit')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (6, 'user_delete')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (7, 'layout_view')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (8, 'layout_add')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (9, 'layout_edit')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (10, 'layout_delete')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (11, 'snippet_view')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (12, 'snippet_add')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (13, 'snippet_edit')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (14, 'snippet_delete')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (15, 'page_view')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (16, 'page_add')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (17, 'page_edit')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (18, 'page_delete')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (19, 'file_manager_view')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (20, 'file_manager_upload')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (21, 'file_manager_mkdir')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (22, 'file_manager_mkfile')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (23, 'file_manager_rename')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (24, 'file_manager_chmod')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (25, 'file_manager_delete')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."permission (id, name) VALUES (26, 'backup_restore_view')");


//  Dumping data for table: role ---------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."role (id, name) VALUES (1, 'administrator')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role (id, name) VALUES (2, 'developer')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role (id, name) VALUES (3, 'editor')");


//  Dumping data for table: setting ------------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('admin_title', 'Wolf CMS')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('admin_email', 'do-not-reply@wolfcms.org')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('language', 'en')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('theme', 'brown_and_green')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('default_status_id', '1')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('default_filter_id', '')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('default_tab', '')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('allow_html_title', 'off')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."setting (name, value) VALUES ('plugins', 'a:5:{s:7:\"textile\";i:1;s:8:\"markdown\";i:1;s:7:\"archive\";i:1;s:14:\"page_not_found\";i:1;s:12:\"file_manager\";i:1;}')");


//  Dumping data for table: plugin_settings
// @todo - should probably be replaced in future by something like Plugin::activate('file_manager') post install.

$PDO->exec("INSERT INTO ".TABLE_PREFIX."plugin_settings (plugin_id, name, value) VALUES ('archive', 'use_dates', '1')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."plugin_settings (plugin_id, name, value) VALUES ('file_manager', 'umask', '0022')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."plugin_settings (plugin_id, name, value) VALUES ('file_manager', 'dirmode', '0755')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."plugin_settings (plugin_id, name, value) VALUES ('file_manager', 'filemode', '0644')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."plugin_settings (plugin_id, name, value) VALUES ('file_manager', 'show_hidden', '0')");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."plugin_settings (plugin_id, name, value) VALUES ('file_manager', 'show_backups', '1')");


//  Dumping data for table: snippet ------------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (id, name, filter_id, content, content_html, created_on, updated_on, created_by_id, updated_by_id) VALUES (1, 'header', '', '<div id=\"header\">\r\n  <h1><a href=\"<?php echo URL_PUBLIC; ?>\">Wolf</a> <span>content management simplified</span></h1>\r\n  <div id=\"nav\">\r\n    <ul>\r\n      <li><a<?php echo url_match(''/'') ? '' class=\"current\"'': ''''; ?> href=\"<?php echo URL_PUBLIC; ?>\">Home</a></li>\r\n<?php foreach(\$this->find(''/'')->children() as \$menu): ?>\r\n      <li><?php echo \$menu->link(\$menu->title, (in_array(\$menu->slug, explode(''/'', \$this->path())) ? '' class=\"current\"'': null)); ?></li>\r\n<?php endforeach; ?> \r\n    </ul>\r\n  </div> <!-- end #navigation -->\r\n</div> <!-- end #header -->', '<div id=\"header\">\r\n  <h1><a href=\"<?php echo URL_PUBLIC; ?>\">Wolf</a> <span>content management simplified</span></h1>\r\n  <div id=\"nav\">\r\n    <ul>\r\n      <li><a<?php echo url_match(''/'') ? '' class=\"current\"'': ''''; ?> href=\"<?php echo URL_PUBLIC; ?>\">Home</a></li>\r\n<?php foreach(\$this->find(''/'')->children() as \$menu): ?>\r\n      <li><?php echo \$menu->link(\$menu->title, (in_array(\$menu->slug, explode(''/'', \$this->path())) ? '' class=\"current\"'': null)); ?></li>\r\n<?php endforeach; ?> \r\n    </ul>\r\n  </div> <!-- end #navigation -->\r\n</div> <!-- end #header -->', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (id, name, filter_id, content, content_html, created_on, updated_on, created_by_id, updated_by_id) VALUES (2, 'footer', '', '<div id=\"footer\"><div id=\"footer-inner\">\r\n  <p>&copy; Copyright <?php echo date(''Y''); ?> <a href=\"http://www.wolfcms.org/\" title=\"Wolf\">Your Name</a><br />\r\n  <a href=\"http://www.wolfcms.org/\" title=\"Wolf CMS\">Wolf CMS</a> Inside.\r\n  </p>\r\n</div></div><!-- end #footer -->', '<div id=\"footer\"><div id=\"footer-inner\">\r\n  <p>&copy; Copyright <?php echo date(''Y''); ?> <a href=\"http://www.wolfcms.org/\" alt=\"Wolf\">Your Name</a><br />\r\n  <a href=\"http://www.wolfcms.org/\" alt=\"Wolf\">Wolf CMS</a> Inside.\r\n  </p>\r\n</div></div><!-- end #footer -->', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");


//  Dumping data for table: user ---------------------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."user (id, name, email, username, password, salt, language, created_on, updated_on, created_by_id, updated_by_id) VALUES (1, 'Administrator', 'admin@yoursite.com', '".$admin_name."', '".$admin_passwd."', '".$admin_salt."', 'en', '".wolf_datetime_incrementor()."', '".wolf_datetime_incrementor()."', 1, 1)");


//  Dumping data for table: user_permission ----------------------------------

$PDO->exec("INSERT INTO ".TABLE_PREFIX."user_role (user_id, role_id) VALUES (1, 1)");


//  Dumping data for table: role_permission ----------------------------------

// Role 1 = administrator
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 2)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 3)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 4)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 5)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 6)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 7)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 8)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 9)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 10)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 11)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 12)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 13)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 14)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 15)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 16)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 17)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 18)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 19)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 20)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 21)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 22)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 23)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 24)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 25)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (1, 26)");

// Role 2 = developer
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 7)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 8)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 9)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 10)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 11)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 12)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 13)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 14)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 15)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 16)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 17)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 18)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 19)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 20)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 21)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 22)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 23)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 24)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 25)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (2, 26)");

// Role 2 = editor
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 1)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 15)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 16)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 17)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 18)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 19)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 20)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 21)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 22)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 23)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 24)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."role_permission (role_id, permission_id) VALUES (3, 25)");



// If this is a PostgreSQL DB, we need to correct the sequences
// The RESTART WITH numbers should be equal to last inserted id + 1
if ($dbdriver == 'pgsql') {
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."cron_id_seq RESTART WITH 2");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."layout_id_seq RESTART WITH 5");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."page_id_seq RESTART WITH 8");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."page_part_id_seq RESTART WITH 11");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."permission_id_seq RESTART WITH 27");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."role_id_seq RESTART WITH 4");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."snippet_id_seq RESTART WITH 3");
    $PDO->exec("ALTER SEQUENCE ".TABLE_PREFIX."user_id_seq RESTART WITH 2");
}