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

// code from spm_wf_designer.js
window.spm_wf_editor = { view: {}, datatype: {} };
(function($) {
	spm_wf_editor.js = {
		getFieldId : function(id) {
			return "spm_wf_field_" + id.replace(/ /g, '_');
		},
		loadFieldSettings : function(settings) {
			var prop = settings.property;
			var o = $(".spm_div_src_active input").last();
			var oldprop = o.val();
			if( prop != oldprop ) {
				o.val( prop );
				warning('Connector has been changed! Save before leave!');
				$("#spm_wf_editor_save").show();
			}
		},
		getFieldSettings : function(obj) {
			var setting_str = $(".spm_wf_field_settings", obj).html();
			var s = setting_str.split("\n");
			return {
				label: s[0].trim(), 
				property: s[1].trim(),
				field: s[2].trim(),
				view: s[3].trim()
			};
		},
		registerField : function(jObj) {
			if($(".spm_wf_field_settings", jObj).length == 0) {
				return false;
			}
			
			var settings = spm_wf_editor.js.getFieldSettings( jObj );
			jObj.mouseover(function(e) {
				if($(".spm_div_src_active").length == 0) return false;
				(function(nod) {
					var node = $(nod);
					node.addClass('spm_div_hover');
					
					var cld = node.children();
					for(var i=0;i<cld.length;++i) {
						arguments.callee(cld[i]);
					}
				})(e);
				jObj.addClass('spm_div_hover');
				e.stopPropagation();
			}).mouseout(function(e) {
				if($(".spm_div_src_active").length == 0) return false;
				jObj.removeClass('spm_div_hover');
				jObj.find('*').removeClass('spm_div_hover');
			}).click(function() {
				if($(".spm_div_src_active").length == 0) return false;
				$(".spm_div_active").each(function() {
					$(this).removeClass('spm_div_active');
				});
				jObj.addClass('spm_div_active');
				spm_wf_editor.js.loadFieldSettings(settings);
			});
			
			return true;
		}
	};
})(jQuery);

