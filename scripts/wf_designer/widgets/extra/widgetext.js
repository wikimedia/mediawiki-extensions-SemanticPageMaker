(function($) {
	spm_wf_editor.extra.widget.scripts = {};
	spm_wf_editor.extra.widget.timeoutId = 0;
	spm_wf_editor.extra.widget.js = {
		hintOnEmptyWidget : function(widgetObj) {
			var jObj = $( ".spm_wf_wiki_body", widgetObj );
			if(jObj.html().trim() == "") {
				jObj.html('<span style="color:#999999">click to edit "widget"</span>');
			}
		},
        createScriptElement : function(srcs, func) {
       		var loaded = true;
        	for(var i=0;i<srcs.length;++i) {
        		if(spm_wf_editor.extra.widget.scripts[srcs[i].src]) continue;
        		
        		loaded = false;

	            var script = document.createElement("script");
	            script.type = (srcs[i].type != '') ? srcs[i].type : "text/javascript";
	            script.src = srcs[i].src;
				script.onload = script.onreadystatechange = function() {
//					if (script.readyState && script.readyState != 'loaded' && script.readyState != 'complete') {
//						return;
//					}
					script.onreadystatechange = script.onload = null;
					spm_wf_editor.extra.widget.scripts[script.src] = true;

	        		var loaded = true;
		        	for(var i=0;i<srcs.length;++i) {
		        		if(!spm_wf_editor.extra.widget.scripts[srcs[i].src]) {
	        				loaded = false;
	        				break;
	        			}
	        		}
	        		if(loaded) {
	        			func();
	        		}
				};
	            document.getElementsByTagName("head")[0].appendChild(script);
            }
       		if(loaded) {
       			func();
       		}
        },
		updateWidgetObj : function(widget) {
			widget = commonHtmlDecode( widget.trim() );
			var jObj = spm_wf_editor.extra.widget.activedWidgetObj;
			var html = spm_wf_editor.js.getWikiHtml( widget );

			//*
			// no document write used
			jObj.html('');
			jObj.append(html);
			spm_wf_editor.extra.widget.js.hintOnEmptyWidget(jObj);
			spm_wf_editor.js.notifySave();
			/*/
			// for document.write use
			html = $( html.replace(/\<\s*script\b/gi, '<div class="spm_wg_script_holder"></div><script') );
			
			var srcs = [];
			html.each(function() {
				if(!this.tagName || this.tagName.toLowerCase() != 'script') return;
				if(!this.src || this.src == '') return;

				srcs.push( {src:this.src, type:this.type} );
				if(!spm_wf_editor.extra.widget.scripts[this.src]) 
					spm_wf_editor.extra.widget.scripts[this.src] = false;
			});

			spm_wf_editor.extra.widget.js.createScriptElement(srcs,
				function(){
					// never use document.write after the document is closed, but ...
					var doc_write = document.write;
					document.write = function(h) { $(".spm_wg_script_holder", jObj).first().html(h); };
					jObj.html('');
					html.each(function() {
						if( (!this.tagName || this.tagName.toLowerCase() != 'script') ||
							(!this.src || this.src == '') ) {
								jObj.append($(this));
							}
					});

					document.write = doc_write;
					spm_wf_editor.extra.widget.js.hintOnEmptyWidget(jObj);

					spm_wf_editor.js.notifySave();
				}
			);
			*/
		},
		applyWidgetEditor : function(obj) {
			var jObj = $( obj );
			spm_wf_editor.extra.widget.js.hintOnEmptyWidget(jObj);
			
			jObj.mouseover(function(e) {
				spm_wf_editor.extra.widget.activedWidgetObj = jObj;
//				(function(nod) {
//					var node = $(nod);
//					node.addClass('spm_div_hover');
//
//					var cld = node.children();
//					for(var i=0;i<cld.length;++i) {
//						arguments.callee(cld[i]);
//					}
//				})(e);
				jObj.addClass('spm_div_hover');
				e.stopPropagation();
			}).mouseout(function(e) {
				jObj.removeClass('spm_div_hover');
				jObj.find('*').removeClass('spm_div_hover');
			}).fancybox({
				'overlayShow'	: true,
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
				'titlePosition' : 'inside',
				'width'		  	: '90%',
				'height'	  	: '90%',
				'autoScale'		: false,
				'title'         : 'Apply Widget',
				'href'          : '#spm_wf_wg_widget_ui',
				'onStart'       : function(){
			var params = {
				'rsargs[]':[
					'getWGDefaultWidget', 
					commonHtmlDecode( $(".spm_wf_wiki", spm_wf_editor.extra.widget.activedWidgetObj).html().trim() )],
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			var widget_name = '';
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				success: function(data, textStatus){
					widget_name = data;
				},
				async: false
			});
			$("#spm_wf_wg_widget_ui select").val(widget_name).change();
				}
			});
		},
		addNew : function() {
			var widgetObj = $('<div class="spm_wf_widget"><div class="spm_wf_wiki_body"></div><div class="spm_wf_wiki"></div></div>');
			$(spm_wf_editor.js.getUpdateFieldObject()).replaceWith(widgetObj);
			spm_wf_editor.extra.widget.js.applyWidgetEditor(widgetObj);

			spm_wf_editor.js.notifySave();
		},
		initializeUI : function() {
			var _ui = $('<div id="spm_wf_wg_widget_ui"></div>'),
				_sel = $('<select><option value="">&nbsp;</option></select>')
				_list = $('<table style="width:300px"></table>');

			_sel.change(function(){
				_list.html('');
				var params = {
					'rsargs[]':['getWGWidgetFields', 
						_sel.val(), 
						commonHtmlDecode( $(".spm_wf_wiki", spm_wf_editor.extra.widget.activedWidgetObj).html().trim() )
					],
					'action':'ajax',
					'rs':'spm_wf_EditorAccess'
				};
				$.ajax({
					type: "POST",
					url: wgScript,
					data: params,
					dataType: 'json',
					success: function(data, textStatus){
						for(var i=0;i<data.length;++i) {
							var field = data[i];
							var tr = $('<tr><td>' + field[0] + '</td><td><input/></td></tr>').appendTo(_list);
							$("input", tr).val(field[1]);
						}
					},
					async: false
				});
			});

			var params = {
				'rsargs[]':['getWGWidgetWidgets'],
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				dataType: 'json',
				success: function(data, textStatus){
					for(var i=0;i<data.length;++i) {
						var widget = data[i];
						var opt = $('<option>' + widget + '</option>').appendTo(_sel);
						opt.value = widget;
					}
				},
				async: false
			});

			$('<div style="display:none;"></div>').appendTo('body').append(_ui);
			_ui.append(_sel)
				.append(_list)
				.append(function(){
					var update = $('<button>Update</button>');
					update.click(function(){
						var id = _sel.val().trim();
						$.fancybox.close();
						if(id == '') return;

						var widget = '{{#widget:' + _sel.val().trim();
						$("tr", _list).each(function() {
							var td = $("td", this);
							widget += '|' + commonHtmlDecode( $(td[0]).html() ) + '=' + $("input", td[1]).val();
						});
						widget += '}}';
						
						spm_wf_editor.extra.widget.js.updateWidgetObj(widget);
					});
					return update;
				});
		}
	};

	$(document).ready(function() {
		spm_wf_editor.extra.widget.js.initializeUI();

		$(".spm_wf_widget").each(function(){
			if($(this).parent().children().length == 1)
				spm_wf_editor.extra.widget.js.applyWidgetEditor(this);
		});

//		$("script", "body").each(function(){
//			var jObj = $(this);
//			$('<div class="spm_wg_script_holder"></div>').insertBefore(jObj);
//		});

		// do not use document.write again, which may cause error
		spm_wf_editor.extra.widget.scripts.doc_write = document.write;
		document.write = function(html) {};
	});
})(jQuery);