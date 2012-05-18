(function($) {
	$(document).ready(function() {
		// FIXME: hardcode here
		var ret = /[\?&]action=wfedit/ig.exec(window.location.href);
		if(ret) return;
		$(".spm_hidden_flag").each(function(){
			$(this).parents("li.widget").first().hide();
		});
	});
})(jQuery);
