<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Textile plugin provides a Filter that uses the Textile parser.
 *
 * @package Plugins
 * @subpackage textile
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

include_once("../../../config.php");

header('Content-Type: text/javascript; charset=UTF-8');

$changedurl = str_replace('//','|',URL_PUBLIC);
$lastslash = strpos($changedurl, '/');
if (false === $lastslash) {
    define('PATH_PUBLIC', '/');
}
else {
    define('PATH_PUBLIC', substr($changedurl, $lastslash));
}

?>

// Wolf CMS Filter Switching system
$(document).ready(function() {
    $('.filter-selector').live('wolfSwitchFilterOut', function(event, filtername, elem) {
        if (filtername == 'textile') {
            elem.markItUpRemove();
        }
    });

    $('.filter-selector').live('wolfSwitchFilterIn', function(event, filtername, elem) {
        if (filtername == 'textile') {
            elem.markItUp(eval(textileSettings));
        }
    });
});

// -------------------------------------------------------------------
// markItUp!
// -------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// -------------------------------------------------------------------
// Textile tags example
// http://en.wikipedia.org/wiki/Textile_(markup_language)
// http://www.textism.com/
// -------------------------------------------------------------------
// Feel free to add more tags
// -------------------------------------------------------------------
textileSettings = {
	nameSpace:		'textile',
	previewParserPath:	'<?php echo PATH_PUBLIC;?><?php echo (USE_MOD_REWRITE === true) ? '' : '?/'; ?><?php echo ADMIN_DIR; ?>/plugin/textile/preview', // path to your Textile parser
        //previewInWindow:        'width=800, height=600, resizable=yes, scrollbars=yes',
        previewAutoRefresh:     true,
	onShiftEnter:		{keepDefault:false, openWith:'\n\n'},
        onTab:                  {keepDefault:false, replaceWith:'    '},
	markupSet: [
		{name:'Heading 1', key:'1', openWith:'h1(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
		{name:'Heading 2', key:'2', openWith:'h2(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
		{name:'Heading 3', key:'3', openWith:'h3(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
		{name:'Heading 4', key:'4', openWith:'h4(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
		{name:'Heading 5', key:'5', openWith:'h5(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
		{name:'Heading 6', key:'6', openWith:'h6(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
		{name:'Paragraph', key:'P', openWith:'p(!(([![Class]!]))!). '},
		{separator:'---------------' },
		{name:'Bold', key:'B', closeWith:'*', openWith:'*'},
		{name:'Italic', key:'I', closeWith:'_', openWith:'_'},
		{name:'Stroke', key:'S', closeWith:'-', openWith:'-'},
		{separator:'---------------' },
		{name:'Bulleted list', openWith:'(!(* |!|*)!)'},
		{name:'Numeric list', openWith:'(!(# |!|#)!)'},
		{separator:'---------------' },
		{name:'Picture', replaceWith:'![![Source:!:http://]!]([![Alternative text]!])!'},
		{name:'Link', openWith:'"', closeWith:'([![Title]!])":[![Link:!:http://]!]', placeHolder:'Your text to link here...' },
		{separator:'---------------' },
		{name:'Quotes', openWith:'bq(!(([![Class]!]))!). '},
		{name:'Code', openWith:'@', closeWith:'@'},
		{separator:'---------------' },
		{name:'Preview', call:'preview', className:'preview'}
	]
}