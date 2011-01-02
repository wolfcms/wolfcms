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
    var rx=/[a-z]|[A-Z]|[0-9]|[áàâôäæßéèêëúùûóöøåíîïñü]/;

    if (!rx.test(value)) {
        return value;
    }
    else {
        value = $.trim(value);
        value = value.toLowerCase();
        value = value.replace(/[áàâ]/g,"a").replace(/[éèêë]/g,"e").replace(/[íîï]/g,"i")
        .replace(/[óô]/g,"o").replace(/[úùû]/g,"u").replace(/[ñ]/g,"n")
        .replace(/[äæ]/g,"ae").replace(/[öø]/g,"oe").replace(/[ü]/g,"ue")
        .replace(/[ß]/g,"ss").replace(/[å]/g,"aa")
        .replace(/[^-a-z0-9~\s\.:;+=_]/g, '').replace(/[\s\.:;=+]+/g, '-');

        return value.replace(/[-]+/g, '-');
    }
}

function page_id() {
  return /(\d+)/.test(window.location.pathname) ? RegExp.$1 : '';
}

// When object is available, do function fn.
function when(obj, fn) {
  if (Object.isString(obj)) obj = /^[\w-]+$/.test(obj) ? $(obj) : $(document.body).down(obj);
  if (Object.isArray(obj) && !obj.length) return;
  if (obj) fn(obj);
}

function part_added() {
  var partNameField = $('part-name-field');
  var partIndexField = $('part-index-field');
  var index = parseInt(partIndexField.value);
  tabControl.addTab('tab-' + index,  partNameField.value, 'page-' + index);
  Element.toggle('busy');
  Element.hide('add-part-popup');
  partNameField.value = '';
  partIndexField.value = (index + 1).toString();
  $('add-part-button').disabled = false;
  Field.focus(partNameField);
  tabControl.select(tab);
}

function part_loading() {
  $('add-part-button').disabled = true;
  Element.toggle('busy');
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

function center(element) {
  var header = $('header')
  element = $(element);
  element.style.position = 'absolute';
  var dim = Element.getDimensions(element);
  var top = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
  element.style.top = (top + 200) + 'px';
  element.style.left = ((header.offsetWidth - dim.width) / 2) + 'px';
}

var toggle_reorder = false;
var toggle_copy = false;