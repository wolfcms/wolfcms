
/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/* String.js ---------------------------------------------------------------*/

Object.extend(String.prototype, {
  upcase: function()
  {
    return this.toUpperCase();
  },

  downcase: function()
  {
    return this.toLowerCase();
  },
  
  toInteger: function()
  {
    return parseInt(this);
  },

  /* TODO - the replace commands here should still be optimized. */
  toSlug: function()
  {
    return this.strip().downcase()
        .replace(/[àâ]/g,"a").replace(/[éèêë]/g,"e").replace(/[îï]/g,"i")
        .replace(/[ô]/g,"o").replace(/[ùû]/g,"u").replace(/[ñ]/g,"n")
        .replace(/[äæ]/g,"ae").replace(/[öø]/g,"oe").replace(/[ü]/g,"ue")
        .replace(/[ß]/g,"ss").replace(/[å]/g,"aa")
        .replace(/[^-a-z0-9~\s\.:;+=_]/g, '').replace(/[\s\.:;=+]+/g, '-');
  }

});


/* Pages.js ----------------------------------------------------------------*/

function toggle_popup(id, focus_field)
{
  var popup = $(id);
  focus_field = $(focus_field);
  center(popup);
  Element.toggle(popup);
  Field.focus(focus_field);
}
function allowTab(event, obj)
{
  var keyCode = event.which ? event.which: event.keyCode;
  
  // 9 is the tab key code
  if (keyCode == 9)
  {
    if (event.type == "keydown")
    {
      if (obj.setSelectionRange) // mozilla, safari
      {
        var content = '\t';
        var start = obj.selectionStart;
        var end = obj.selectionEnd;
      
        // with selection
        if (end - start > 1)
        {
          content += obj.value.substring(start, end);
          content = content.replace(/\n/g, '\n\t');
        }
      
        obj.value = obj.value.substring(0, start) + content + obj.value.substr(end);
        obj.setSelectionRange(start + 1, start + 1);
        obj.focus();
      }
      else if (obj.createTextRange) // ie
      {
        // sorry selection tab doesn't work because I can develop
        // for internet explorer. if you want me do to it, buy me a windows
        // license and I will do it for you!
        // here is my site to contact me: www.philworks.com
        document.selection.createRange().text = '\t';
        obj.onblur = function() { this.focus(); this.onblur = null; };
      }
      // else unsupported browsers
    }
    
    if (event.returnValue) // ie
      event.returnValue = false;
      
    if (event.preventDefault) // dom
      event.preventDefault();

    return false; // should work in all browsers
  }
  return true;
}

function setTextAreaToolbar(textarea, filter)
{
  filter = ('-'+filter.dasherize()).camelize();

  var toolbar_name = textarea + '_toolbar';
  
  // make sure the textarea is display 
  //(maybe some filter will choose to use a iframe like tinycme)
  $(textarea).style.display = 'block';
  
  var ul_toolbar = document.getElementById(toolbar_name);
  if (ul_toolbar != null)
    ul_toolbar.parentNode.removeChild(ul_toolbar);
  
  if (Control.TextArea.ToolBar[filter] != null)
  {
    var tb = new Control.TextArea.ToolBar[filter](textarea);
    tb.toolbar.container.id = toolbar_name;
  }
}


/* RuledTable.js -----------------------------------------------------------*/

var RuledTable = Class.create({
  initialize: function(element)
  {
    if (Prototype.Browser.IE)
      $(element).
        observe('mouseover', this.onMouseOverRow.bindAsEventListener(this, 'addClassName')).
        observe('mouseout', this.onMouseOverRow.bindAsEventListener(this, 'removeClassName'));
  },
  
  onMouseOverRow: function(event, method)
  {
    var row = event.findElement('tr');
    if (row) row[method]('highlight');
  }

});


/* RuledList.js ------------------------------------------------------------*/

var RuledList = Class.create({
  initialize: function(element)
  {
    if (Prototype.Browser.IE)
      $(element).
        observe('mouseover', this.onMouseOverRow.bindAsEventListener(this, 'addClassName')).
        observe('mouseout', this.onMouseOverRow.bindAsEventListener(this, 'removeClassName'));
  },
  
  onMouseOverRow: function(event, method)
  {
    var row = event.findElement('li');
    if (row) row[method]('highlight');
  }

});


