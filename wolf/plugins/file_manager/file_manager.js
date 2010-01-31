
/**
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS.
 *
 * Wolf CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Wolf CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wolf CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Wolf CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

function toggle_chmod_popup(filename) {
	var popup = $j('#chmod-popup');
	//var file_mode = $('#chmod_file_mode');
	$j('#chmod_file_name').value = filename;
	//center(popup);
	//Element.toggle(popup);
	//Field.focus(file_mode);

    var height = $j(document).height();
    var popup_height = popup.height();
    var width = $j(document).width();
    var popup_width = popup.width();
    popup.css({"position" : "absolute", "top" : height/3 - popup_height/2, "left" : width/3 - popup_width/2});
    popup.toggle("normal");
    $j("#chmod_file_mode").focus();
}

function toggle_rename_popup(file, filename) {
	var popup = $j('#rename-popup');
	var file_mode = $j('#rename_file_new_name');
	$j('#rename_file_current_name').value = file;
	file_mode.value = filename;
	//center(popup);
	//Element.toggle(popup);
	//Field.focus(file_mode);

    var height = $j(document).height();
    var popup_height = popup.height();
    var width = $j(document).width();
    var popup_width = popup.width();
    popup.css({"position" : "absolute", "top" : height/3 - popup_height/2, "left" : width/3 - popup_width/2});
    popup.toggle("normal");
    file_mode.focus();
}


$j(document).ready(function() {
    // Make all modal dialogs draggable
    $j("#boxes .window").draggable({
        addClasses: false,
        containment: 'window',
        scroll: false,
        handle: '.titlebar'
    })

	//select all the a tag with name equal to modal
    $j('a.popupLink').click(function(e) {
		//Cancel the link behavior
		e.preventDefault();
		//Get the A tag
		var id = $j(this).attr('href');

		//Get the screen height and width
		var maskHeight = $j(document).height();
		var maskWidth = $j(window).width();

		//Set height and width to mask to fill up the whole screen
		$j('#mask').css({'width':maskWidth,'height':maskHeight,'top':0,'left':0});

		//transition effect
		$j('#mask').show();//fadeIn(10000);
		$j('#mask').fadeTo("slow",0.5);

		//Get the window height and width
		var winH = $j(window).height();
		var winW = $j(window).width();

		//Set the popup window to center
		$j(id).css('top',  winH/2-$j(id).height()/2);
		$j(id).css('left', winW/2-$j(id).width()/2);

		//transition effect
		$j(id).fadeIn(500); //2000

        $j(id+" :input:visible:enabled:first").focus();

	});

	//if close button is clicked
	$j('#boxes .window .close').click(function (e) {
		//Cancel the link behavior
		e.preventDefault();
		$j('#mask, .window').hide();
	});
});