(function($){
	spm_wf_editor.datatype.widget.js = {
		getFieldSettings : function(params, offset) {
			var ret = spm_wf_editor.js.getFieldBaseSettingsDatatype(params, offset);
			ret.widget = params[offset + spm_wf_editor.js.getFieldBaseSettingsDatatypeLength()];
			return ret;
		},
		renderFieldSettings : function( settings ) {
			spm_wf_editor.js.renderBaseFieldSettingsDatatype( settings );
			$("#spm_wf_field_widget").val( settings.widget );
//			$("#spm_wf_editor_view select").val('section').attr('disabled', 'disabled').change();
//			$("#spm_wf_view_multiple").removeAttr('checked').attr('disabled', 'disabled');
//			$("#spm_wf_view_optional").removeAttr('checked').attr('disabled', 'disabled');
		},
		getFieldPropertyDefinition : function() {
			var params = spm_wf_editor.js.getFieldPropertyBaseDefinition();
			params.push($("#spm_wf_field_widget").val());
			return params;
		}
	};
	
//	$(document).ready(function() {
//		$("#spm_wf_editor_datatype select").change(function(){
//			if( $(this).val() != 'widget' ) {
//				$("#spm_wf_editor_view select").removeAttr('disabled');
//				$("#spm_wf_view_multiple").removeAttr('disabled');
//				$("#spm_wf_view_optional").removeAttr('disabled');
//			} else {
//				$("#spm_wf_editor_view select").val('section').attr('disabled', 'disabled').change();
//				$("#spm_wf_view_multiple").removeAttr('checked').attr('disabled', 'disabled');
//				$("#spm_wf_view_optional").removeAttr('checked').attr('disabled', 'disabled');
//			}
//		});
//	});
})(jQuery);