/* Sitemap.js --------------------------------------------------------------*/

var SiteMap = Class.create(RuledList, {
  initialize: function($super, element)
  {
    $super(element);
    this.id = element;
    this.readExpandedCookie();
    Event.observe(element, 'click', this.onMouseClickRow.bindAsEventListener(this));
    Event.observe('toggle_copy', 'click', this.copyalize.bindAsEventListener(this));
    Event.observe('toggle_reorder', 'click', this.sortablize.bindAsEventListener(this));
  },
  
  onMouseClickRow: function(event)
  {
    if (this.isExpander(event.target)) {
      var row = event.findElement('li');
      if (this.hasChildren(row)) {
        this.toggleBranch(row, event.target);
      }
    }
  },
  
  hasChildren: function(row)
  {
    return !row.hasClassName('no-children');
  },
  
  isExpander: function(element)
  {
    return element.match('img.expander');
  },
  
  isExpanded: function(row)
  {
    return row.hasClassName('children-visible');
  },
  
  isRow: function(element)
  {
    return element && element.tagName && element.match('li');
  },
  
  extractLevel: function(row)
  {
    if (/level-(\d+)/i.test(row.className))
      return RegExp.$1.toInteger();
  },
  
  extractPageId: function(row)
  {
    if (/page_(\d+)/i.test(row.id)) // script.aculo.us needs _ instead of - (for Sortable)
      return RegExp.$1.toInteger();
  },
  
  getExpanderImageForRow: function(row)
  {
    return row.down('img');
  },
  
  readExpandedCookie: function()
  {
    var matches = document.cookie.match(/expanded_rows=(.+?);/);
    this.expandedRows = matches ? matches[1].split(',') : [];
  },

  saveExpandedCookie: function()
  {
    document.cookie = "expanded_rows=" + this.expandedRows.uniq().join(",");
  }, 

  persistCollapsed: function(row)
  {
    this.expandedRows = this.expandedRows.without(this.extractPageId(row));
    this.saveExpandedCookie();
  },

  persistExpanded: function(row)
  {
    this.expandedRows.push(this.extractPageId(row));
    this.saveExpandedCookie();
  },

  toggleExpanded: function(row, img)
  {
    if (!img) img = this.getExpanderImageForRow(row);
    if (this.isExpanded(row)) {
      img.src = img.src.replace('collapse', 'expand');
      row.removeClassName('children-visible');
      row.addClassName('children-hidden');
      this.persistCollapsed(row);
    } else {
      img.src = img.src.replace('expand', 'collapse');
      row.removeClassName('children-hidden');
      row.addClassName('children-visible');
      this.persistExpanded(row);
    }
  },
  
  sortablize: function()
  {
    Sortable.destroy(this.id);
    this.sortable = Sortable.create(this.id,
    { 
       constraint: 'vertical',
       scroll: window,
       handle: 'handle_reorder',
       tree: true,
       onChange: this.adjustLevelOf,
       onUpdate: this.update
    });
  },
    
  copyalize: function()
  {
    Sortable.destroy(this.id);
    this.sortable = Sortable.create(this.id,
    { 
       constraint: 'vertical',
       scroll: window,
       handle: 'handle_copy',
       tree: true,
       ghosting: true,
       onChange: this.adjustLevelOf,
       onUpdate: this.copy
    });
  },
  
  hideBranch: function(parent, img)
  {
    for (var i = parent.childNodes.length-1; i>=0; i--)
    {
      if (parent.childNodes[i].nodeName == 'UL')
      {
        Element.hide(parent.childNodes[i]);
        break;
      }
    }
    this.toggleExpanded(parent, img);
  },
  
  showBranch: function(parent, img)
  {
    var children = false;
    for (var i=parent.childNodes.length-1; i>=0; i--)
    {
        if (parent.childNodes[i].nodeName == 'UL')
        {
            Element.show(parent.childNodes[i]);
            children = true;
            break;
        }
    }
    
    if (!children) this.getBranch(parent);
    this.toggleExpanded(parent, img);
  },
  
  getBranch: function(row)
  {
    var id = this.extractPageId(row), level = this.extractLevel(row),
        spinner = $('busy-' + id);
    
    new Ajax.Updater(
      row,
      'index.php?/page/children/' + id + '/' + level,
      {
        evalScripts: true,
        asynchronous: true,
        insertion:  "bottom",
        onLoading:  function() { spinner.show(); this.updating = true; }.bind(this),
        onComplete: function() {
          this.sortablize();
          spinner.fade();
          this.updating = false;
          $$('.handle').each(function(e) { e.style.display = toggle_handle ? 'inline': 'none'; });
        }.bind(this)
      }
    );
  },
  
  toggleBranch: function(row, img)
  {
    if (!this.updating) {
      var method = (this.isExpanded(row) ? 'hide' : 'show') + 'Branch';
      this[method](row, img);
    }
  },
  
  adjustLevelOf: function(element)
  {
    // this will make the page displayed at the level + 1 of the parent
    var currentLevel = 1;
    var parentLevel = 0;
    currentElementSelected = element;
  
    if (/level-(\d+)/i.test(element.className))
      currentLevel = RegExp.$1.toInteger();
    
    if (/level-(\d+)/i.test(element.parentNode.parentNode.className))
      parentLevel = RegExp.$1.toInteger();

    if (currentLevel != parentLevel+1)
    {
      Element.removeClassName(element, 'level-'+currentLevel);
      Element.addClassName(element, 'level-'+(parentLevel+1));
    }
    // this will update all childs level
    var container = Element.findChildren(element, false, false, 'UL');
    if (container.length == 1)
    {
      var childs = Element.findChildren(container[0], false, false, 'LI');
      for (var i=0; i < childs.length; i++)
        childs[i].className = childs[i].className.replace(/level-(\d+)/, 'level-'+(parentLevel+2));
    }
  },

  update: function()
  {
    var parent = currentElementSelected.parentNode;
    var parent_id = 1;
    var pages = [];
    var data = '';
  
    if (/page_(\d+)/i.test(currentElementSelected.parentNode.parentNode.id))
      parent_id = RegExp.$1.toInteger();
  
    pages = Element.findChildren(parent, false, false, 'LI');
  
    for(var i=0; i<pages.length; i++)
      data += 'pages[]='+SiteMap.prototype.extractPageId(pages[i])+'&';
  
    new Ajax.Request('index.php?/page/reorder/'+parent_id, {method: 'post', parameters: { 'data': data }});
  },
  
  copy: function(element) 
  {
    var parent = currentElementSelected.parentNode;
    var parent_id = 1;
    var pages = [];
    var data = '';
  
    if (/page_(\d+)/i.test(currentElementSelected.parentNode.parentNode.id)) 
      parent_id = RegExp.$1.toInteger();      

    /* Dragged page. */
    data  = 'dragged_id=' + SiteMap.prototype.extractPageId(currentElementSelected) + '&';
  
    /* We still need this for sorting. */
    pages = Element.findChildren(parent, false, false, 'LI');
    
    for(var i=0; i<pages.length; i++) 
      data += 'pages[]='+SiteMap.prototype.extractPageId(pages[i]) + '&';      
  
    new Ajax.Request('index.php?/page/copy/'+parent_id, {
      method: 'post',
      parameters: { 'data': data },
      onSuccess: function(transport) {
        /* Ugly hack until I figure out how to update only the sitemap. */
        window.location.reload();
      }
    });
    
  }

});


