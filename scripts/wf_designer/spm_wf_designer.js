function ___verbose(clazz, msg) {
	var o = jQuery("#spm_wf_info");
	o.attr('class', clazz);
	jQuery("#spm_wf_info_msg", o).html(msg);
}
function info (msg) {
	___verbose('info', msg);
}
function success (msg) {
	___verbose('success', msg);
}
function warning (msg) {
	___verbose('warning', msg);
}
function error (msg) {
	___verbose('error', msg);
}

function commonHtmlEncode(value){
  return jQuery('<div/>').text(value).html();
}
function commonHtmlDecode(value){
  return jQuery('<div/>').html(value).text();
}

window.spm_wf_editor = { view: {}, datatype: {}, flds: [], extra: {}, event: {} };

(function($) {
	spm_wf_editor.js = {
		notifySave : function() {
			warning('Widget has been changed! Save before leave!');
			$(".spm_wf_editor_save").show();
			$("#spm_wf_editor_reset").show();
		},
		bindFieldEditEvent : function(jObj) {
			$('input, textarea', jObj).focus(function() {
				spm_wf_editor.editInFocus = true;
			});
			$('input, textarea', jObj).blur(function() {
				spm_wf_editor.editInFocus = false;
			});
		},
		getFieldId : function(id) {
			return "spm_wf_field_" + id.replace(/\W/g, '_');
		},
		loadFieldSettings : function(settings) {
			$("#spm_wf_field").val(settings.field);
			$("#spm_wf_label").val(settings.label);
			$("#spm_wf_editor_view").val(settings.view);
			$("#spm_wf_editor_datatype").val(settings.datatype);
		},
		resetFieldSettings : function() {
			$("#spm_wf_field").val('');
			$("#spm_wf_label").val('');
			$("#spm_wf_prop_description").val('');
			$("#spm_wf_prop_aclquery").val('');

			$("#fbpl .remarkps .fl .stylized").html( spm_wf_editor.js.__datatype_html );
			$("#fbpl .remarkps .fr .stylized").html( spm_wf_editor.js.__view_html );

			$("#spm_wf_editor_view").val('').change();
			$("#spm_wf_editor_datatype").val('').change();

			spm_wf_editor.js.loadWikiHtml();

			$(".spm_div_active").each(function() {
				$(this).removeClass('spm_div_active');
			});

			$("#fbpl").hide();
			$("#fbpl_overlay").hide();
			
			$("div.remarkps").scrollTop(0);
		},
		getFieldPropertyBaseDefinition : function() {
			return [
				// keep a default value in sem store
				$("#spm_wf_field_default").val(),
				$("#spm_wf_prop_description").val(),
				$("#spm_wf_prop_allows").val(),
				$("#spm_wf_prop_aclquery").val()
			];
		},
		updatePropertyDefinition : function(name, datatype, params) {
			var rsargs = [
				'updatePropertyDefinition',
				wgTitle,
				name,
				datatype];
			for(var i=0; i<params.length; ++i) {
				rsargs.push(params[i]);
			}
			var params2 = {
				'rsargs[]':rsargs,
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			var ret = false;
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params2,
				success: function(data, textStatus){
//					alert(data);
					ret = true;
				},
				async: false
			});
			return ret;
		},
		getUpdateFieldObject : function() {
			obj = document.createElement("div");
			obj.className = "spm_wf_field_placeholder";

			if(spm_wf_editor.activeLayout != null && spm_wf_editor.activeLayout.length != 0) {
				var column = $("ul.column", spm_wf_editor.activeLayout).first(),
					widgets = $("li.widget", column), 
					new_widget = $( '<li class="widget">' +
					"\n" + '<div class="widget-head">&nbsp;</div>' +
					"\n" + '<div class="widget-content"></div>' +
					"\n" + '</div>' +
					"\n" + '</li>' );
				if(widgets.length == 0) {
					column.append(new_widget);
				} else {
					new_widget.insertBefore(widgets.first());
				}
				spm_wf_editor.iNettuts.applyWidget(new_widget, spm_wf_editor.iNettuts.settings.widgetDefault);
				
				$("div.widget-content", new_widget).append(obj);
				
				return obj;
			}
			
			// just append to the end of spm_wf_main, leave a placeholder here
			$("#spm_wf_main").append(obj);
			return obj;
		},
		getObjectWikiTextContext : function(obj) {
			var before = '', current = '', after = '';
			var matched = false;
			$("#spm_wf_main .spm_wf_wiki").each(function(){
				var m = false;
				var jObj = $(this);
				jObj.parents().each(function(){
					if(this == obj) m=true;
					return !m;
				});
				if(m) {
					current += jObj.html();
					matched = true;
				} else {
					if(!matched) {
						before += jObj.html();
					} else {
						after += jObj.html();
					}
				}
			});
			return {before: before, current: current, after: after};
		},
		getFieldBaseDefinitionView : function() {
			return [
				$("#spm_wf_view_optional").attr("checked"),
				$("#spm_wf_view_multiple").attr("checked"),
				$("#spm_wf_view_editidx").val()
			];
		},
		getFieldBaseDefinitionDatatype : function() {
			return [
				$("#spm_wf_field_default").val()
			];
		},
		afterFieldUpdated : function() {
			$("#spm_wf_main .spm_wf_bound").each(function(){
				if(!this.id) {
					if( spm_wf_editor.js.registerField($(this)) ) {
						var _id = spm_wf_editor.js.getFieldId(
							spm_wf_editor.js.getFieldSettings( this ).field );
						this.id = _id;

//						spm_wf_editor.js.activeField( $( this ) );
					}
				}
			});
			return true;
		},
		updateField : function(
			obj, field, label,
			view, view_params,
			datatype, datatype_params,
			freetext/*, context*/) {

			var jObj = $(obj);

			var rsargs = [
				'getFieldHtml',
				wgTitle,
				field,
				label,
				freetext,
				datatype,
				view
//				,
//				context.before,
//				context.current,
//				context.after
			];
			for(var i=0; i<datatype_params.length; ++i) {
				rsargs.push(datatype_params[i]);
			}
			for(var i=0; i<view_params.length; ++i) {
				rsargs.push(view_params[i]);
			}
			var params = {
				'rsargs[]':rsargs,
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			var ret = false;
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				success: function(data, textStatus){
					// special cases
					jObj.next(".spm_wf_plain").remove();
					jObj.replaceWith(data);
					if(spm_wf_editor.view[view].js && spm_wf_editor.view[view].js.afterFieldUpdated){
						spm_wf_editor.view[view].js.afterFieldUpdated();
					} else {
						spm_wf_editor.js.afterFieldUpdated();
					}
					ret = true;
				},
				async: false
			});
			return ret;
		},
		getWidgetWiki : function(obj) {
			var wiki = '';
			var lastLF = true;
			$(".spm_wf_wiki", obj).each(function(){
				var w = commonHtmlDecode( $(this).html() );
				if( w != '' ) {
					wiki += (lastLF || w.charAt(0) == "\n" ? '' : "\n") + w;
					lastLF = ( w.charAt(w.length - 1) == "\n" );
				}

//				wiki += commonHtmlDecode( $(this).html() );
			});
			
			return wiki;
		},
		getTemplateWiki : function() {
			var wiki = '';
			// FIXME: hard code here
			$("#spm_wf_main .layout-content").each(function(){
				if( $(".spm_wf_wiki", this).length == 0 ) return;
				
				wiki += "\n" + '<div class="layout-content">';
				$("ul.column", this).each(function(){
					var clazz = "column column";
					var jo = $(this);
					if(jo.hasClass("column1")) clazz += "1";
					else if(jo.hasClass("column2")) clazz += "2";
					else if(jo.hasClass("column3")) clazz += "3";
					wiki += "\n" + '<ul class="' + clazz + '">';
					if( $(".spm_wf_wiki", this).length == 0 ) {
						wiki += '&nbsp;</ul>';
						return;
					}
					$("li.widget", this).each(function(){
						if( $(".spm_wf_wiki", this).length == 0 ) return;

						wiki += "\n" + '<li class="' + $(this).attr("class") + '" style="background-color:' + $(this).css('backgroundColor') + '">';
						$("div.widget-content", this).each(function(){
							if( $(".spm_wf_wiki", this).length == 0 ) return;

							wiki += "\n" + '<div class="widget-content">';
							wiki += "\n" + spm_wf_editor.js.getWidgetWiki(this).trim();
							wiki += "\n" + '</div>';
						});
						wiki += "\n" + '</li>';
					});
					wiki += "\n" + '</ul>';
				});
				wiki += "\n" + '<div style="clear: both;"></div>' + "\n" + '</div>';
			});
			
			return wiki;
		},
		updateWidgetWiki : function() {
			var wiki = spm_wf_editor.js.getTemplateWiki();

			var params = {
				'rsargs[]':['updateWidgetWiki',
					wgNamespaceNumber,
					wgTitle,
					wiki],
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				success: function(data, textStatus){
					success(data);
					$(".spm_wf_editor_save").hide();
					$("#spm_wf_editor_reset").hide();
				}
			});
		},
		refreshPropertyRevision : function() {
			// refresh revision ids
			var params = {
				'rsargs[]':[ 'refreshPropertyRevision', wgTitle ],
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			var ret = $.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				dataType: 'json', 
				success: function(data, textStatus){
					spm_wf_editor.flds = data;
				},
				async: false
			});
		},
		getFieldBaseSettingsView : function(params, offset) {
			var s = params[offset].split('|');
			var ret = { multiple : false, optional : false, editidx : '' };
			for(var i=0; i<s.length; ++i) {
				if(s[i] == 'multiple') ret.multiple = true;
				else if(s[i] == 'optional') ret.optional = true;
				else if(s[i].indexOf('editidx=') == 0) ret.editidx = s[i].substring('editidx='.length).trim();
			}
			return ret;
		},
		getFieldBaseSettingsViewLength : function() {
			return 1;
		},
		renderBaseFieldSettingsView : function( viewSettings ) {
			$("#spm_wf_view_optional").attr("checked", viewSettings.optional);
			$("#spm_wf_view_multiple").attr("checked", viewSettings.multiple);
			$("#spm_wf_view_editidx").val(viewSettings.editidx);
		},
		getFieldBaseSettingsDatatype : function(params, offset) {
			return {
				description: params[offset],
				default: params[offset + 1],
				possible_values: params[offset + 2],
				acl: params[offset + 3]
			};
		},
		getFieldBaseSettingsDatatypeLength : function() {
			return 4;
		},
		renderBaseFieldSettingsDatatype : function( datatypeSettings ) {
			$("#spm_wf_prop_description").val( commonHtmlDecode( datatypeSettings.description.replace( /\\n/g, " " ) ) );
			$("#spm_wf_field_default").val( commonHtmlDecode( datatypeSettings.default.replace( /\\n/g, "\n" ) ) );
			$("#spm_wf_prop_allows").val( commonHtmlDecode( datatypeSettings.possible_values.replace( /\\n/g, "\n" ) ) );
			$("#spm_wf_prop_aclquery").val( commonHtmlDecode( datatypeSettings.acl.replace( /\\n/g, "\n" ) ) );
		},
		getFieldSettings : function(obj) {
			obj = $(obj);
			if(! obj.hasClass('spm_wf_field_settings')) obj = $(".spm_wf_field_settings", obj);
			if(obj.length == 0) return null;
			
			var setting_str = obj.html();
			var params = setting_str.split("\n");
			var label = params[0].trim(),
				property = params[1].trim(),
				field = params[2].trim(),
				sample = params[3].trim(),
				view = params[4].trim(),
				datatype = params[5].trim();
			var offset = 6;
			var viewSettings = {};
			if( spm_wf_editor.view[view].js && spm_wf_editor.view[view].js.getFieldSettings ) {
				viewSettings = spm_wf_editor.view[view].js.getFieldSettings(params, offset);
				offset += spm_wf_editor.view[view].js.getFieldSettingsLength();
			} else {
				viewSettings = spm_wf_editor.js.getFieldBaseSettingsView(params, offset);
				offset += spm_wf_editor.js.getFieldBaseSettingsViewLength();
			}
			var datatypeSettings = {};
			if( spm_wf_editor.datatype[datatype].js && spm_wf_editor.datatype[datatype].js.getFieldSettings ) {
				datatypeSettings = spm_wf_editor.datatype[datatype].js.getFieldSettings(params, offset);
			} else {
				datatypeSettings = spm_wf_editor.js.getFieldBaseSettingsDatatype(params, offset);
			}
			return {
				label: label,
				property: property,
				field: field,
				sample: sample,
				view: view,
				datatype: datatype,
				viewSettings: viewSettings,
				datatypeSettings: datatypeSettings
			};
		},
		removeFieldBase : function( jObj, settings, uiOnly ) {
			// remove free editor
			jObj.next(".spm_wf_plain").remove();

			jObj.remove();
			$("#spm_wf_field").val('');
			$("#spm_wf_label").val('');
			$("#spm_wf_prop_description").val('');
			$("#spm_wf_prop_aclquery").val('');

			if( uiOnly ) return;

			// FIXME: removed property cannot roll back!
			var rsargs = ['removeProperty', wgTitle, settings.field ];
			var params = {
					'rsargs[]':rsargs,
					'action':'ajax',
					'rs':'spm_wf_EditorAccess'
				};
			var ret = false;
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				success: function(data, textStatus){
					ret = true;
				},
				async: false
			});
		},
		applyFieldUp : function(jObj, where) {
			$('<img class="spm_ui_addin_icon" src="' + wgScriptPath + '/extensions/SemanticPageMaker/skins/images/arrow_up_48.png"/>')
				.insertBefore($(":first", $(where).parent()))
				.click(function(e){
					var objs = null;
					if(jObj.hasClass("spm_wf_bound")) {
						objs = $(".spm_wf_bound");
					} else if(jObj.hasClass("spm_tablerow")) {
						objs = $(".spm_tablerow");
					} else {
						return;
					}

					// find item before
					var before = null;
					objs.each(function(){
						if(this == jObj[0]) return false;
						if($(".spm_wf_wiki", this).length > 0) before = this;
					});
					if(before == null) {
						e.stopPropagation();
						alert('Reached top!');
						return;
					}
					var html = jObj.clone().wrap('<div></div>').parent().html();
					if(jObj.next().hasClass("spm_wf_plain")) {
						html += jObj.next().clone().wrap('<div></div>').parent().html();
					}

					var settings = spm_wf_editor.js.getFieldSettings( jObj );
					if(spm_wf_editor.view[settings.view].js && spm_wf_editor.view[settings.view].js.removeField){
						spm_wf_editor.view[settings.view].js.removeField( jObj, settings, true );
					} else {
						spm_wf_editor.js.removeFieldBase( jObj, settings, true );
					}

					var jo = $(html).insertBefore(before).first();
					$(".spm_ui_addin_icon", jo).remove();
					spm_wf_editor.js.registerField( jo );
					spm_wf_editor.js.renderActiveHtml( jo );

					spm_wf_editor.js.notifySave();
					e.stopPropagation();
				});
		},
		applyFieldDown : function(jObj, where) {
			$('<img class="spm_ui_addin_icon" src="' + wgScriptPath + '/extensions/SemanticPageMaker/skins/images/arrow_down_48.png"/>')
				.insertBefore($(":first", $(where).parent()))
				.click(function(e){
					var objs = null;
					if(jObj.hasClass("spm_wf_bound")) {
						objs = $(".spm_wf_bound");
					} else if(jObj.hasClass("spm_tablerow")) {
						objs = $(".spm_tablerow");
					} else {
						return;
					}

					// find item before
					var after = null;
					var matched = false;
					objs.each(function(){
						if(matched && $(".spm_wf_wiki", this).length > 0) { after = this; return false; }
						if(this == jObj[0]) matched = true;
					});
					if(after == null) {
						e.stopPropagation();
						alert('Reached bottom!');
						return;
					}
					if($(after).next().hasClass("spm_wf_plain")) after = $(after).next();

					var html = jObj.clone().wrap('<div></div>').parent().html();
					if(jObj.next().hasClass("spm_wf_plain")) {
						html += jObj.next().clone().wrap('<div></div>').parent().html();
					}

					var settings = spm_wf_editor.js.getFieldSettings( jObj );
					if(spm_wf_editor.view[settings.view].js && spm_wf_editor.view[settings.view].js.removeField){
						spm_wf_editor.view[settings.view].js.removeField( jObj, settings, true );
					} else {
						spm_wf_editor.js.removeFieldBase( jObj, settings, true );
					}

					var jo = $(html).insertAfter(after).first();
					$(".spm_ui_addin_icon", jo).remove();
					spm_wf_editor.js.registerField( jo );
					spm_wf_editor.js.renderActiveHtml( jo );

					spm_wf_editor.js.notifySave();
					e.stopPropagation();
				});
		},
		applyFieldRemove : function(jObj, where) {
			$('<img class="spm_ui_addin_icon" src="' + wgScriptPath + '/extensions/SemanticPageMaker/skins/images/error.png"/>')
				.insertBefore($(":first", $(where).parent()))
				.click(function(){
					var answer = confirm("Delete this field?\nSome settings will lost, even if you do not save this widget.")
		            if (!answer){
		            	return;
		            }
					var settings = spm_wf_editor.js.getFieldSettings( jObj );
					if(spm_wf_editor.view[settings.view].js && spm_wf_editor.view[settings.view].js.removeField){
						spm_wf_editor.view[settings.view].js.removeField( jObj, settings );
					} else {
						spm_wf_editor.js.removeFieldBase( jObj, settings );
					}
					spm_wf_editor.js.notifySave();
				});
		},
		hintOnEmptyWiki : function(jObj) {
			if($(".spm_wf_wiki_body", jObj).html().trim() == "") {
				$(".spm_wf_wiki_body", jObj).html('<span style="color:#999999">+ extra content</span>');
			}
		},
		getWikiHtml : function(wiki, domElement) {
			var rsargs = [
				'getWikiHtml',
				spm_wf_editor.js.$smwgIQRunningNumber,
				wiki
			];
			var params = {
				'rsargs[]':rsargs,
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			var html = '';
			$.ajax({
				url: wgScript,
				data: params,
				success: function(data, textStatus){
					html = data;
					if(domElement) {
						spm_wf_editor.js.initResultFormatLoading();
						//get inline scripts
						var scripts = spm_wf_editor.js.getInitScripts(html);
						html = scripts.pop();
						//set the result html
						domElement.html(html);
						//execute inline scripts
						spm_wf_editor.js.appendScripts(domElement, scripts);
						spm_wf_editor.js.executeInitMethods();
					}
				},
				async: false
			});
			return html;
		},
		$$Onload : addOnloadHook,
		$smwgIQRunningNumber : 1,
		dynamicInitMethods : [],
		initResultFormatLoading : function(){
			$.fn.ready = spm_wf_editor.js.documentReady;
			addOnloadHook = spm_wf_editor.js.documentReady;
		},
		documentReady : function(someFunction){
			spm_wf_editor.js.addInitMethod(someFunction);
		},
		addInitMethod : function(func){
			if(!spm_wf_editor.js.isFunctionInArray(func, spm_wf_editor.js.dynamicInitMethods)){
				spm_wf_editor.js.dynamicInitMethods.push(func);
			}
		},
		getInitScripts : function(text){
			var scriptRegexp = new RegExp(/\<script[^\>]*\>[\s\S]*?\<\/script\>/gmi);
			var result = [];
			var noscript = text;
			var match;
			while(match = scriptRegexp.exec(text)){
				result.push(match[0]);
				noscript = noscript.replace(match[0], '');
			}
			result.push(noscript);
			return result;
		},
		isFunctionInArray : function(someFunction, arrayOfFunctions){
			var result = false;
			if(typeof someFunction === 'function' && arrayOfFunctions && arrayOfFunctions.length){
				$.each(arrayOfFunctions, function(key, value){
					if(value.toString() == someFunction.toString()){
						result = true;
						return false; //break the loop
					}
				});
			}
			return result;
		},
		appendScripts : function(domElement, scriptArray){
			for(var i = 0; i < scriptArray.length; i++){
				$(domElement).append(scriptArray[i]);
			}
		},
		executeInitMethods : function(){
			var initMethods = spm_wf_editor.js.dynamicInitMethods || [];
			for(var i = 0; i < initMethods.length; i++){
				try{
					//method 'smw_sortables_init' when applied more than once causes multiple sort headers to appear
					var method = initMethods[i];
					if((method.name == 'smw_sortables_init' || method.toString().indexOf('function smw_sortables_init') > -1)
						&& $('.sortheader').length > 0)	{
						continue;
					}
					method();
				} catch(x) {
					//exceptions are expected so just continue
					if(mw) mw.log('EXCEPTION: ' + x);
				}
			}
		},
		loadWikiHtml : function() {
			var jo = $(".spm_div_active textarea");
			if(jo.length > 0) {
				var plainObj = jo.parents(".spm_wf_plain");
				var html = spm_wf_editor.js.getWikiHtml( spm_wysiwyg.js.getSource( jo[0] ), plainObj );
				// plainObj.html(html);
				spm_wf_editor.js.hintOnEmptyWiki(plainObj);
				spm_wf_editor.js.notifySave();
			}
		},
		cancelWikiHtml : function() {
			var jo = $(".spm_div_active textarea");
			if(jo.length > 0) {
				var plainObj = jo.parents(".spm_wf_plain");
				var html = spm_wf_editor.js.getWikiHtml( commonHtmlDecode( $(".spm_wf_wiki", plainObj).html() ), plainObj );
				// plainObj.html(html);
				spm_wf_editor.js.hintOnEmptyWiki(plainObj);
			}
		},
		renderActiveHtml : function(jObj) {
			if( jObj.hasClass("spm_div_active") ) return;

			spm_wf_editor.js.resetFieldSettings();

			jObj.addClass('spm_div_active');
		},
		activeFreeTextEditor : function(jObj) {
			if( jObj.hasClass("spm_div_active") && $(".spm_div_active textarea").length > 0 ) return;

			spm_wf_editor.js.renderActiveHtml(jObj);
			$("#spm_wf_field").val('');

			var jo = $(".spm_wf_wiki", jObj);
			var text = commonHtmlDecode( jo.html() );
			$(".spm_wf_wiki_body", jObj).html('<textarea>' + text + '</textarea>');

			spm_wysiwyg.js.attachCKEditor( $("textarea", jObj)[0] );
		},
		applyFreeTextEditor : function(obj) {
			var jObj = $( obj );
			spm_wf_editor.js.hintOnEmptyWiki(jObj);

			jObj.mouseover(function(e) {
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
			}).click(function(e) {
				spm_wf_editor.js.activeFreeTextEditor(jObj);
				e.stopPropagation();
				e.preventDefault();
			});
		},
		activeField : function(jObj) {
			spm_wf_editor.activeLayout = jObj.parents(".layout");
			spm_wf_editor.js.renderActiveHtml(jObj);

			var settings = spm_wf_editor.js.getFieldSettings( jObj );
			spm_wf_editor.js.loadFieldSettings(settings);

			$("#spm_wf_field").attr('readonly', 'readonly');
			spm_wf_editor.js.editField(settings);
		},
		editField : function() {
			$("#spm_wf_editor_view").change();
			$("#spm_wf_editor_datatype").change();
			$("#fbpl_overlay").css({'height' : $(document).height()}).show();
			$("#fbpl").css({
				'top': $(document).scrollTop() + 20
			}).show('slow');
			$("#fbpl_accordion").accordion("activate", 0);
		},
		registerField : function(jObj) {
			if($(".spm_wf_field_settings", jObj).length == 0) {
				return false;
			}
			
			if( ! jObj.hasClass("spm_wf_bound") ) {
				spm_wf_editor.js.applyFieldUp(jObj, $(".spm_wf_field_settings", jObj));
				spm_wf_editor.js.applyFieldDown(jObj, $(".spm_wf_field_settings", jObj));
				spm_wf_editor.js.applyFieldRemove(jObj, $(".spm_wf_field_settings", jObj));
			}

			var jo = $(".spm_wf_plain", jObj);
			if( jo.length == 0) {
				jo = jObj.next(".spm_wf_plain");
			}
			spm_wf_editor.js.applyFreeTextEditor(jo);

			jObj.mouseover(function(e) {
				jObj.addClass('spm_div_hover');
				e.stopPropagation();
			}).mouseout(function(e) {
				jObj.removeClass('spm_div_hover');
				jObj.find('*').removeClass('spm_div_hover');
			}).click(function(e) {
				spm_wf_editor.js.activeField(jObj);
				e.stopPropagation();
				e.preventDefault();
			});

			return true;
		}
	};

	var applyDefault = function(id, type, list) {
		var ul = $("#" + list + " > ul").first();
		for(var key in spm_wf_editor[type]) {
			var ls = spm_wf_editor[type][key].list_str.split('|');
			var jul = ul, jli = null;
			for(var i=0;i<ls.length;++i) {
				if(ls[i] == '') continue;
				if(jli != null) {
					jul = $("> ul", jli).first();
					if(jul.length == 0) {
						jul = $('<ul></ul>');
						jli.append(jul);
					}
				}
				var id = "wfd_" + type.replace(/\W/g, '_') + "_" + ls[i].replace(/\W/g, '_'); 
				jli = $("> li#" + id, jul);
				if(jli.length > 0) continue;
				
				var item = document.createElement("li");
				item.id = id;
				jli = $(item);
				jli.html('<a href="#">' + ls[i] + '</a>');
				jul.append(jli);
			}

			var params = {
					'rsargs[]':[
						'getFieldDesignerHtml',
						type,
						key,
						wgTitle],
					'action':'ajax',
					'rs':'spm_wf_EditorAccess'
				};
			$.ajax({
				url: wgScript,
				data: params,
				success:function(data) {
					spm_wf_editor[type][key].html = data;
				},
				async: false
			});
		}
	};
	
	var keydownFunc = function(e){
		switch( e.which ) {
			case 13:
				// enter
				if( spm_wf_editor.editInFocus ) return;
				if( $("#fbpl:hidden").length == 0 ) {
					$("#spm_wf_updatefield").click();
					e.stopPropagation();
				}
				break;
			case 27:
				// esc
				if( $("#fbpl:hidden").length == 0 ) {
					spm_wf_editor.js.resetFieldSettings();
					e.stopPropagation();
				} else 
				if($(".spm_div_active textarea").length > 0) {
					spm_wf_editor.js.cancelWikiHtml();
					e.stopPropagation();
				}
				break;
		}
	};
	spm_wf_editor.event.keydownFunc = keydownFunc;
	
	var onDatatypeItemChosenFunc = function(item) {
		if($("ul", item).length > 0) return;
		var dto = $("#spm_wf_editor_datatype");
		var dt = item.text();
		if(dto.val() != dt) {
			dto.val(dt).change();
		}
	};
	
	var onViewItemChosenFunc = function(item) {
		if($("ul", item).length > 0) return;
		var vo = $("#spm_wf_editor_view");
		var v = item.text();
		if(vo.val() != v) {
			vo.val(v).change();
		}
	};

	$(document).ready(function() {
		spm_wf_editor.editInFocus = false;

		$("#fbpl_overlay").click(function(){
			$("#fbpl").hide("slow");
			$("#fbpl_overlay").hide();
		});

		spm_wf_editor.js.bindFieldEditEvent( $(document) );
		$(document).click(function(e){
			if($(".spm_div_active textarea").length > 0) {
				spm_wf_editor.js.loadWikiHtml();
			}
		}).keydown(keydownFunc);

		// fill in ids
		$("#spm_wf_main .spm_wf_bound").each(function(){
			if( spm_wf_editor.js.registerField($(this)) ) {
				var _id = spm_wf_editor.js.getFieldId(
					spm_wf_editor.js.getFieldSettings( this ).field );
				this.id = _id;
			}
		});
		$("#spm_wf_field").attr('readonly', 'readonly');

		$("#small_pl").click(function(){
			$("#fbpl").hide('slow');
			$("#fbpl_overlay").hide();
		});
		$("#spm_wf_cancel").click(function(){
			$("#fbpl").hide('slow');
			$("#fbpl_overlay").hide();
		});
		$("#fbpl").draggable({handle:'h3'});

		spm_wf_editor.js.__datatype_html = $("#fbpl .remarkps .fl .stylized").html();
		spm_wf_editor.js.__view_html = $("#fbpl .remarkps .fr .stylized").html();
		spm_wf_editor.js.refreshPropertyRevision();

		applyDefault('spm_wf_editor_widget', 'extra', 'spm_wf_editor_widget_content');
		// get valid field types
		applyDefault('spm_wf_editor_datatype', 'datatype', 'spm_wf_editor_datatype_content');
		applyDefault('spm_wf_editor_view', 'view', 'spm_wf_editor_view_content');

		$("#spm_wf_editor_remove_field").click(function(){
			var id = spm_wf_editor.js.getFieldId( $("#spm_wf_field").val().trim() );
			var jObj = $("#" + id);
			if(jObj.length == 0) {
				alert('Field does not exist.\nPlease change the field name.');
				return;
			}
			var settings = spm_wf_editor.js.getFieldSettings( jObj );
			if(spm_wf_editor.view[settings.view].js && spm_wf_editor.view[settings.view].js.removeField){
				spm_wf_editor.view[settings.view].js.removeField( jObj, settings );
			} else {
				spm_wf_editor.js.removeFieldBase( jObj, settings );
			}

		});

		$("#spm_wf_editor_datatype").change(function(){
			var datatype = $("#spm_wf_editor_datatype").val();
			if( !datatype ) return;

			if( !spm_wf_editor.datatype[datatype] ) {
				$("#spm_wf_editor_datatype").val(spm_wf_editor.activeDatatype);
				return;
			}
			spm_wf_editor.activeDatatype = datatype;
			
			var jObj = $("#fbpl .remarkps .fl .stylized");
			jObj.html(spm_wf_editor.datatype[datatype].html);

			if(spm_wf_editor.datatype[datatype].js && spm_wf_editor.datatype[datatype].js.bindEventBeforeShow){
				spm_wf_editor.datatype[datatype].js.bindEventBeforeShow();
			}
			spm_wf_editor.js.bindFieldEditEvent(jObj);

			var field = $("#spm_wf_field").val().trim();
			if(field == '') {
//			alert('Field is empty!\nPlease either select from widget view or input one.');
//			$("#spm_wf_editor_datatype select").val('');
				return;
			}
			jObj = $( "#" + spm_wf_editor.js.getFieldId( field ) );
			if(jObj.length > 0) {
				// A field with the same field name has been created
				var settings = spm_wf_editor.js.getFieldSettings( jObj );
				if(settings.datatype == datatype) {
					if(spm_wf_editor.datatype[settings.datatype].js && spm_wf_editor.datatype[settings.datatype].js.renderFieldSettings){
						spm_wf_editor.datatype[settings.datatype].js.renderFieldSettings( settings.datatypeSettings );
					} else {
						spm_wf_editor.js.renderBaseFieldSettingsDatatype( settings.datatypeSettings );
					}
				}
			}
		});
		$("#spm_wf_editor_view").change(function(){
			var view = $("#spm_wf_editor_view").val();
			if( !view ) return;

			if( !spm_wf_editor.view[view] ) {
				$("#spm_wf_editor_view").val(spm_wf_editor.activeView);
				return;
			}
			spm_wf_editor.activeView = view;

			var jObj = $("#fbpl .remarkps .fr .stylized");
			jObj.html(spm_wf_editor.view[view].html);

			if(spm_wf_editor.view[view].js && spm_wf_editor.view[view].js.bindEventBeforeShow){
				spm_wf_editor.view[view].js.bindEventBeforeShow();
			}
			spm_wf_editor.js.bindFieldEditEvent(jObj);

			var field = $("#spm_wf_field").val().trim();
			if(field == '') {
//			alert('Field is empty!\nPlease either select from widget view or input one.');
//			$("#spm_wf_editor_view select").val('');
				return;
			}

			jObj = $( "#" + spm_wf_editor.js.getFieldId( field ) );
			if(jObj.length > 0) {
				// A field with the same field name has been created
				var settings = spm_wf_editor.js.getFieldSettings( jObj );
				if(settings.view == view) {
					if(spm_wf_editor.view[settings.view].js && spm_wf_editor.view[settings.view].js.renderFieldSettings){
						spm_wf_editor.view[settings.view].js.renderFieldSettings( settings.viewSettings );
					} else {
						spm_wf_editor.js.renderBaseFieldSettingsView( settings.viewSettings );
					}
				}
			}
		});

		$(".spm_wf_editor_save").click(function(){
			spm_wf_editor.js.resetFieldSettings();

			spm_wf_editor.js.updateWidgetWiki();
			
			spm_wf_editor.js.refreshPropertyRevision();

			// FIXME: hard code here, to remove the category widget redlink
			var o = $("#ca-nstab-category_widget");
			o.removeClass('new');
			o = $("a", o);
			var href = o.attr("href");
			href = href.replace(/&?redlink=1/, '').replace(/&?action=wfedit/, '');
			o.attr("href", href);
		});

		$("#spm_wf_updatefield").click(function() {
			var label = $("#spm_wf_label").val().trim();
			var field = $("#spm_wf_field").val().trim();
			var datatype = $("#spm_wf_editor_datatype").val();
			var view = $("#spm_wf_editor_view").val();

			if(label == '') {
				alert('Please input label.');
				return;
			}
			if(field == '') {
				$("#spm_wf_field").val(label);
				field = label;
			}
			if(datatype == '') {
				alert('Please select data type.');
				return;
			}
			if(view == '') {
				alert('Please select view.');
				return;
			}
			var freetext = '';

			var id = spm_wf_editor.js.getFieldId(field);

			var jObj = $("#" + id);
			if(jObj.length > 0) {
				spm_wf_editor.activeLayout = jObj.parents(".layout");

				if( !$("#spm_wf_field").attr('readonly') ) {
					alert('The field name exists, please try another one.');
					return;
				}
				var plainObj = $(".spm_wf_plain", jObj);
				if(plainObj.length == 0) {
					plainObj = jObj.next(".spm_wf_plain");
				}
				freetext = $(".spm_wf_wiki", plainObj).html();
				if( freetext == null ) {
					freetext = '';
				} else {
					// FIXME: hard code, MW add an \n to <div> html tag
					if(freetext[0] == "\n") freetext = freetext.substring(1);
				} 

				// A field with the same field name has been created
				var settings = spm_wf_editor.js.getFieldSettings( jObj );
				if(settings.view != view ||
					(spm_wf_editor.view[settings.view].js &&
					spm_wf_editor.view[settings.view].js.updateInNew && spm_wf_editor.view[settings.view].js.updateInNew() )) {

					// just remove, will append to the end of document
					if(spm_wf_editor.view[settings.view].js && spm_wf_editor.view[settings.view].js.removeField){
						spm_wf_editor.view[settings.view].js.removeField( jObj, settings );
					} else {
						var widget = jObj.parents(spm_wf_editor.iNettuts.settings.widgetSelector);
						spm_wf_editor.js.removeFieldBase( jObj, settings );
						spm_wf_editor.iNettuts.removeWidgetUI( widget );
					}
					jObj = $("#" + id);
				}
			}

			var params = [];
			if(spm_wf_editor.datatype[datatype].js && spm_wf_editor.datatype[datatype].js.getFieldPropertyDefinition){
				params = spm_wf_editor.datatype[datatype].js.getFieldPropertyDefinition();
			} else {
				params = spm_wf_editor.js.getFieldPropertyBaseDefinition();
			}
			if( !spm_wf_editor.js.updatePropertyDefinition(
				field,
				datatype,
				params
			) ) {
				alert('Create/edit field property failed!');
				return;
			}

			var obj = null;
			// overwrite
			if(jObj.length == 0) {
				if(spm_wf_editor.view[view].js && spm_wf_editor.view[view].js.getUpdateFieldObject){
					obj = spm_wf_editor.view[view].js.getUpdateFieldObject();
				} else {
					obj = spm_wf_editor.js.getUpdateFieldObject();
				}
			} else {
				obj = jObj[0];
			}
//			var context = spm_wf_editor.js.getObjectWikiTextContext(obj);

			var view_params = [];
			if(spm_wf_editor.view[view].js && spm_wf_editor.view[view].js.getFieldDefinition){
				view_params = spm_wf_editor.view[view].js.getFieldDefinition();
			} else {
				view_params = spm_wf_editor.js.getFieldBaseDefinitionView();
			}
			var datatype_params = [];
			if(spm_wf_editor.datatype[datatype].js && spm_wf_editor.datatype[datatype].js.getFieldDefinition){
				datatype_params = spm_wf_editor.datatype[datatype].js.getFieldDefinition();
			} else {
				datatype_params = spm_wf_editor.js.getFieldBaseDefinitionDatatype();
			}
			if( !spm_wf_editor.js.updateField(
				obj,
				field,
				$("#spm_wf_label").val().trim(),
				view,
				view_params,
				datatype,
				datatype_params,
				freetext
//				,
//				context
			) ) {
				alert('Create/edit field view failed!');
				return;
			}
			spm_wf_editor.js.notifySave();

			spm_wf_editor.js.resetFieldSettings();
		});

		$("#spm_wf_editor_reset").click(function(){
			var rsargs = [ 'resetPropertyDefinition' ];
			for(var i=0; i<spm_wf_editor.flds.length; ++i) {
				rsargs.push("" + spm_wf_editor.flds[i].rid + "|" + spm_wf_editor.flds[i].name);
			}
			var params = {
				'rsargs[]':rsargs,
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			};
			$.ajax({
				type: "POST",
				url: wgScript,
				data: params,
				success: function(data, textStatus){
					alert("Reset : " + data);
				},
				async: false
			});
			location.reload();
		});
		
		
		// BUTTON
    	$(".fg-button").hover(
    		function(){ $(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
    		function(){ $(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
    	);
    	
    	// MENU
		$("#spm_wf_editor_datatype_list").menu({ 
			content: $("#spm_wf_editor_datatype_content").html(), 
			flyOut: true,
			keydownFunc: keydownFunc,
			chooseItemFunc: onDatatypeItemChosenFunc
		});
		$("#spm_wf_editor_view_list").menu({ 
			content: $("#spm_wf_editor_view_content").html(), 
			flyOut: true,
			keydownFunc: keydownFunc,
			chooseItemFunc: onViewItemChosenFunc
		});
		
		// layout
		$("#spm_wf_editor_add_layout").click(function() {
			var _o = $('<div class="layout"><div class="layout-head"></div><div class="layout-content"><ul class="column column1"></ul><div style="clear:both;"></div></div></div>');
			var last = $('div.layout').last()
			if(last.length > 0) {
				_o.insertAfter(last);
			} else {
				$("#spm_wf_main").append(_o);
			}
			spm_wf_editor.iNettuts.applyLayout(_o, spm_wf_editor.iNettuts.settings.widgetDefault);

			spm_wf_editor.js.notifySave();
		});
		
		$("#fbpl_accordion").accordion({
			autoHeight: false
		});
	});
})(jQuery);