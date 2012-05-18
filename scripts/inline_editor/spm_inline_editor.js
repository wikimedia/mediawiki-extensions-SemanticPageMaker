(function($) {
		$(document).ready(function() {
			$("div.spm_inline_div").each(function(idx, ele){
				var d = $(this);

				var id = ele.id.substring('spm_inline_'.length);
				var title = url = '';

				for(var i=spm_objs.length-1;i>=0;--i) {
					if(id == spm_objs[i].id) {
						title = spm_objs[i].title;
						url = spm_objs[i].url;
						break;
					}
				}
				var fbox = {
					'overlayShow'	: true,
					'transitionIn'	: 'elastic',
					'transitionOut'	: 'elastic',
					'titlePosition' : 'inside',
					'width'		  	: '90%',
					'height'	  	: '90%',
					'autoScale'		: false,
					'type'		  	: 'iframe',

					'reservedUrl'   : url,
					'title'         : title,
					'onStart'       : function(){
						this.href = this.reservedUrl + '?' + Math.random();
					}
				};

				(function(nod, first) {
					// special case, skip toc table, scripts and links
					if(nod.id == 'toc' || nod.nodeName == 'SCRIPT' || nod.nodeName == 'LINK') return;
					var node = $(nod);

					if(!first && node.hasClass('spm_inline_div')) return;

					node.fancybox(fbox);
					node.click(function(e2){
						e2.stopPropagation();
						return false;
					});

					var cld = node.children();
					for(var i=0;i<cld.length;++i) {
						arguments.callee(cld[i], false);
					}
				})(ele, true);

				d.mouseover(function(e) {
					// special case, toc
					var isToc = false;
					(function(nod) {
						// special case, skip toc table, scripts and links
						if(nod.id == 'toc' || nod.nodeName == 'SCRIPT' || nod.nodeName == 'LINK') return;

						var node = $(nod);
						node.addClass('spm_div_hover');

						var cld = node.children();
						for(var i=0;i<cld.length;++i) {
							arguments.callee(cld[i]);
						}
					}(ele));
					if(!isToc) d.addClass('spm_div_hover');
					e.stopPropagation();
				}).mouseout(function(e) {
					d.removeClass('spm_div_hover');
					d.find('*').removeClass('spm_div_hover');
				});
			});
		});
})(jQuery);
