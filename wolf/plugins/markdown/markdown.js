/**
 * @author Ryan Johnson <ryan@livepipe.net>
 * @copyright 2007 LivePipe LLC
 * @package Control.TextArea.ToolBar.Markdown
 * @license MIT
 * @url http://livepipe.net/projects/control_textarea/
 * @version 1.0.1
 */

//toolbars["Markdown"] =

    function setupMarkdownToolbar(toolbar, textarea) {


//buttons
		toolbar.addButton('Italics',function(){
            this.wrapSelection('*','*');
		},{
		    id: 'filter_italic_button'
		});

		toolbar.addButton('Bold',function(){
		    this.wrapSelection('**','**');
		},{
		    id: 'filter_bold_button'
		});

		toolbar.addButton('Link',function(){
		    var selection = this.getSelection();
		    var response = prompt('Enter Link URL','');
		    if(response == null)
		        return;
		    this.replaceSelection('[' + (selection == '' ? 'Link Text' : selection) + '](' + (response == '' ? 'http://link_url/' : response).replace(/^(?!(f|ht)tps?:\/\/)/,'http://') + ')');
		},{
		    id: 'filter_link_button'
		});

		toolbar.addButton('Image',function(){
		    var selection = this.getSelection();
		    var response = prompt('Enter Image URL','');
		    if(response == null)
		        return;
		    this.replaceSelection('![' + (selection == '' ? 'Image Alt Text' : selection) + '](' + (response == '' ? 'http://image_url/' : response).replace(/^(?!(f|ht)tps?:\/\/)/,'http://') + ')');
		},{
		    id: 'filter_image_button'
		});

		toolbar.addButton('Heading',function(){
		    var selection = this.getSelection();
		    if(selection == '')
		        selection = 'Heading';
		    this.replaceSelection("\n" + selection + "\n" + $R(0,Math.max(5,selection.length)).collect(function(){'-'}).join('') + "\n");
		},{
		    id: 'filter_h1_button'
		});

		toolbar.addButton('Unordered List',function(event){
		    this.collectFromEachSelectedLine(function(line){
		        return event.shiftKey ? (line.match(/^\*{2,}/) ? line.replace(/^\*/,'') : line.replace(/^\*\s/,'')) : (line.match(/\*+\s/) ? '*' : '* ') + line;
		    });
		},{
		    id: 'filter_unordered_list_button'
		});

		toolbar.addButton('Ordered List',function(event){
		    var i = 0;
		    this.collectFromEachSelectedLine(function(line){
		        if(!line.match(/^\s+$/)){
		            ++i;
		            return event.shiftKey ? line.replace(/^\d+\.\s/,'') : (line.match(/\d+\.\s/) ? '' : i + '. ') + line;
		        }
		    });
		},{
		    id: 'filter_ordered_list_button'
		});

		toolbar.addButton('Block Quote',function(event){
		    this.collectFromEachSelectedLine(function(line){
		        return event.shiftKey ? line.replace(/^\> /,'') : '> ' + line;
		    });
		},{
		    id: 'filter_quote_button'
		});

		toolbar.addButton('Code Block',function(event){
		    this.collectFromEachSelectedLine(function(line){
		        return event.shiftKey ? line.replace(/    /,'') : '    ' + line;
		    });
		},{
		    id: 'filter_code_button'
		});

		toolbar.addButton('Help',function(){
		    window.open('http://daringfireball.net/projects/markdown/dingus');
		},{
		    id: 'filter_help_button'
		});

    }
