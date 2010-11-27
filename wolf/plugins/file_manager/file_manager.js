/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

function toggle_chmod_popup(filename) {
    var popup = $('#chmod-popup');
    $('#chmod_file_name').val(filename);

    var height = $(document).height();
    var popup_height = popup.height();
    var width = $(document).width();
    var popup_width = popup.width();
    popup.css({"position" : "absolute", "top" : height/3 - popup_height/2, "left" : width/3 - popup_width/2});
    popup.toggle("normal");
    $("#chmod_file_mode").focus();
}

function toggle_rename_popup(file, filename) {
    var popup = $('#rename-popup');
    var file_mode = $('#rename_file_new_name');
    $('#rename_file_current_name').val(file);
    file_mode.val(filename);

    var height = $(document).height();
    var popup_height = popup.height();
    var width = $(document).width();
    var popup_width = popup.width();
    popup.css({"position" : "absolute", "top" : height/3 - popup_height/2, "left" : width/3 - popup_width/2});
    popup.toggle("normal");
    file_mode.focus();
}


$(document).ready(function() {
    // Make all modal dialogs draggable
    $("#boxes .window").draggable({
        addClasses: false,
        containment: 'window',
        scroll: false,
        handle: '.titlebar'
    })

	//select all the a tag with name equal to modal
    $('a.popupLink').click(function(e) {
		//Cancel the link behavior
		e.preventDefault();
		//Get the A tag
		var id = $(this).attr('href');

		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();

		//Set height and width to mask to fill up the whole screen
		$('#mask').css({'width':maskWidth,'height':maskHeight,'top':0,'left':0});

		//transition effect
		$('#mask').show();//fadeIn(10000);
		$('#mask').fadeTo("fast",0.5);

		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();

		//Set the popup window to center
		$(id).css('top',  winH/2-$(id).height()/2);
		$(id).css('left', winW/2-$(id).width()/2);

		//transition effect
		$(id).fadeIn("fast"); //2000

        $(id+" :input:visible:enabled:first").focus();

	});

	//if close button is clicked
	$('#boxes .window .close').click(function (e) {
		//Cancel the link behavior
		e.preventDefault();
		$('#mask, .window').hide();
	});
});