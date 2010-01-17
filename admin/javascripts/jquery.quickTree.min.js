/*
quickTree 0.4 - Simple jQuery plugin to create tree-structure navigation from an unordered list
http://scottdarby.com/

Copyright (c) 2009 Scott Darby

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/
jQuery.fn.quickTree=function(){return this.each(function(){var a=$(this);var b=a.find("li");a.find("li:last-child").addClass("last");a.addClass("tree");a.find("ul").hide();b.each(function(){if($(this).children("ul").length>0){$(this).addClass("root").prepend('<span class="expand" />')}});$("span.expand").toggle(function(){$(this).toggleClass("contract").nextAll("ul").slideDown()},function(){$(this).toggleClass("contract").nextAll("ul").slideUp()})})};