/* TabControl.js -----------------------------------------------------------*/

var TabControl = Class.create({
  /*
    Initializes a tab control. The variable +element_id+ must be the id of an HTML element
    containing one element with it's class name set to 'tabs' and another element with it's
    class name set to 'pages'.
  */
  initialize: function(element)
  {
    this.element = $(element);
    this.control_id = this.element.identify();
    TabControl.controls.set(this.control_id, this);
    this.tab_container = this.element.down('.tabs');
    this.tabs = $H();
    this.onSelect = Prototype.emptyFunction;
  },
  
  /*
    Creates a new tab. The variable +tab_id+ is a unique string used to identify the tab
    when calling other methods. The variable +caption+ is a string containing the caption
    of the tab. The variable +page+ is the ID of an HTML element, or the HTML element
    itself. When a tab is initially added the page element is hidden.
  */
  addTab: function(tab_id, caption, page)
  {
    var tab = new TabControl.Tab(this, tab_id, caption, page);
    this.tabs.set(tab.id, tab);
    return this.tab_container.appendChild(tab.createElement());
  },
  
  /*
    Removes +tab+. The variable +tab+ may be either a tab ID or a tab element.
  */
  removeTab: function(tab)
  {
    if (Object.isString(tab)) tab = this.tabs.get(tab);
    tab.remove();
    this.tabs.unset(tab);
    
    if (this.selected == tab) {
      var first = this.firstTab();
      if (first) this.select(first);
      else this.selected = null;
    }
  },

  /*
    Selects +tab+ updating the control. The variable +tab+ may be either a tab ID or a
    tab element.
  */
  select: function(tab)
  {
    if (Object.isString(tab)) tab = this.tabs.get(tab);
    if (this.selected) this.selected.unselect();
    tab.select();
    this.selected = tab;
    this.onSelect(tab);
  },

  /*
    Returns the first tab element that was added using #addTab().
  */
  firstTab: function()
  {
    return this.tabs.get(this.tabs.keys().first());
  },
  
  /*
    Returns the the last tab element that was added using #addTab().
  */
  lastTab: function()
  {
    return this.tabs.get(this.tabs.keys().last());
  },
  
  /*
    Returns the total number of tab elements managed by the control.
  */
  tabCount: function()
  {
    return this.tabs.keys().length;
  }
  
});

