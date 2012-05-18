(function($){
	spm_wf_editor.datatype.uid.js = {
		getFieldSettings : function(params, offset) {
			var ret = spm_wf_editor.js.getFieldBaseSettingsDatatype(params, offset);
			ret.prefix = params[offset + spm_wf_editor.js.getFieldBaseSettingsDatatypeLength()];
			return ret;
		},
		renderFieldSettings : function( settings ) {
			spm_wf_editor.js.renderBaseFieldSettingsDatatype( settings );
			$("#spm_wf_field_prefix").val( commonHtmlDecode( settings.prefix.replace( /\\n/g, "\n" ) ) );
		},
		getFieldPropertyDefinition : function() {
			var params = spm_wf_editor.js.getFieldPropertyBaseDefinition();
			params.push($("#spm_wf_field_prefix").val());
			
			return params;
		}
	};
})(jQuery);