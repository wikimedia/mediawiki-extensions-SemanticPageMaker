(function($){
	spm_wf_editor.datatype.number.js = {
		getFieldSettings : function(params, offset) {
			var ret = spm_wf_editor.js.getFieldBaseSettingsDatatype(params, offset);
			ret.ranges = params[offset + spm_wf_editor.js.getFieldBaseSettingsDatatypeLength()];
			return ret;
		},
		renderFieldSettings : function( settings ) {
			spm_wf_editor.js.renderBaseFieldSettingsDatatype( settings );
			$("#spm_wf_prop_range").val( commonHtmlDecode( settings.ranges.replace( /\\n/g, "\n" ) ) );
		},
		getFieldPropertyDefinition : function() {
			var params = spm_wf_editor.js.getFieldPropertyBaseDefinition();
			params.push($("#spm_wf_prop_range").val());
			
			return params;
		}
	};
})(jQuery);