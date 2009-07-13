/**
 * @author Ryan Johnson <ryan@livepipe.net>
 * @copyright 2007 LivePipe LLC
 * @package Control.TextArea.ToolBar.Textile
 * @license MIT
 * @url http://livepipe.net/projects/control_textarea/textile
 * @version 1.0.0
 */

Control.TextArea.ToolBar.Textile = Class.create();
Object.extend(Control.TextArea.ToolBar.Textile.prototype,{
	textarea: false,
	toolbar: false,
	options: {},
	initialize: function(textarea,options){
		this.textarea = new Control.TextArea(textarea);
		this.toolbar = new Control.TextArea.ToolBar(this.textarea);
		this.toolbar.container.className ='filter_toolbar';
		this.options = {
			preview: false,
			afterPreview: Prototype.emptyFunction
		};
		Object.extend(this.options,options || {});
		if(this.options.preview){
			this.textarea.observe('change',function(textarea){
				$(this.options.preview).update(Control.TextArea.ToolBar.Textile.format(textarea.getValue()));
				this.options.afterPreview();
			}.bind(this));
		}
		
		//buttons
		this.toolbar.addButton('Bold',function(){
			this.wrapSelection('*','*');
		},{
			id: 'filter_bold_button'
		});
		
		this.toolbar.addButton('Italic',function(){
			this.wrapSelection('_','_');
		},{
			id: 'filter_italic_button'
		});
		
		this.toolbar.addButton('Ordered List',function(event){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/^\#+\s/))
					lines.push(line.replace(/^(\#+\s)/, ''));
				else if (line.match(/^\*+\s/))
					lines.push(line.replace(/\*/, '#'));
				else
					lines.push('# ' + line);
				return lines;
			});
		},{
			id: 'filter_ordered_list_button'
		});
		
		this.toolbar.addButton('Unordered List',function(event){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/^\*+\s/))
					lines.push(line.replace(/^(\*+\s)/, ''));
				else if (line.match(/^\#+\s/))
					lines.push(line.replace(/\#/, '*'));
				else
					lines.push('* ' + line);
				return lines;
			});
		},{
			id: 'filter_unordered_list_button'
		});
		
		this.toolbar.addButton('Heading 1',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, 'h1$2. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('h1. ' + line);
				return lines;
			});
		},{
			id: 'filter_h1_button'
		});
		
		this.toolbar.addButton('Heading 2',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, 'h2$2. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('h2. ' + line);
				return lines;
			});
		},{
			id: 'filter_h2_button'
		});
		
		this.toolbar.addButton('Heading 3',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, 'h3$2. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('h3. ' + line);
				return lines;
			});
		},{
			id: 'filter_h3_button'
		});
		
		this.toolbar.addButton('Heading 4',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, 'h4$2. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('h4. ' + line);
				return lines;
			});
		},{
			id: 'filter_h4_button'
		});
		
		this.toolbar.addButton('Block Quote',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, 'bq$2. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('bq. ' + line);
				return lines;
			});
		},{
			id: 'filter_quote_button'
		});
		
		this.toolbar.addButton('Link',function(){
			selection = this.getSelection();
			response = prompt('Enter Link URL','');
			if(response == null)
				return;
			this.replaceSelection('"' + (selection == '' ? 'Link Text' : selection) + '":' + (response == '' ? 'http://link_url/' : response).replace(/^(?!(f|ht)tps?:\/\/)/,'http://'));
		},{
			id: 'filter_link_button'
		});
		
		this.toolbar.addButton('Image',function(){
			selection = this.getSelection();
			this.replaceSelection('!' + (selection == '' ? 'image_url' : selection) + '!');
		},{
			id: 'filter_image_button'
		});
		
		this.toolbar.addButton('Left Justify',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, '$1. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('p. ' + line);
				return lines;
			});
		},{
			id: 'filter_left_justify_button'
		});
		
		this.toolbar.addButton('Center Text',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, '$1=. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('p=. ' + line);
				return lines;
			});
		},{
			id: 'filter_center_text_button'
		});
		
		this.toolbar.addButton('Right Justify',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, '$1>. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('p>. ' + line);
				return lines;
			});
		},{
			id: 'filter_right_justify_button'
		});
		
		this.toolbar.addButton('Justify',function(){
			this.injectEachSelectedLine(function(lines,line){
				if (line.match(/(p|h[1-6]|bq)(>|=|<|<>)?\. /))
					lines.push(line.replace(/(p|h[1-6]|bq)(>|=|<|<>)?\. /, '$1<>. '));
				else if (line.match(/^\s*$/)) // safari bug
					lines.push(line);
				else
					lines.push('p<>. ' + line);
				return lines;
			});
		},{
			id: 'filter_justify_button'
		});
		
		this.toolbar.addButton('Help',function(){
			window.open('http://www.textism.com/tools/textile/');
		},{
			id: 'filter_help_button'
		});
	}
});