window.spm_wf_conn_editor = {};
(function($) {
	spm_wf_conn_editor.js = {
			resetConnectorField : function() {
				var o = $(".spm_div_src_active");
				if(o.lengthe == 0) return;
				var input = $("input", o);
				input.replaceWith('<span>' + input.val() + '</span>');
				o.removeClass('spm_div_src_active');
			},
			resetConnector : function() {
				// reset connector field
				spm_wf_conn_editor.js.resetConnectorField();
				// reset connector
				var o = $(".spm_div_conn_active");
				if(o.lengthe == 0) return;
				var textarea = $("div.spm_wf_exp_exp textarea", o);
				if(textarea.length > 0) {
					textarea.replaceWith('<span>' + commonHtmlEncode(textarea.val()).replace(/\n/g, "<br/>\n") + '</span>');
				}
				o.removeClass('spm_div_conn_active');
			},
			mouseHover : function(jObj, className) {
				jObj.mouseover(function(e) {
					(function(nod) {
						var node = $(nod);
						node.addClass(className);
						
						var cld = node.children();
						for(var i=0;i<cld.length;++i) {
							arguments.callee(cld[i]);
						}
					})(e);
					jObj.addClass(className);
					e.stopPropagation();
				}).mouseout(function(e) {
					jObj.removeClass(className);
					jObj.find('*').removeClass(className);
				});
			},
			activeConnectoer : function(jObj) {
				var ret = false;
				$(".spm_div_conn_active").each(function() {
					if(jObj[0] == this) {
						ret = true;
						return false;
					}
				});
				if(ret) return;
				spm_wf_conn_editor.js.resetConnector();
				
				// active
				var span = $("div.spm_wf_exp_exp span", jObj).each(function(){
					var o = $(this);
					var vo = $('<textarea>' + commonHtmlDecode(o.html()) + '</textarea>');
					o.replaceWith(vo);
					vo.change(function(){
						warning('Connector has been changed! Save before leave!');
						$("#spm_wf_editor_save").show();
					})
				});

				$(".spm_div_active").each(function() {
					$(this).removeClass('spm_div_active');
				});
				$(".spm_div_conn_active").each(function() {
					$(this).removeClass('spm_div_conn_active');
				});
				jObj.addClass('spm_div_conn_active');
			},
			addRemoveIcon : function(jObj, click_func) {
				$('<img class="spm_remove_icon2" src="' + wgScriptPath + '/extensions/SemanticPageMaker/skins/images/error.png"/>')
					.insertBefore(jObj)
					.click(function(){
						if( click_func() ) {
							$(this).remove();
							warning('Connector has been changed! Save before leave!');
							$("#spm_wf_editor_save").show();
						}
					});
			},
			registerConnector : function(jObj) {
				spm_wf_conn_editor.js.addRemoveIcon(jObj, function() {
					var answer = confirm("Delete this field?")
		            if (!answer){
		            	return;
		            }
					jObj.remove();
					return true;
				});
				spm_wf_conn_editor.js.mouseHover(jObj, 'spm_div_hover');
				jObj.click(function() {
					spm_wf_conn_editor.js.activeConnectoer(jObj);
				});
				$('<a class="spm_wf_exphelp">edit helper</a> ]').insertBefore($(".spm_wf_exp_exp", jObj)).click(function(){
					$("#fbpl_overlay").css({'height' : $(document).height()}).show();
					$("#fbpl").css({
						'top': $(document).scrollTop() + 20
					}).show('slow');
					var val = $("div.spm_wf_exp_exp span", jObj);
					if(val.length == 0) {
						val = $("div.spm_wf_exp_exp textarea", jObj).val();
					} else {
						val = commonHtmlDecode(val.html())
					}
					$("#spm_wf_expression_txt").val(val.trim());
				});
			},
			activeConnectorField : function(jObj) {
				var ret = false;
				$(".spm_div_src_active").each(function() {
					if(jObj[0] == this) {
						ret = true;
						return false;
					}
				});
				if(ret) return;
				// reset connector field
				spm_wf_conn_editor.js.resetConnectorField();
				
				// active
				var span = $("span", jObj).each(function(){
					var o = $(this);
					o.replaceWith('<input type="text" readonly="readonly" value="' + o.html().replace(/"/g, '\"') + '"/>');
				});
				$(".spm_div_active").each(function() {
					$(this).removeClass('spm_div_active');
				});
				$(".spm_div_src_active").each(function() {
					$(this).removeClass('spm_div_src_active');
				});
				jObj.addClass('spm_div_src_active');
			},
			registerConnectorField : function(jObj) {
				if(jObj.hasClass("spm_wf_exp_src")) {
					spm_wf_conn_editor.js.addRemoveIcon(jObj, function() {
						if($(".spm_wf_exp_src").length == 1) {
							alert('This the last source field, cannot be removed.\nOr you may remove the connector.')
							return false;
						}
						var answer = confirm("Delete this field?")
			            if (!answer){
			            	return;
			            }
						jObj.remove();
						return true;
					});
				}
				spm_wf_conn_editor.js.mouseHover(jObj, 'spm_div_hover');
				jObj.click(function() {
					spm_wf_conn_editor.js.activeConnectorField(jObj);
				});
			},
			bindEvents : function() {
				$(".spm_wf_exp").each(function(){
					spm_wf_conn_editor.js.registerConnector($(this));
				});
				$(".spm_wf_exp_target").each(function(){
					spm_wf_conn_editor.js.registerConnectorField($(this));
				});
				$(".spm_wf_exp_src").each(function(){
					spm_wf_conn_editor.js.registerConnectorField($(this));
				});
			},
			resetConnectors : function() {
				var rsargs = ['getWidgetConnectorHtml', wgNamespaceNumber, wgTitle];
				$.ajax({
					type: "POST",
					url: wgScript,
					data: {
						'rsargs[]':rsargs,
						'action':'ajax',
						'rs':'spm_wf_EditorAccess'
					},
					success: function(data, textStatus){
						$("#spm_wf_exps").html('');
						$("#spm_wf_exps").append(data);
					},
					async: false
				});
			},
			loadParentConnectors : function() {
				var rsargs = ['getParentWidgetConnectorHtml', wgNamespaceNumber, wgTitle];
				$.ajax({
					type: "POST",
					url: wgScript,
					data: {
						'rsargs[]':rsargs,
						'action':'ajax',
						'rs':'spm_wf_EditorAccess'
					},
					success: function(data, textStatus){
						$("#spm_wf_exps").html('');
						$("#spm_wf_exps").append(data);
					},
					async: false
				});
			},
			updateWidgetConnectors : function() {
				spm_wf_conn_editor.js.resetConnector();

				var rsargs = ['updateWidgetConnectors', wgNamespaceNumber, wgTitle];
				$("#spm_wf_exps .spm_wf_exp").each(function(){
					var con = $(".spm_wf_exp_target span", this).html() + '|';
					$(".spm_wf_exp_srcs .spm_wf_exp_src span", this).each(function(){
						con += $(this).html() + '|';
					});
					con += '<nowiki>' + commonHtmlDecode( $(".spm_wf_exp_exp span", this).html() ).trim() + '</nowiki>';
					rsargs.push(con);
				});
				$.ajax({
					type: "POST",
					url: wgScript,
					data: {
						'rsargs[]':rsargs,
						'action':'ajax',
						'rs':'spm_wf_EditorAccess'
					},
					success: function(data, textStatus){
						success(data);
						$("#spm_wf_editor_save").hide();
//						alert(data);
					}
				});
			}
	};

	var __parserfunctions = {
		'if': { label: 'if', exp: '{{#if: test_string | value_if_true | value_if_false }}' },
		'ifeq': { label: 'ifeq', exp: '{{#ifeq: string_1 | string_2 | value_if_identical | value_if_different }}' },
		'switch': { label: 'switch', exp: "{{#switch: comparison_string\n | case = result\n | default_result\n}}" }
	};
	$(document).ready(function() {
		var conn_html = '';
		var src_html = '';
		$.ajax({
			type: "POST",
			url: wgScript,
			data: {
				'rsargs[]':['getConnectorHtmlEmpty'],
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			},
			success: function(data, textStatus){
				conn_html = data;
			},
			async: false
		});
		$.ajax({
			type: "POST",
			url: wgScript,
			data: {
				'rsargs[]':['getConnectorSrcHtmlEmpty'],
				'action':'ajax',
				'rs':'spm_wf_EditorAccess'
			},
			success: function(data, textStatus){
				src_html = data;
			},
			async: false
		});

		// fill in ids
		$("#spm_wf_main .spm_wf_bound").each(function(){
			if( spm_wf_editor.js.registerField($(this)) ) {
				var _id = spm_wf_editor.js.getFieldId(
					spm_wf_editor.js.getFieldSettings( this ).field );
				this.id = _id;
			}
		});

		spm_wf_conn_editor.js.bindEvents();

		$("#spm_wf_conn_remove").click(function(){
			$(".spm_div_conn_active").remove();
		});
		$("#spm_wf_conn_remove_src").click(function(){
			$(".spm_div_src_active").remove();
		});
		$("#spm_wf_conn_reset").click(function(){
			spm_wf_conn_editor.js.resetConnectors();
			spm_wf_conn_editor.js.bindEvents();
			warning('Connector has been changed! Save before leave!');
			$("#spm_wf_editor_save").show();
		});
		$("#spm_wf_conn_reloadp").click(function(){
			spm_wf_conn_editor.js.loadParentConnectors();
			spm_wf_conn_editor.js.bindEvents();
			warning('Connector has been changed! Save before leave!');
			$("#spm_wf_editor_save").show();
		});
		$("#spm_wf_conn_add").click(function(){
			var jObj = $("#spm_wf_exps").append(conn_html);
			jObj = $(".spm_wf_exp", jObj).last();
			spm_wf_conn_editor.js.registerConnector(jObj);
			spm_wf_conn_editor.js.activeConnectoer(jObj);
			$(".spm_wf_exp_target", jObj).last().each(function(){
				var o = $(this);
				spm_wf_conn_editor.js.registerConnectorField(o);
				spm_wf_conn_editor.js.activeConnectorField(o);
			});
			$(".spm_wf_exp_src", jObj).each(function(){
				spm_wf_conn_editor.js.registerConnectorField( $(this) );
			});
		});
		$("#spm_wf_conn_add_src").click(function(){
			var o = $(".spm_div_conn_active .spm_wf_exp_srcs").append(src_html);
			o = $(".spm_wf_exp_src", o).last();
			spm_wf_conn_editor.js.registerConnectorField(o);
			spm_wf_conn_editor.js.activeConnectorField(o);
		});

		$("#spm_wf_editor_save").click(function(){
			spm_wf_conn_editor.js.updateWidgetConnectors();
		});

		$("#fbpl_accordion").accordion({
			autoHeight: false
		});
		$("#small_pl").click(function(){
			$("#fbpl").hide('slow');
			$("#fbpl_overlay").hide();
		});
		$("#spm_wf_cancel").click(function(){
			$("#fbpl").hide('slow');
			$("#fbpl_overlay").hide();
		});
		$("#fbpl_overlay").click(function(){
			$("#fbpl").hide("slow");
			$("#fbpl_overlay").hide();
		});
		$("#fbpl").draggable({handle:'h3'});
		$("#spm_wf_update").click(function(){
			$(".spm_wf_exp_exp textarea").val($("#spm_wf_expression_txt").val());
			$(".spm_wf_exp_exp textarea").change();
			
			$("#fbpl").hide('slow');
			$("#fbpl_overlay").hide();
		});

		$(document).keydown(function(e){
			switch( e.which ) {
//				case 13:
//					// enter
//					if( $("#fbpl:hidden").length == 0 ) {
//						$("#spm_wf_update").click();
//						e.stopPropagation();
//					}
//					break;
				case 27:
					// esc
					if( $("#fbpl:hidden").length == 0 ) {
						$("#spm_wf_cancel").click();
						e.stopPropagation();
					}
			}
		});
		
		for(var key in __parserfunctions) {
			$("#spm_wf_exp_pfs").append('<option value="' + key + '">' + __parserfunctions[key].label + '</option>');
		}

		var isHostProperty = function(object, property) {
			return typeof(object[property]) != "undefined";
		},
		isHostMethod = function (object, property) {
			var t = typeof object[property];
			return t === "function" || (!!(t == "object" && object[property])) || t == "unknown";
		},
		isHostObject = function (object, property) {
			return !!(typeof(object[property]) == "object" && object[property]);
		},
		getBody = function() {
			return isHostObject(document, "body") ? document.body : document.getElementsByTagName("body")[0];
		};
	    var makeSelection = function(el, start, end) {
	        return {
	            start: start,
	            end: end,
	            length: end - start,
	            text: el.value.slice(start, end)
	        };
	    },
	    adjustOffsets = function (el, start, end) {
	        if (start < 0) {
	            start += el.value.length;
	        }
	        if (typeof end == "undefined") {
	            end = start;
	        }
	        if (end < 0) {
	            end += el.value.length;
	        }
	        return { start: start, end: end };
	    };

        var testTextArea = document.createElement("textarea");
        getBody().appendChild(testTextArea);
        var getSelection, setSelection;
        if (isHostProperty(testTextArea, "selectionStart") && isHostProperty(testTextArea, "selectionEnd")) {
            getSelection = function(el) {
                var start = el.selectionStart, end = el.selectionEnd;
                return makeSelection(el, start, end);
            };
            setSelection = function(el, startOffset, endOffset) {
                var offsets = adjustOffsets(el, startOffset, endOffset);
                el.selectionStart = offsets.start;
                el.selectionEnd = offsets.end;
            };
        } else if (isHostMethod(testTextArea, "createTextRange") && isHostObject(document, "selection") &&
                   isHostMethod(document.selection, "createRange")) {
            getSelection = function(el) {
                var start = 0, end = 0, normalizedValue, textInputRange, len, endRange;
                var range = document.selection.createRange();

                if (range && range.parentElement() == el) {
                    len = el.value.length;

                    normalizedValue = el.value.replace(/\r\n/g, "\n");
                    textInputRange = el.createTextRange();
                    textInputRange.moveToBookmark(range.getBookmark());
                    endRange = el.createTextRange();
                    endRange.collapse(false);
                    if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                        start = end = len;
                    } else {
                        start = -textInputRange.moveStart("character", -len);
                        start += normalizedValue.slice(0, start).split("\n").length - 1;
                        if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
                            end = len;
                        } else {
                            end = -textInputRange.moveEnd("character", -len);
                            end += normalizedValue.slice(0, end).split("\n").length - 1;
                        }
                    }
                }

                return makeSelection(el, start, end);
            };
            var offsetToRangeCharacterMove = function(el, offset) {
                return offset - (el.value.slice(0, offset).split("\r\n").length - 1);
            };

            setSelection = function(el, startOffset, endOffset) {
                var offsets = adjustOffsets(el, startOffset, endOffset);
                var range = el.createTextRange();
                var startCharMove = offsetToRangeCharacterMove(el, offsets.start);
                range.collapse(true);
                if (offsets.start == offsets.end) {
                    range.move("character", startCharMove);
                } else {
                    range.moveEnd("character", offsetToRangeCharacterMove(el, offsets.end));
                    range.moveStart("character", startCharMove);
                }
                range.select();
            };
        }
        getBody().removeChild(testTextArea);
        var replaceSelectedText = function(el, text) {
            var sel = getSelection(el), val = el.value;
            el.value = val.slice(0, sel.start) + text + val.slice(sel.end);
            var caretIndex = sel.start + text.length;
            setSelection(el, caretIndex, caretIndex);
        };
        
        
        
		$("#spm_wf_exp_apf").click(function(){
			var key = $("#spm_wf_exp_pfs").val();
			$("#fbpl_accordion").accordion("activate", 0);
			var txt = $("#spm_wf_expression_txt");
			txt.focus();
			replaceSelectedText(txt[0], __parserfunctions[key].exp);
		});
		$("#spm_wf_exp_aenum").click(function(){
			var key = $("#spm_wf_exp_allows").val().split("\n"), val = "";
			for(var i=0;i<key.length;++i){
				if(key[i].trim() != "") {
					val = val + "[[allows value::" + key[i].trim() + "]]\n";
				}
			}
			$("#spm_wf_exp_allows").val('');
			if(val == "") return;
			$("#fbpl_accordion").accordion("activate", 0);
			var txt = $("#spm_wf_expression_txt");
			txt.focus();
			replaceSelectedText(txt[0], val);
		});
		$("#spm_wf_exp_aquery").click(function(){
			var key = $("#spm_wf_exp_query").val().split("\n"), val = "";
			for(var i=0;i<key.length;++i){
				if(key[i].trim() != "") {
					val = val + key[i].trim().replace(/\|/g, "{{!}}") + "|\n";
				}
			}
			$("#spm_wf_exp_query").val('');
			if(val == "") return;
			$("#fbpl_accordion").accordion("activate", 0);
			var txt = $("#spm_wf_expression_txt");
			txt.focus();
			replaceSelectedText(txt[0], "{{#wfallowsvalue:" + val + "}}");
		});

			$("#spm_wf_exp_qi").fancybox({
				'overlayShow'	: true,
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
				'titlePosition' : 'inside',
				'width'		  	: '90%',
				'height'	  	: '90%',
				'autoScale'		: false,
				'type'		  	: 'iframe',
				'title'         : 'Apply query',
				'onStart'       : function(){
					this.href = wgScript + '/Special:SPMQueryInterface';
				}
			});
			$( "#wf_wd_exp_tabs" ).tabs();
	});
	// use the same name of wf designer page query
	spm_wf_editor.datatype = { page: { js: { appendPossibleValuesQuery : function( query ) {
			var queries = $("#spm_wf_exp_query").val();
			query = commonHtmlDecode( query );

			var reg = /[[\]|]/g;
			reg.lastIndex = 0;
			
			var result = reg.exec( query );
			if(result == null) return;
			
			var start = stop = result.index, bracket = 0;
			
			while(result != null && (result[0] != '|' || bracket > 0)) {
				if(result[0] == '[') ++ bracket;
				else if(result[0] == ']') -- bracket;
				stop = result.index;
				
				result = reg.exec( query );
			}
			var printout = query.substring(stop + 1);
			query = query.substring(start, stop + 1);
			
			// get the first printout, in case query condition is an 'instance' or 'mainlabel=-'
			var prs = printout.split('|'), pr = '';
			for(var i=0;i<prs.length;++i) {
				if(prs[i].trim().charAt(0) == '?') {
					pr = prs[i].trim();
					break;
				}
			}
			if(pr != '') query = query + "|" + pr;
			if((/\|\s*mainlabel\s*=\s*-\s*[\|}]/i.exec(printout) != null)) {
				query = query + "|mainlabel=-";
			}
			
			queries = queries.trim();
			if(queries != '') queries += "\n";
			queries += query.replace(/\n/g, '').replace('|', '{{!}}');
			$("#spm_wf_exp_query").val( queries );
		}
	} } };
})(jQuery);