window.spm_wf_field = { formUrl: "", js: {}, data: [] };
(function($) {
	var autocompleteOnAllChars = true;
	/* extending jquery functions for custom highlighting */
	$.ui.autocomplete.prototype._renderItem = function( ul, item) {
		var re = new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + this.term.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi");
		var loc = item.label.search(re);
		if (loc >= 0) {
			var t = item.label.substr(0, loc) + '<strong>' + item.label.substr(loc, this.term.length) + '</strong>' + item.label.substr(loc + this.term.length);
		} else {
			var t = item.label;
		}
		return $( "<li></li>" ).data( "item.autocomplete", item )
			.append( " <a>" + t + "</a>" )
			.appendTo( ul );
	};
	/* extending jquery functions */
	$.extend( $.ui.autocomplete, {
		filter: function(array, term) {
			if ( autocompleteOnAllChars ) {
				var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), "i" );
			} else {
				var matcher = new RegExp("\\b" + $.ui.autocomplete.escapeRegex(term), "i" );
			}
			return $.grep( array, function(value) {
				return matcher.test( value.label || value.value || value );
			});
		}
	});

	spm_wf_field.js = {
		saveSubPage : function(jObj, data) {
			var args = {
				'submit' : 'Save',
				'spm_w' : data.widget,
				'spm_t' : data.page
				};
			$(".spm_wf_fld [name]", jObj).each(function(){
				args[$(this).attr('name')] = $(this).val();
			});

			$.ajax({
				type: "POST",
				url: spm_wf_field.formUrl,
				data: args,
				success: function(data, textStatus){
					jObj.replaceWith('<div>Saved to a sub page: ' + data.page + '</div>');
				},
				async: false
			});
		},
		bindAutocomplete : function(obj, values, delimiter) {
			if(values == null) return;
			var jObj = $(obj);
			/* delimiter != '' means multiple autocomplete */
			if (delimiter != null) {
				jObj.autocomplete({
					minLength: 0,
					source: function(request, response) {
						response($.ui.autocomplete.filter(values, request.term.split(delimiter+" ").pop()));
					},
					open: function(event, ui) {
						jObj[0].ac_on = true;
					},
					close: function(event, ui) {
						jObj[0].ac_on = false;
						jObj.change();
					},
					focus: function() {
						// prevent value inserted on focus
						return false;
					},
					select: function(event, ui) {
						var terms = this.value.split(delimiter + " ");
						// remove the current input
						terms.pop();
						// add the selected item
						terms.push( ui.item.value );
						// add placeholder to get the comma-and-space at the end
						terms.push("");
						this.value = terms.join(delimiter + " ");
						return false;
					}
				}).click(function(){
					$(this).autocomplete("search", jObj.val());
				});
			} else {
				jObj.autocomplete({
					source:values,
					minLength: 0,
					open: function(event, ui) {
						jObj[0].ac_on = true;
					},
					close: function(event, ui) {
						jObj[0].ac_on = false;
						jObj.change();
					}
				}).click(function(){
					$(this).autocomplete("search", jObj.val());
				});
			}
		},
		bindAutocompleteOnRange : function(obj, type, range, delimiter) {
			// souce from SemanticForms extension, libs/SemanticForms.js
			var url = wgScriptPath + '/api.php';
			
			if (type == 'property')
				url += "?action=sfautocomplete&format=json&property=" + range;
			else if (type == 'relation')
				url += "?action=sfautocomplete&format=json&relation=" + range;
			else if (type == 'attribute')
				url += "?action=sfautocomplete&format=json&attribute=" + range;
			else if (type == 'category')
				url += "?action=sfautocomplete&format=json&category=" + range;
			else if (type == 'namespace')
				url += "?action=sfautocomplete&format=json&namespace=" + range;
			else if (type == 'external_url')
				url += "?action=sfautocomplete&format=json&external_url=" + range;
			
			var jObj = $(obj);
			
			if (delimiter != null) {
				jObj.autocomplete({
					source: function(request, response) {
						$.getJSON(
							url, 
							{ substr: request.term.split(delimiter + " ").pop() }, 
							function( data ) {
								if(data.length == 0) {
									response();
									return;
								}
								response( 
									$.map( 
										data.sfautocomplete, 
										function(item) { return { value: item.title }; } 
									) 
								);
							}
						);
					},
					search: function() {
						// custom minLength
						var term = this.value.split(delimiter + " ").pop();
						if (term.length < 1) { return false; }
					},
					focus: function() {
						// prevent value inserted on focus
						return false;
					},
					select: function(event, ui) {
						var terms = this.value.split(delimiter + " ");
						// remove the current input
						terms.pop();
						// add the selected item
						terms.push( ui.item.value );
						// add placeholder to get the comma-and-space at the end
						terms.push("");
						this.value = terms.join(delimiter + " ");
						return false;
					},
					open: function(event, ui) {
						jObj[0].ac_on = true;
					},
					close: function(event, ui) {
						jObj[0].ac_on = false;
						jObj.change();
					}
				});
			} else {
				jObj.autocomplete({
					minLength: 1,
					source: function(request, response) {
						$.ajax({
							url: url,
							dataType: "json",
							data: { substr: request.term },
							success: function( data ) {
								if(data.length == 0) {
									response();
									return;
								}
								response(
									$.map(
										data.sfautocomplete, 
										function(item) { return { value: item.title }; }
									)
								);
							}
						});
					},
					open: function(event, ui) {
						jObj[0].ac_on = true;
						$(this).removeClass("ui-corner-all").addClass("ui-corner-top");
					},
					close: function() {
						$(this).removeClass("ui-corner-top").addClass("ui-corner-all");
						jObj[0].ac_on = false;
						jObj.change();
					}
				});
			}
        },
		bindValidation : function(obj, func, err_html) {
			var jObj = $(obj);
			jObj.change(function(){
				var p = jObj.parents(".spm_wf_fld").first();
				$(".spm_wf_fld_hint", p).remove();
				p.parent("td").removeClass('spm_wf_err');
				if(!func(jObj.val())) {
					p.append('<div class="spm_wf_fld_hint">' + err_html + '</div>');
					p.parent("td").addClass('spm_wf_err');
				}
			});
			jObj.change();
		},
		bindUploadable : function(obj, link_id) {
			$("#" + link_id).fancybox({
				'width'		: '90%',
				'height'	: '90%',
				'autoScale'	: false,
				'transitionIn'	: 'none',
				'transitionOut'	: 'none',
				'type'		: 'iframe',
				'overlayColor'  : '#222',
				'overlayOpacity' : '0.8'
			});
		},
		bindDatepicker : function(obj) {
			var jObj = $(obj);
			// FIXME: multiple value not supported
			if(jObj.hasClass('spm_wf_multi_val')) {}
			
			jObj.datepicker({
					dateFormat: 'yy/mm/dd',
					changeMonth: true,
					changeYear: true,
					duration: '',
					showTime: true,
					constrainInput: false
			});
		},
		bindTimepicker : function(obj) {
			var jObj = $(obj);
			// FIXME: multiple value not supported
			if(jObj.hasClass('spm_wf_multi_val')) {}
			
			jObj.datetimepicker({dateFormat: 'yy/mm/dd'});
		},
		bindFields : function() {
			var fields = spm_wf_field.data;
			for(var i=fields.length-1;i>=0;--i) {
				var field = fields[i];
				switch(field.type) {
					case 'ac':
	spm_wf_field.js.bindAutocomplete($("form")[0][field.name], field.params[0], field.params[1]);
						break;
					case 'date':
	spm_wf_field.js.bindDatepicker($("form")[0][field.name]);
						break;
					case 'time':
	spm_wf_field.js.bindTimepicker($("form")[0][field.name]);
						break;
					case 'upload':
	spm_wf_field.js.bindUploadable($("form")[0][field.name], field.params[0]);
						break;
					case 'ac_range':
	spm_wf_field.js.bindAutocompleteOnRange($("form")[0][field.name], 
		field.params[0], field.params[1], field.params[2]);
						break;
					case 'validate':
	spm_wf_field.js.bindValidation($("form")[0][field.name], field.params[0], field.params[1]);
						break;
				}
			}
		}
	};
	
	$(document).ready(function() {
		$('input[type=submit]').click(function() {
			$(".spm_wf_err").removeClass('spm_wf_err');
			var err = false;
			$(".spm_wf_fld").each(function(){
				var jo = $(".spm_wf_val", this);
				if(!jo.hasClass('spm_wf_optional_val') && jo.val().trim() == '') {
					$(this).parent("td").addClass('spm_wf_err');
					err = true;
				}
			});
			if($(".spm_wf_fld_hint").length > 0) {
				$(".spm_wf_fld_hint").parent().parent("td").addClass('spm_wf_err');
				err = true;
			}
			if(err) {
				alert('Invalid field value(s).\nPlease check.');
				return false;
			}
			// do not use the first form
			for(var i=spm_wf_connector.data.length-1; i>0;--i) {
				var jObj = $("#spm_wf_form_group_" + i);
				spm_wf_field.js.saveSubPage(jObj, spm_wf_connector.data[i]);
			}
		});
		
		$(".popupIcon").tooltip({
			effect: 'slide'
		}).dynamic({ bottom: { direction: 'down', bounce: true } });
		$(".tooltip").each(function(){
			$("img", this).css("height", "50px"); 
		});
		
		spm_wf_field.js.bindFields();
	});
})(jQuery);