TabControl.controls = $H();

TabControl.Tab = Class.create({
  initialize: function(control, id, label, content)
  {
    this.content = $(content).hide();
    this.label   = label || id;
    this.id      = id;
    this.control = control;
  },

  createElement: function()
  {
    return this.element = new Element('a', { className: 'tab', href: '#' }).
      update("<span>" + this.label + "</span>").
      observe('click', function(event){
        this.control.select(this.id);
        event.stop();
      }.bindAsEventListener(this));
  },

  select: function()
  {
    this.content.show();
    this.element.addClassName('here');
  },

  unselect: function()
  {
    this.content.hide();
    this.element.removeClassName('here');
  },

  remove: function()
  {
    this.content.remove();
    this.element.stopObserving('click').remove();
  }

});


/* Admin.js ----------------------------------------------------------------*/

document.observe('dom:loaded', function() {
  when('site-map', function(table) { new SiteMap(table) });

  when('page_title', function(title) {
    var slug = $('page_slug'),
        breadcrumb = $('page_breadcrumb'),
        oldTitle = title.value;
    
    if (!slug || !breadcrumb) return;
    
    new Form.Element.Observer(title, 0.15, function() {
      if (oldTitle.toSlug() == slug.value) slug.value = title.value.toSlug();
      if (oldTitle == breadcrumb.value) breadcrumb.value = title.value;
      oldTitle = title.value;
    });
  });

  when($$('#pages div.part > input[type=hidden]:first-child'), function(parts) {
    tabControl = new TabControl('tab-control');
    tabControl.onSelect = function(tab) {
      document.cookie = "current_tab=" + page_id() + ':' + tab.id;
    };
    
    parts.each(function(part, index) {
      var page = part.up('.page');
      tabControl.addTab('tab-' + (index + 1), part.value, page.id);
    });

    // Tab page part Auto Select
    var tab, matches = document.cookie.match(/current_tab=(.+?);/);
    if (matches) {
      matches = matches[1].split(':');
      var page = matches[0], tabId = matches[1];

      if (!page || page == page_id()) tab = tabControl.tabs.get(tabId);
    }
    tabControl.select(tab || tabControl.firstTab());

  });
});

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

function valid_part_name() {
  var partNameField = $('part-name-field');
  var name = partNameField.value.downcase().strip();
  var result = true;
  if (name == '') {
    alert('Part name cannot be empty.');
    return false;
  }
  tabControl.tabs.each(function(pair){
    if (tabControl.tabs.get(pair.key).label == name) {
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