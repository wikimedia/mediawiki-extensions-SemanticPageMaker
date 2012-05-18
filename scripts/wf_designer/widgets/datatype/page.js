(function($){
	spm_wf_editor.datatype.page.js = {
		getFieldSettings : function(params, offset) {
			var ret = spm_wf_editor.js.getFieldBaseSettingsDatatype(params, offset);
			offset += spm_wf_editor.js.getFieldBaseSettingsDatatypeLength();
			ret.query = params[offset ++];
			ret.range = params[offset ++];
			return ret;
		},
		bindEventBeforeShow : function() {
			$("#spm_wf_page_qi").fancybox({
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
			$( "#wf_wd_page_tabs" ).tabs();
		},
		renderFieldSettings : function( settings ) {
			spm_wf_editor.js.renderBaseFieldSettingsDatatype( settings );
			$("#spm_wf_prop_query").val( commonHtmlDecode( settings.query.replace( /\\n/g, "\n" ) ) );
			if(settings.range != '') {
				var r = settings.range.split(':', 2);
				$("input[name='spm_wf_ac_range_type']").each(function(){
					if($(this).val() == r[0]) {
						$(this).attr('checked', true);
						return false;
					}
				});
				$("#spm_wf_ac_range").val(r[1]);
			}
		},
		getFieldPropertyDefinition : function() {
			var params = spm_wf_editor.js.getFieldPropertyBaseDefinition();
			params.push($("#spm_wf_prop_query").val());
			
			var o = $("input[name='spm_wf_ac_range_type']:checked");
			var range = $("#spm_wf_ac_range").val().trim();
			if(o.length > 0 && range != '') {
				params.push( o.val() + ':' + range );
			} else {
				params.push('');
			}
			return params;
		},
		appendPossibleValuesQuery : function( query ) {
			var queries = $("#spm_wf_prop_query").val();
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
			query = query.substring(start, stop + 1);
			
			queries = queries.trim();
			if(queries != '') queries += "\n";
			queries += query.replace(/\n/g, '');
			$("#spm_wf_prop_query").val( queries );
		}
	};
})(jQuery);