(function($) {
	spm_wf_editor.extra.plain.js = {
		addNew : function() {
			var plainObj = $('<div class="spm_wf_plain"><div class="spm_wf_wiki_body"></div><div class="spm_wf_wiki"></div></div>');
			$(spm_wf_editor.js.getUpdateFieldObject()).replaceWith(plainObj);
			spm_wf_editor.js.applyFreeTextEditor(plainObj);

			spm_wf_editor.js.notifySave();
		}
	};

	$(document).ready(function() {
		$(".spm_wf_plain").each(function(){
			if($(this).parent().children().length == 1)
				spm_wf_editor.js.applyFreeTextEditor(this);
		});
	});
})(jQuery);