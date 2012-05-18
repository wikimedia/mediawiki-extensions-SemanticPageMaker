(function($){
	spm_wf_editor.view.section.js = {
		getFieldSettings : function(params, offset) {
			var ret = spm_wf_editor.js.getFieldBaseSettingsView(params, offset);
			ret.level = params[offset + spm_wf_editor.js.getFieldBaseSettingsViewLength()];
			return ret;
		},
		getFieldSettingsLength : function() {
			return spm_wf_editor.js.getFieldBaseSettingsViewLength() + 1;
		},
		renderFieldSettings : function( settings ) {
			spm_wf_editor.js.renderBaseFieldSettingsView( settings );
			$("#spm_section_level").val( settings.level );
		},
		getFieldDefinition : function() {
			var section_lev = $("#spm_section_level").val().trim();
			if(! (section_lev>=1 && section_lev<=6)) {
				alert('Invalid section level: 1 - 6 only!\nSet to "2" by default.');
				section_lev = 2;
				$("#spm_section_level").val(2);
			}

			var params = spm_wf_editor.js.getFieldBaseDefinitionView();
			params.push(section_lev);
			
			return params;
		}
	};
})(jQuery);