(function($) {
	spm_wf_editor.extra.ask.js = {
		getLastAsk : function() {
			return spm_wf_editor.extra.ask.activedQuery;
		},
		hintOnEmptyAsk : function(askObj) {
			var jObj = $( ".spm_wf_wiki_body", askObj );
			if(jObj.html().trim() == "") {
				jObj.html('<span style="color:#999999">click to edit "ask" query</span>');
			}
		},
		updateAskObj : function(ask) {
			ask = commonHtmlDecode( ask.trim() );
			var jObj = spm_wf_editor.extra.ask.activedAskObj;
			var html = spm_wf_editor.js.getWikiHtml( ask, jObj );
			// jObj.html(html);
			spm_wf_editor.extra.ask.js.hintOnEmptyAsk(jObj);

			spm_wf_editor.js.notifySave();
		},
		applyQueryInterfaceEditor : function(obj) {
			var jObj = $( obj );
			spm_wf_editor.extra.ask.js.hintOnEmptyAsk(jObj);
			
			jObj.mouseover(function(e) {
				spm_wf_editor.extra.ask.activedQuery = commonHtmlDecode( $(".spm_wf_wiki", jObj).html().trim() );
				spm_wf_editor.extra.ask.activedAskObj = jObj;
//				(function(nod) {
//					var node = $(nod);
//					node.addClass('spm_div_hover');
//
//					var cld = node.children();
//					for(var i=0;i<cld.length;++i) {
//						arguments.callee(cld[i]);
//					}
//				})(e);
				jObj.addClass('spm_div_hover');
				e.stopPropagation();
			}).mouseout(function(e) {
				jObj.removeClass('spm_div_hover');
				jObj.find('*').removeClass('spm_div_hover');
			}).fancybox({
				'overlayShow'	: true,
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
				'titlePosition' : 'inside',
				'width'		  	: '90%',
				'height'	  	: '90%',
				'autoScale'		: false,
				'type'		  	: 'iframe',
				'title'         : 'Apply query',
				'onStart'       : function(){
					this.href = wgScript + '/Special:SPMQueryInterface2';
				}
			});
		},
		getAllValidProperties : function() {
			var o = {};
			$(".spm_wf_field_settings").each(function(){
				var settings = spm_wf_editor.js.getFieldSettings( this );
				o[settings.field] = settings.sample;
			});
			return o;
		},
		addNew : function() {
			var askObj = $('<div class="spm_wf_ask"><div class="spm_wf_wiki_body"></div><div class="spm_wf_wiki"></div></div>');
			$(spm_wf_editor.js.getUpdateFieldObject()).replaceWith(askObj);
			spm_wf_editor.extra.ask.js.applyQueryInterfaceEditor(askObj);

			spm_wf_editor.js.notifySave();
		}
	};

	$(document).ready(function() {
		$(".spm_wf_ask").each(function(){
			if($(this).parent().children().length == 1)
				spm_wf_editor.extra.ask.js.applyQueryInterfaceEditor(this);
		});
	});
})(jQuery);