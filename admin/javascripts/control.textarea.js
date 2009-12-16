/*
 * Copyright (c) 2009 Benoit Chesneau <benoitc@e-engura.org>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.

 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * Some code borrowed to livepipe :
 * Copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * License MIT
 * Url: http://livepipe.net/control/textarea
 *
 */

var toolbars;

(function($) {

  // From Prototype's array.js
  var $A = function(iterable) {
    if (!iterable) return [];
    if (iterable.toArray) {
      return iterable.toArray();
    } else {
      var results = [];
      for (var i = 0; i < iterable.length; i++)
      results.push(iterable[i]);
      return results;
    }
  }

  Function.prototype.ebind = function() {
    var __method = this, args = $A(arguments), object = args.shift();
    return function() {
      return __method.apply(object, args.concat($A(arguments)));
    }
  }

  Function.prototype.bindAsEventListener = function() {
    var __method = this,
    args = $A(arguments),
    object = args.shift();
    return function(event) {
      return __method.apply(object, [event || window.event].concat(args));
    }
  }

  K = function(x) {
    return x
  }

  Array.prototype.collect = function(iterator, context) {
    iterator = iterator || K;
    var results = [];
    $(this).each(function(index, value) {
      results.push(iterator.call(context, value, index));
    });
    return results;
  },


  $.fn.TextArea = function(options) {
    options = options || {};

    var defaults = {
      tab_spacing: true,
      tab_char: 4,
      lineHeight: 16,
      change: null,
      resizeable: true,
      resizeClassName: "resizeHandle"
    };

    var options = $.extend(defaults, options);
    return this.each(function() {
      new TextArea(this, options);
    });
  }

  function TextArea(el, options) {
    return this instanceof TextArea
    ? this.init(el, options)
    : TextArea(el, options);
  }

  $.extend(TextArea.prototype, {

    onChangeTimeoutLength: 500,

    init: function(el, options) {
      var self = this;
      this.el = $(el);
      this.element = this.el[0];
      this.options = options;
      this.toolbar = options.toolbar;

      this._change_callback = options.change;

      $.extend(el, {
        textarea: this
      });

      // detection of browser for webkit
      var ua = navigator.userAgent.toLowerCase();
      this.isWebkit = (ua.indexOf('webkit') >= 0);
      this.isChrome = (ua.indexOf('chrome') >= 0);
      this.isOpera = (ua.indexOf('opera') >= 0);

      if (this.options.resizeable && !this.isWebkit) {
        resizeHandle = $('<div class="' + this.options.resizeClassName + '"></div>')
        .insertAfter(this.el)
        .bind("mousedown", function(e) {
          var h = self.el.height(), y = e.clientY, mouseMove, mouseUp;
          mouseMove = function(e) {
            self.el.css("height", Math.max(20, e.clientY+h-y)+"px");
            return false;
          };
          mouseUp = function(e) {
            $("html").unbind("mousemove", mouseMove).unbind("mouseup", mouseUp);
            return false;
          };
          $("html").bind("mousemove", mouseMove).bind("mouseup", mouseUp);
        });
      }

      this.lastSelection = {};

      // we need to add one character for webkit
      if (this.isWebkit)
        this.options.tab_char += 1;

      if (this.options.tab_spacing) {
        this.tabulation = "";
        for (var i=0; i<this.options.tab_char; i++)
          this.tabulation += " ";
      } else {
        this.tabulation = "\t";
      }

      this.tab_detected = false;

      // init selection for ie
      if (!!document.selection)
        this.element.selectionStart = this.element.selectionEnd = 0;

      this.el.keydown(this.handleKey.ebind(this));

      var self = this;
      this.doOnChange = function(e) {
        if (self._change_callback) {
          self._change_callback(self.element.value);
        }
        return;
      }

      this.el.keyup(this.doOnChange);
      this.el.bind('paste', this.doOnChange);
      this.el.bind('input', this.doOnChange);

      if(!!document.selection){
  			this.el.bind('mouseup',this.saveRange.bindAsEventListener(this));
  			this.el.bind('keyup',this.saveRange.bindAsEventListener(this));
  		}

  		if (this.isOpera) {
  		  this.el.bind('blur', function(e) {
  		    if (self.lastKey && self.lastKey == 9)
  		      self.el.focus();
  		  });
  		}

    },

    saveRange: function(){
      this.range = document.selection.createRange();
    },
    getValue: function(){
      return this.element.value;
    },

    getSelection: function(){
      if(!!document.selection)
        return document.selection.createRange().text;
      else if(!!this.element.setSelectionRange)
        return this.element.value.substring(this.element.selectionStart,this.element.selectionEnd);
      else
      return false;
    },

    replaceSelection: function(text){
      var scroll_top = this.element.scrollTop;
      if(!!document.selection){
        this.element.focus();
        var range = (this.range) ? this.range : document.selection.createRange();
        range.text = text;
        range.select();
      }else if(!!this.element.setSelectionRange){
        var selection_start = this.element.selectionStart;
        this.element.value = this.element.value.substring(0,selection_start) + text + this.element.value.substring(this.element.selectionEnd);
        this.element.setSelectionRange(selection_start + text.length,selection_start + text.length);
      }
      this.doOnChange();
      this.element.focus();
      this.element.scrollTop = scroll_top;
    },

    wrapSelection: function(before,after){
      this.replaceSelection(before + this.getSelection() + after);
    },
    insertBeforeSelection: function(text){
      this.replaceSelection(text + this.getSelection());
    },
    insertAfterSelection: function(text){
      this.replaceSelection(this.getSelection() + text);
    },
    collectFromEachSelectedLine: function(callback,before,after){
      this.replaceSelection((before || '') + $A(this.getSelection().split("\n")).collect(callback).join("\n") + (after || ''));
    },

    insertBeforeEachSelectedLine: function(text,before,after){
      this.collectFromEachSelectedLine(function(line){
        },before,after);
      },


    handleKey: function(e) {
      c = e.charCode || e.keyCode;
      this.lastKey = c;
      if (c == 9) {
        this.tab_selection();
        if( window.event ){
          e.returnValue = false;
        }
        e.preventDefault();
        e.stopPropagation();
        return false;
      } else if ((c == 13 || c == 10) && (!this.isOpera)) {
        //FIXME: opera disabled for now
        if (this.do_enter()) {
          if( window.event ){
            e.returnValue = false;
          }
          e.preventDefault();
          e.stopPropagation();
          return false;
        }
      }
      return true;
    },

    tab_selection: function() {
      if (this._is_tabbing)
        return;

      this._is_tabbing = true;
      if (!!document.selection && !this.isOpera)
        this._getIESelection();

      if (!this._tab_detected)
        this._detect_tab();

      var start = this.element.selectionStart;
      var end = this.element.selectionEnd;
      var insText = this.element.value.substring(start, end);
      var scrollTop = this.el.scrollTop();
      var scrollLeft = this.el.scrollLeft();

      var pos_start = start;
      var pos_end = end;
      if (insText.length == 0) {
        // if only one line selected
        this.element.value = this.element.value.substr(0, start) +
          this.tabulation + this.element.value.substr(end);
        pos_start = start + this.tabulation.length;
        pos_end = pos_start;
      } else {
        start = Math.max(0, this.element.value.substr(0, start).lastIndexOf("\n") + 1);
        endText = this.element.value.substr(end);
        startText = this.element;
        value.substr(0, start);
        tmp = insText.split("\n");
        insText = this.tabulation + tmp.join("\n" + this.tabulation);
        this.el.val(startText + insText + endText);
        pos_start = start;
        pos_end = this.element.value.indexOf("\n", startText.length + insText.length);
        if (pos_end == -1)
          pos_end = this.element.value.length;
      }
      this.element.selectionStart = pos_start;
      this.element.selectionEnd = pos_end;

      if (!!document.selection && !this.isOpera) {
        this._setIESelection();
        setTimeout(function() {
          self._is_tabbing = false;
        }, 100);
        this._is_tabbing = false;
      } else {
        this._is_tabbing = false;
      }

      this.el.scrollTop(scrollTop);
      this.el.scrollLeft(scrollLeft);
    },

    do_enter: function() {
      if (!!document.selection && !this.isOpera)
        this._getIESelection();

      var scrollTop = this.el.scrollTop();
      var scrollLeft = this.el.scrollLeft();
      var start = this.element.selectionStart;
      var end = this.element.selectionEnd;

      var start_last_line = Math.max(0, this.element.value.substring(0, start).lastIndexOf("\n") + 1);
      var latest_line = this.element.value.substring(start_last_line, start)
      if (latest_line.match(/^[ \t]+$/mg, ""))
        return false;

      var begin_line = latest_line.replace(/^([ \t]*).*/gm, "$1");
      if (begin_line == "\n" || begin_line == "\r\n")
        return false;

      begin_line = begin_line.replace(/\r?\n/g, '')
      if ( !!document.selection) {
        begin_line = "\r\n" + begin_line;
      } else {
        begin_line = "\n" + begin_line;
      }
      this.element.value = this.element.value.substring(0, start) +
        begin_line + this.element.value.substring(end);

      this.area_select(start + begin_line.length, 0);
      this.el.scrollTop(scrollTop);
      this.el.scrollLeft(scrollLeft);
      return true;
    },

    area_select: function(start, length) {
      value = this.el.val();
      start = Math.max(0, Math.min(value.length, start));
      end = Math.max(start, Math.min(value.length, start + length));
      if (!!document.selection && !this.isOpera) {
        this.element.selectionStart = start;
        this.element.selectionEnd = end;
        this._setIESelection();
      } else {
        if (this.isOpera) {
          this.element.setSelectionRange(0, 0);
          this.element.setSelectionRange(end, end);
        } else {
          this.element.setSelectionRange(start, end);
        }

      }
    },

    _detect_tab: function() {
      if (this.element.value.indexOf("\t") > 0) {
        this.tabulation = "\t";
      } else {
        this.tabulation = "";
        for (var i = 0; i < this.options.tab_char; i++)
          this.tabulation += " ";
      }
      this._tab_detected = true;
    },

    _getIESelection: function() {
      this.el.focus();
      var start_range = this.elelement.createTextRange();
      var end_range = start_range.duplicate();
      start_range.moveToBookmark(document.selection.createRange().getBookmark());
      start_range.moveEnd('character', this.element.value.length);
      this.el.selectionStart = this.element.value.length - start_range.text.length;
      end_range.moveToBookmark(document.selection.createRange().getBookmark());
      end_range.moveStart('character', -this.element.value.length);
      this.el.selectionEnd = end_range.text.length;
      if (this.element.selectionEnd < this.element.selectionStart)
        this.element.selectionEnd = this.element.selectionStart;

    },

    _setIESelection: function() {
      var nbLineStart = this.element.value.substr(0,
        this.element.selectionStart).split("\n").length - 1;
      var nbLineEnd = this.element.value.substr(0,
        this.element.selectionEnd).split("\n").length - 1;
      var range = document.selection.createRange();
      range.moveToElementText(this.element);
      range.setEndPoint('EndToStart', range);
      range.collapse(true);
      range.moveStart('character', this.element.selectionStart - nbLineStart);
      range.moveEnd('character', this.element.selectionEnd - nbLineEnd -
        (this.element.selectionStart - nbLineStart));

      range.select();
    }

});


$.Toolbar = function(textarea, options) {
  options = options || {};

  var defaults = {
    className: null
  };

  var options = $.extend(defaults, options);
  return new Toolbar(textarea, options);
}


function Toolbar(textarea, options) {
  return this instanceof Toolbar
  ? this.init(textarea, options)
  : Toolbar(textarea, options);
}

$.extend(Toolbar.prototype, {
  init: function(textarea, options) {
    this.textarea = textarea;
    this.options = options;
    this.containers =[];
    var className = this.options.className || "toolbar";
    var self = this;
    this.textarea.each(function() {
      var container = $(document.createElement('ul'));
      container.addClass(className)
      container.insertBefore(this);
      var obj = this;
      self.containers.push({
        container: container,
        textarea: obj
      });
    })

  },

  attachButton: function(node, textarea, callback){
    node.onclick = function(){return false;}
    var obj = textarea.textarea
    $(node).bind('click', callback.bindAsEventListener(obj));

  },

  addButton: function(link_text,callback,attrs){
    for (var i=0; i< this.containers.length; i++) {
      var container = this.containers[i];

      var li = document.createElement('li');
      var a = document.createElement('a');
      a.href = '#';
      this.attachButton(a, container.textarea, callback);
      li.appendChild(a);

      if ('className' in attrs) {
        var cls = attrs['className'];
        attrs['className'] = null;
        delete attrs['className'];
        $(a).addClass(cls);
      }

      $.extend(a,attrs || {});

      if(link_text){
        var span = document.createElement('span');
        span.innerHTML = link_text;
        a.appendChild(span);
      }
      container.container[0].appendChild(li);
    }

  }

});

})(jQuery);





