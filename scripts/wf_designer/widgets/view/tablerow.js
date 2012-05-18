(function($){
	spm_wf_editor.view["table row"].js = {
		bindEventBeforeShow : function() {
			$("#spm_table_type").change(function() {
				switch( $(this).val() ) {
					case 'common':
						$("#spm_table_class").val('').removeAttr('readonly');
						$("#spm_table_header_class").val('').removeAttr('readonly');
						break;
					case 'l_infobox':
						$("#spm_table_class").val('spm_l_infobox').attr('readonly', 'readonly');
						$("#spm_table_header_class").val('spm_infobox_header').attr('readonly', 'readonly');
						break;
					case 'infobox':
						$("#spm_table_class").val('spm_infobox').attr('readonly', 'readonly');
						$("#spm_table_header_class").val('spm_infobox_header').attr('readonly', 'readonly');
						break;
					case 'hidden':
						$("#spm_table_class").val('spm_hidden_flag').attr('readonly', 'readonly');
						$("#spm_table_header_class").val('').attr('readonly', 'readonly');
						break;
				}
			});
			$("#spm_table_new").change(function() {
				if($(this).is(":checked")) {
					$("#spm_table_new_div").show();
				} else {
					$("#spm_table_new_div").hide();
				}
			});
		},
		updateInNew : function() {
			return $("#spm_table_new").attr("checked");
		},
		getUpdateFieldObject : function() {
			var table = null;
			if(spm_wf_editor.activeLayout != null) {
				$(".spm_table", spm_wf_editor.activeLayout).each(function() {
					if($(".spm_wf_wiki", this).length > 0) table = $(this);
				});
			} else {
				$("#spm_wf_main .spm_table").each(function() {
					if($(".spm_wf_wiki", this).length > 0) table = $(this);
				});
			}
			if(table == null || $("#spm_table_new").attr("checked")) {
				$("#spm_table_new").attr("checked", "checked");
				return spm_wf_editor.js.getUpdateFieldObject();
			} else {
				var table_row = document.createElement("tr");
				table_row.className = "spm_wf_field_placeholder";
				table.append(table_row);
				
				return table_row;
			}
		},
		getFieldDefinition : function() {
			var params = spm_wf_editor.js.getFieldBaseDefinitionView();
			var table = $("#spm_wf_main .spm_table").last();
			params.push(table.length == 0 || $("#spm_table_new").attr("checked"));
			params.push($("#spm_table_type").val().trim());
			params.push($("#spm_table_class").val().trim());
			params.push($("#spm_table_header").val().trim());
			params.push($("#spm_table_header_class").val().trim());
			
			return params;			
		},
		afterFieldUpdated : function() {
			$("#spm_wf_main .spm_tablerow").each(function(){
				if(!this.id) {
					if( spm_wf_editor.js.registerField($(this)) ) {
						var _id = spm_wf_editor.js.getFieldId(
							spm_wf_editor.js.getFieldSettings( this ).field );
						this.id = _id;
						
//						spm_wf_editor.js.activeField( $( this ) );
					}
					
					// append table style editor if necessary
					spm_wf_editor.view["table row"].js.applyTableEditor(
						$(this).parents(spm_wf_editor.iNettuts.settings.widgetSelector));
				}
			});
			return true;
		},
		applyTableEditor : function( widget ) {
			if($(".spm_wf_table_helper", widget).length > 0) return;

			// FIXME: hardcode here
			var tr = $("tr", widget).first();
			if(tr.length == 0) return;
			
			var _wiki = $("div.spm_wf_wiki", tr);
			if(_wiki.html() == null || !_wiki.html().startsWith('{{Wom table begin')) return;

           	var tableEdit = $('<div class="spm_wf_table_helper"></div>');
           	tableEdit.appendTo($(".widget-head", widget))
				.fancybox({
					'overlayShow'	: true,
					'transitionIn'	: 'elastic',
					'transitionOut'	: 'elastic',
					'titlePosition' : 'inside',
					'width'		  	: '90%',
					'height'	  	: '90%',
					'autoScale'		: false,
					'title'         : 'Field Settings',
					'href'          : "#spm_wf_table_settings",
					'onStart'       : function(){
						spm_wf_editor.view["table row"].wikiObj = _wiki;
						var p = _wiki.parent(),
							_settings = $("#spm_wf_table_settings");

						// hardcode here
						var params = _wiki.html().substring(0, _wiki.html().length - 2).split('|');
						var settings = {'header':'', 'class':'', 'header class':''};
						for(var i=1;i<params.length;++i) {
							var s = params[i].split('=', 2);
							if(s.length != 2) continue;
							settings[s[0].trim()]=s[1].trim();
						}

						$("input._header", _settings).val( settings['header'] );
						$("input._class", _settings).val(  settings['class'] );
						$("input._header_class", _settings).val(  settings['header class'] );
					}
				});
		},
		removeField : function( jObj, settings, uiOnly ) {
			// FIXME: hardcode here
			var tbl = jObj.parents("table")[0], 
				lastElement = false, 
				widget = null;
			if($(".spm_tablerow", tbl).length == 1) {
				// remove whole table
				// don't forget the end table template
				jObj = $(tbl);
				jObj.next().remove();
				lastElement = true;
				widget = jObj.parents(spm_wf_editor.iNettuts.settings.widgetSelector);
			}
			spm_wf_editor.js.removeFieldBase( jObj, settings, uiOnly );
			
			if(lastElement) {
				spm_wf_editor.iNettuts.removeWidget(widget);
			}
		}
	};

	// fill in table row ids
	$(document).ready(function() {
		$("#spm_wf_main .spm_tablerow").each(function(){
			if( spm_wf_editor.js.registerField($(this)) ) {
				var _id = spm_wf_editor.js.getFieldId(
					spm_wf_editor.js.getFieldSettings( this ).field );
				this.id = _id;
			}
		});

			$('<div style="display:none"><div id="spm_wf_table_settings"></div></div>').appendTo("body");
			$("#spm_wf_table_settings").append(function() {
				var tbl = $('<table class="stylized" ><tr><td></td></tr></table>')
					.append(
'              <label style="width:200;">Table Header' + "\n" +
'                <span class="small">Optional. Table header in Wiki text.</span>' + "\n" +
'              </label> ' + "\n")
					.append('<input class="_header" type="text" style="margin: 2px 0px 0px 10px;width:240px;" value="">')
					.append(
'              <div style="clear:both;"></div> ' + "\n" +
'              <label style="width:200;">Table Class</span>' + "\n" +
'                <span class="small">Optional. Specify HTML class, e.g., spm_infobox.</span> ' + "\n" +
'              </label>' + "\n")
					.append('<input class="_class" type="text" style="margin: 2px 0px 0px 10px;width:240px;" value="">')
					.append(
'              <div style="clear:both;"></div> ' + "\n" +
'              <label style="width:200;">Table Header Class</span>' + "\n" +
'                <span class="small">Optional. Specify HTML class, e.g., spm_infobox_header.</span> ' + "\n" +
'              </label>' + "\n")
					.append('<input class="_header_class" type="text" style="margin: 2px 0px 0px 10px;width:240px;" value="">')
					.append(
'              <div style="clear:both;"></div>')
					.append(function(){
						return $('<div style="margin-top:5px;" class="buttons"></div>').append($('<button>Apply</button>')
							.click(function(){
								// FIXME: hardcode here, should communicate to server, but not for now
								var _wiki = spm_wf_editor.view["table row"].wikiObj,
									_settings = $("#spm_wf_table_settings"),
									_hide = $("input._hide", _settings).attr("checked"),
									_header = $("input._header", _settings).val().trim(),
									_class = $("input._class", _settings).val().trim(),
									_header_class = $("input._header_class", _settings).val().trim();
	
								var p = _wiki.parent();
								_wiki.parents("table").first().attr("class", "spm_table " + _class);
								p.attr("class", _header_class)
									.text(_header)
									.append(_wiki)
									.css('display', (_header == '') ? 'none' : '');
									
								_wiki.html('{{Wom table begin|class=' + _class + '|header class=' + _header_class + '|header=' + _header + '|}}');
	
								$.fancybox.close();
								spm_wf_editor.js.notifySave();
							})
						).append($('<button>Hide this table</button>')
							.click(function(){
								// FIXME: hardcode here, should communicate to server, but not for now
								$("#spm_wf_table_settings input._class").val('spm_hidden_flag');
								$("#spm_wf_table_settings input._header_class").val('');
							})
						);
					});
				return tbl;
			});
	});
})(jQuery);