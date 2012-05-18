(function($) {
	$(document).ready(function() {
		$("#spm_wf_clone").fancybox({
			'overlayShow'	: true,
			'transitionIn'	: 'elastic',
			'transitionOut'	: 'elastic',
			'titlePosition' : 'inside',
			'width'		  	: '90%',
			'height'	  	: '90%',
			'autoScale'		: false,
			'type'		  	: 'iframe',
			'title'         : 'Duplicate category widget from: ' + wgTitle,
		});
		$("#spm_wf_create_form").click(function(){
			var answer = confirm("A form for this widget will be (re-)created.\nSure to go?")
            if (!answer) return;
			// create form page
			var rsargs = [ 'createFormPage', wgTitle, Math.random() ];
			$.ajax({
				type: "GET",
				url: wgScript,
				data: {
					'rsargs[]':rsargs,
					'action':'ajax',
					'rs':'spm_wf_EditorAccess'
				},
				success: function(data, textStatus){
					var ret = data.split('|');
					if(ret[0] == '0') {
						alert(ret[1]);
						return;
					}
					// redirect to form page
					window.location.href = ret[1];
				}
//				, async: false
			});
		});
	});
})(jQuery);