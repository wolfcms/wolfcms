/**
 * @author Ryan Johnson <ryan@livepipe.net>
 * @copyright 2007 LivePipe LLC
 * @package Control.TextArea
 * @license MIT
 * @url http://livepipe.net/projects/control_textarea/
 * @version 2.0.0.RC1
 */

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