/**
 * @author Ryan Johnson <ryan@livepipe.net>
 * @copyright 2007 LivePipe LLC
 * @package Control.TextArea
 * @license MIT
 * @url http://livepipe.net/projects/control_textarea/
 * @version 2.0.0.RC1
 *

if(typeof(Control) == 'undefined')
	Control = {};
Control.TextArea = Class.create();
Object.extend(Control.TextArea.prototype,{
	onChangeTimeoutLength: 500,
	element: false,
	onChangeTimeout: false,
	initialize: function(textarea){
		this.element = $(textarea);
		$(this.element).observe('keyup',this.doOnChange.bindAsEventListener(this));
		$(this.element).observe('paste',this.doOnChange.bindAsEventListener(this));
		$(this.element).observe('input',this.doOnChange.bindAsEventListener(this));
	},
	doOnChange: function(event){
		if(this.onChangeTimeout)
			window.clearTimeout(this.onChangeTimeout);
		this.onChangeTimeout = window.setTimeout(function(){
			if(this.notify)
				this.notify('change',this.getValue());
		}.bind(this),this.onChangeTimeoutLength);
	},
	getValue: function(){
		return this.element.value;
	},
	getSelection: function(){
		if(!!document.selection)
			return document.selection.createRange().text;
		else if(!!this.element.setSelectionRange)
			return this.element.value.substring(this.element.selectionStart,this.element.selectionEnd);
		else
			return false;
	},
	replaceSelection: function(text){
		if(!!document.selection){
			this.element.focus();
			var old = document.selection.createRange().text;
			var range = document.selection.createRange();
			if(old == '')
				this.element.innerHTML += text;
			else{
				range.text = text;
				range -= old.length - text.length;
			}
		}else if(!!this.element.setSelectionRange){
			var selection_start = this.element.selectionStart;
			this.element.value = this.element.value.substring(0,selection_start) + text + this.element.value.substring(this.element.selectionEnd);
			//this.element.setSelectionRange(selection_start + text.length,selection_start + text.length);
			this.element.setSelectionRange(selection_start, selection_start + text.length);
		}
		this.doOnChange();
		this.element.focus();
	},
	wrapSelection: function(before,after){
		this.replaceSelection(before + this.getSelection() + after);
	},
	insertBeforeSelection: function(text){
		this.replaceSelection(text + this.getSelection());
	},
	insertAfterSelection: function(text){
		this.replaceSelection(this.getSelection() + text);
	},
	injectEachSelectedLine: function(callback,before,after){
		this.replaceSelection((before || '') + $A(this.getSelection().split("\n")).inject([],callback).join("\n") + (after || ''));
	},
	insertBeforeEachSelectedLine: function(text,before,after){
		this.injectEachSelectedLine(function(lines,line){
			lines.push(text + line);
			return lines;
		},before,after);
	}
});
if(typeof(Object.Event) != 'undefined')
	Object.Event.extend(Control.TextArea);

Control.TextArea.ToolBar = Class.create();
Object.extend(Control.TextArea.ToolBar.prototype,{
	textarea: false,
	container: false,
	initialize: function(textarea,toolbar){
		this.textarea = textarea;
		if(toolbar)
			this.container = $(toolbar);
		else{
			this.container = $(document.createElement('ul'));
			this.textarea.element.parentNode.insertBefore(this.container,this.textarea.element);
		}
	},
	attachButton: function(node,callback){
		node.onclick = function(){return false;}
		$(node).observe('click',callback.bindAsEventListener(this.textarea));
	},
	addButton: function(link_text,callback,attrs){
		var li = document.createElement('li');
		var a = document.createElement('a');
		a.href = '#';
		this.attachButton(a,callback);
		li.appendChild(a);
		Object.extend(a,attrs || {});
		if(link_text){
			var span = document.createElement('span');
			span.innerHTML = link_text;
			a.appendChild(span);
		}
		this.container.appendChild(li);
	}
});

*/