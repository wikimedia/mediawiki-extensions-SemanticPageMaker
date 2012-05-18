window.spm_wf_connector = { js: {}, data: [] }; // [ target: {}, src: {}, widget: "", page: "" };
(function($) {
	spm_wf_connector.js = {
		hitTransaction : function(idx, trans_id, val, multiple, optional) {
			var rsargs = ['hitTransaction', val, multiple, optional, spm_wf_connector.data[idx].widget, trans_id];
			for(var id in spm_wf_connector.data[idx].src) {
				for(var i=0; i<spm_wf_connector.data[idx].src[id].length; ++i) {
					var tmp = spm_wf_connector.data[idx].src[id][i].split('_',2);
					if(trans_id == tmp[0]) {
						rsargs.push(tmp[1]);
						rsargs.push($('#' + id + ' .spm_wf_val').val());
					}
				}
			}
			$.ajax({
				type: "POST",
				url: wgScript + '/' + Math.random(),
				data: {
					'rsargs[]':rsargs,
					'action':'ajax',
					'rs':'spm_wf_EditorAccess'
				},
				success: function(data, textStatus){
					var _id = spm_wf_connector.data[idx].target['_' + trans_id];
					var jObj = $('#' + _id);
					jObj.html(data);
					
					for(var _idx in spm_wf_connector.data) {
						for(var id in spm_wf_connector.data[_idx].src) {
							if(id == _id) {
								$(".spm_wf_val", jObj).change(function(){
									// FIXME: hard code here, to work with autocomplete flag
									if(!this.ac_on) spm_wf_connector.js.hitTransactions(this);
								});
							}
						}
					}
				},
				async: false
			});
		},
		hitTransactions : function(input) {
			var id = $(input).parents(".spm_wf_fld").attr('id');
			// e.g., spm_wf_fld_3_0
			var idx = parseInt(id.substring('spm_wf_fld_'.length));
			for(var i=0; i<spm_wf_connector.data[idx].src[id].length; ++i) {
				var trans_id = spm_wf_connector.data[idx].src[id][i].split('_',2)[0];
				var jObj = $('#' + spm_wf_connector.data[idx].target['_' + trans_id] + ' .spm_wf_val');
				spm_wf_connector.js.hitTransaction(idx, trans_id, jObj.val(), jObj.hasClass('spm_wf_multi_val'), jObj.hasClass('spm_wf_optional_val'));
			}
		}
	};
	
	$(document).ready(function() {
		for(var idx in spm_wf_connector.data) {
			for(var id in spm_wf_connector.data[idx].src) {
				$('#' + id + ' .spm_wf_val').change(function(){
					// FIXME: hard code here, to work with autocomplete flag
					if(!this.ac_on) spm_wf_connector.js.hitTransactions(this);
				});
			}
		}
	});
})(jQuery);