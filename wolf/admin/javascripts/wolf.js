/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 *
 * @todo Needs further cleanup
 */

function toSlug(value) {
    // Test for non western characters
    // Need to do this in a better way
    var rx=/[a-z]|[A-Z]|[0-9]|[áàâąбćčцдđďéèêëęěфгѓíîïийкłлмñńňóôóпúùûůřšśťтвýыžżźзäæчöøüшщßåяюж]/;

    if (!rx.test(value)) {
        return value;
    }
    else {
        value = $.trim(value);
        value = value.toLowerCase();
        value = value
        .replace(/[áàâą]/g,"a")
        .replace(/[б]/g,"b")
        .replace(/[ćčçц]/g,"c")
        .replace(/[дđď]/g,"d")
        .replace(/[éèêëęě]/g,"e")
        .replace(/[ф]/g,"f")
        .replace(/[гѓ]/g,"g")
        .replace(/[íîïи]/g,"i")
        .replace(/[й]/g,"j")
        .replace(/[к]/g,"k")
        .replace(/[łл]/g,"l")
        .replace(/[м]/g,"m")
        .replace(/[ñńň]/g,"n")
        .replace(/[óôó]/g,"o")
        .replace(/[п]/g,"p")
        .replace(/[úùûů]/g,"u")
        .replace(/[ř]/g,"r")
        .replace(/[šś]/g,"s")
        .replace(/[ťт]/g,"t")
        .replace(/[в]/g,"v")
        .replace(/[ýы]/g,"y")
        .replace(/[žżźз]/g,"z")
        .replace(/[äæ]/g,"ae")
        .replace(/[ч]/g,"ch")
        .replace(/[öø]/g,"oe")
        .replace(/[ü]/g,"ue")
        .replace(/[ш]/g,"sh")
        .replace(/[щ]/g,"shh")
        .replace(/[ß]/g,"ss")
        .replace(/[å]/g,"aa")
        .replace(/[я]/g,"ya")
        .replace(/[ю]/g,"yu")
        .replace(/[ж]/g,"zh")
        .replace(/[^-a-z0-9~\s\.:;+=_]/g, '')
        .replace(/[\s\.:;=+]+/g, '-');

        return value.replace(/[-]+/g, '-');
    }
}

function page_id() {
    if(/(\d+)/.test(window.location.pathname)){
        return RegExp.$1;
    } else if (/(\d+)/.test(window.location.href)) {
        return RegExp.$1;
    }
}

// Updated valid_part_name function for JQuery
function valid_part_name(name) {
  name = name.toLowerCase();
  name = $.trim(name);
  var result = true;

  if (name == '') {
    alert('Part name cannot be empty.');
    return false;
  }

  $('#part-tabs .tabNavigation .tab a').each(function(){
    if (this.text == name) {
      result = false;
      alert('Part name must be unique.');
      throw $break;
    }
  })

  return result;
}

var toggle_reorder = false;
var toggle_copy = false;