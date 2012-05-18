window.spm_wf_input = {url:'', objs:[]};
(function($) {
	var spm_wf_input_showfancy = function(input_params) {
		var id = input_params.id;
		$('#spm_wf_new_' + id).fancybox({
			'overlayShow'	: true,
			'transitionIn'	: 'elastic',
			'transitionOut'	: 'elastic',
			'titlePosition' : 'inside',
			'width'		  	: '90%',
			'height'	  	: '90%',
			'autoScale'		: false,
			'type'		  	: 'iframe',
			'title'         : 'Create page on widget : ' + input_params.widget,
			'onStart'       : function(){
				var v = $('#spm_wf_new_title_' + id).val().trim();
				if( v == "" ) {
					alert('Textbox cannot be blank. Please fill in.');
					return false;
				}
				this.href = spm_wf_input.url +
						'?spm_w=' + input_params.widget +
						'&spm_t=' +  encodeURIComponent(v) +
						'&params=' +  input_params.params +
						'&' + input_params.value +
						'&' + Math.random();
			}
		});
	};
	$(document).ready(function() {
		for(var i=0;i<spm_wf_input.objs.length;++i) {
			spm_wf_input_showfancy(spm_wf_input.objs[i]);
		}
	});
})(jQuery);
