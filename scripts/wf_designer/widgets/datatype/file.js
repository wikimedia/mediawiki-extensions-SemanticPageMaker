(function($){
	spm_wf_editor.datatype.file.js = {
		getFieldSettings : function(params, offset) {
			var ret = spm_wf_editor.js.getFieldBaseSettingsDatatype(params, offset);
			offset += spm_wf_editor.js.getFieldBaseSettingsDatatypeLength();
			ret.mediatype = params[offset ++];
			ret.size = params[offset ++];
			return ret;
		},
		renderFieldSettings : function( settings ) {
			spm_wf_editor.js.renderBaseFieldSettingsDatatype( settings );
			$("#spm_wf_field_mediatype").val( settings.mediatype );
			$("#spm_wf_image_size").val( settings.size );
		},
		getFieldDefinition : function() {
			var params = spm_wf_editor.js.getFieldBaseDefinitionDatatype();
			params.push($("#spm_wf_field_mediatype").val());
			params.push($("#spm_wf_image_size").val());
			
			return params;
		}
	};
})(jQuery);