Control.TextArea.ToolBar.Textile.format = function(s){
   var r = s;
    // quick tags first
    qtags = [['\\*', 'strong'],
             ['\\?\\?', 'cite'],
             ['\\+', 'ins'],  //fixed
             ['~', 'sub'],   
             ['\\^', 'sup'], // me
             ['@', 'code']];
    for (var i=0;i<qtags.length;i++) {
        ttag = qtags[i][0]; htag = qtags[i][1];
        re = new RegExp(ttag+'\\b(.+?)\\b'+ttag,'g');
        r = r.replace(re,'<'+htag+'>'+'$1'+'</'+htag+'>');
    }
    // underscores count as part of a word, so do them separately
    re = new RegExp('\\b_(.+?)_\\b','g');
    r = r.replace(re,'<em>$1</em>');

	//jeff: so do dashes
    re = new RegExp('[\s\n]-(.+?)-[\s\n]','g');
    r = r.replace(re,'<del>$1</del>');

    // links
    re = new RegExp('"\\b(.+?)\\(\\b(.+?)\\b\\)":([^\\s]+)','g');
    r = r.replace(re,'<a href="$3" title="$2">$1</a>');
    re = new RegExp('"\\b(.+?)\\b":([^\\s]+)','g');
    r = r.replace(re,'<a href="$2">$1</a>');

    // images
    re = new RegExp('!\\b(.+?)\\(\\b(.+?)\\b\\)!','g');
    r = r.replace(re,'<img src="$1" alt="$2">');
    re = new RegExp('!\\b(.+?)\\b!','g');
    r = r.replace(re,'<img src="$1">');

    // block level formatting

		// Jeff's hack to show single line breaks as they should.
		// insert breaks - but you get some....stupid ones
	    re = new RegExp('(.*)\n([^#\*\n].*)','g');
	    r = r.replace(re,'$1<br />$2');
		// remove the stupid breaks.
	    re = new RegExp('\n<br />','g');
	    r = r.replace(re,'\n');

    lines = r.split('\n');
    nr = '';
    for (var i=0;i<lines.length;i++) {
        line = lines[i].replace(/\s*$/,'');
        changed = 0;
        if (line.search(/^\s*bq\.\s+/) != -1) { line = line.replace(/^\s*bq\.\s+/,'\t<blockquote>')+'</blockquote>'; changed = 1; }

		// jeff adds h#.
        if (line.search(/^\s*h[1-6]\.\s+/) != -1) { 
	    	re = new RegExp('h([1-6])\.(.+)','g');
	    	line = line.replace(re,'<h$1>$2</h$1>');
			changed = 1; 
		}

		if (line.search(/^\s*\*\s+/) != -1) { line = line.replace(/^\s*\*\s+/,'\t<liu>') + '</liu>'; changed = 1; } // * for bullet list; make up an liu tag to be fixed later
        if (line.search(/^\s*#\s+/) != -1) { line = line.replace(/^\s*#\s+/,'\t<lio>') + '</lio>'; changed = 1; } // # for numeric list; make up an lio tag to be fixed later
        if (!changed && (line.replace(/\s/g,'').length > 0)) line = '<p>'+line+'</p>';
        lines[i] = line + '\n';
    }

    // Second pass to do lists
    inlist = 0; 
	listtype = '';
    for (var i=0;i<lines.length;i++) {
        line = lines[i];
        if (inlist && listtype == 'ul' && !line.match(/^\t<liu/)) { line = '</ul>\n' + line; inlist = 0; }
        if (inlist && listtype == 'ol' && !line.match(/^\t<lio/)) { line = '</ol>\n' + line; inlist = 0; }
        if (!inlist && line.match(/^\t<liu/)) { line = '<ul>' + line; inlist = 1; listtype = 'ul'; }
        if (!inlist && line.match(/^\t<lio/)) { line = '<ol>' + line; inlist = 1; listtype = 'ol'; }
        lines[i] = line;
    }

    r = lines.join('\n');
	// jeff added : will correctly replace <li(o|u)> AND </li(o|u)>
    r = r.replace(/li[o|u]>/g,'li>');

    return r;	
}