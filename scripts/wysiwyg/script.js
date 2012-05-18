
function commonHtmlEncode(value){
  return jQuery('<div/>').text(value).html();
}
function commonHtmlDecode(value){
  return jQuery('<div/>').html(value).text();
}

/* ###CHECK CKEDITOR### */
var spm_wysiwyg = {};
(function($) {
	function toEditorDataFormat( data, arg ) {
		// CATCH SINGLE QUOTES
		data = data.replace(/(''''')(.*?)(''''')/ig, "<strong><em>$2</em></strong>");
		data = data.replace(/(''')(.*?)(''')/ig, "<strong>$2</strong>");
		data = data.replace(/('')(.*?)('')/ig, "<em>$2</em>");

		// CATCH BULLET LIST
		data = data.replace(/\n\s*([*#])\s(.*)/ig, '<li>$2</li>');
		data = data.replace(/(<br\/>)?<\/li>(\n)?<li>/ig, '###li###');
		data = data.replace(/<li>/ig, '<ul><li>');
		data = data.replace(/(<br\/>)?<\/li>/ig, '</li></ul>');
		data = data.replace(/###li###/ig, "</li>\n<li>");

//		// CATCH ANCHORS
//		data = data.replace(/\[(.*?)\s(.*?)\]/ig, '<a _cke_saved_href="$1" href="javascript:void(0)">$2</a>');

		data = data.replace(/\n\n/g, "</p><p>");
		data = data.replace(/<p><ul>/g, "<ul>");
		data = data.replace(/<\/ul><\/p>/g, "</ul>");

		data = data.replace(/<q>/ig, "__SUBQUERY__");
		data = data.replace(/<\/q>/ig, "__/SUBQUERY__");

		if(data.indexOf('</p>') == 0) data = '<br/>' + data;
		return "<p>" + data + "</p>";
	}
	function toWikiDataFormat( html, arg ) {
		// speical case
		if(commonHtmlDecode(html) == "") return "";

		// remove all LF/RF
		html = html.replace(/\n/g, "");

		// CATCH BOLD
		html = html.replace(/<strong><\/strong>/, "");
		html = html.replace(/<strong>/ig, "'''");
		html = html.replace(/<\/strong>/ig, "'''");
		html = html.replace(/(.*?)(<br>)'''/ig, "$1'''");

		// CATCH UNDERLINE
		html = html.replace(/<u><\/u>/, "");
		html = html.replace(/<u>/ig, "###u###");
		html = html.replace(/<\/u>/ig, "###/u###");

		// CATCH ITALIC
		html = html.replace(/<em><\/em>/, "");
		html = html.replace(/<em>/ig, "''");
		html = html.replace(/<\/em>/ig, "''");
		html = html.replace(/(.*?)(<br\/?>)''/ig, "$1''");

		// CATCH BULLET LIST
		html = html.replace(/<\/li><li>/ig, "</li>\n<li>");
		html = html.replace(/<li>/ig, "* ");
		html = html.replace(/(<br\/?>)?<\/li>/ig, "");
		html = html.replace(/<ul>/ig, "");
		html = html.replace(/<\/ul>/ig, "\n\n");

		// CATCH PARAGRAPH
		html = html.replace(/<p><\/p>/, "");
		html = html.replace(/<\/p>$/, "");
		html = html.replace(/<\/p>/ig, "\n\n");

//		// CATCH ANCHOR
//		anchorPattern = /<a(.*?)_cke_saved_href="(.*?)"(.*?)>(.*?)<\/a>/ig;
//		anchorReplace = "[$2 $4]";
//		html = html.replace(anchorPattern, anchorReplace);
//		html = html.replace(/(.*?)(<br\/?>)\]/ig, "$1]");

		// CONVERT BR TO NEWLINE
		html = html.replace(/<br\/?>/ig, "###br###");

		// STRIP REMAINING HTML AND WEIRD FORMATTING
		html = html.replace(/(<([^>])>)/ig,"");

		// STRIP EMPTY SINGLE QUOTES FORM WIKI MARKUP
		html = html.replace(/(''''')(''''')/ig, "");
		html = html.replace(/('''''')/ig, "");

		// ADD BACK VALID WIKI HTML
		html = html.replace(/###u###/ig, "<u>");
		html = html.replace(/###\/u###/ig, "</u>");
		html = html.replace(/###br###/ig, "<br/>");

		// STRIP ANY RELICS
		html = html.replace(/(<([^>])>)(').*(<([^>])>)/ig,"");
		html = html.replace(/(<([^>])>)(<([^>])>)/ig,"");
		html = html.replace(/<u><\/u>/ig, "");

		// Wiki handles common html chars, don't encode it
		html = commonHtmlDecode(html);

		html = html.replace(/__SUBQUERY__/ig, "<q>");
		html = html.replace(/__\/SUBQUERY__/ig, "</q>");

		// FINAL RETURN
		return html;
	}

	spm_wysiwyg.js = {
		attachCKEditor : function( obj ) {
			if(typeof CKEDITOR == 'undefined') return;

			var jObj = $( obj );
			// FORMAT THE REPLACEMENT DIV FOR CKEDITOR
			jObj.val( toEditorDataFormat( jObj.val() ) );

			// INSTANTIATE CKEDITOR AND REPLACE THE OUTPUT PROCESSOR FOR WIKIS
			CKEDITOR.replace(
				obj,
				{
					toolbar : 'Basic',
					customConfig : wgScriptPath + '/extensions/WikiEditors/scripts/wysiwyg/ckeditor_config.js',
					on : {
						instanceReady : function( ev ){
							this.dataProcessor.toDataFormat = toWikiDataFormat;
							this.dataProcessor.toHtml = toEditorDataFormat;
							return true;
						}
					}
				}
			);
		},
		getSource : function( obj ) {
			if(typeof CKEDITOR == 'undefined') {
				return $(obj).val();
			}

			// FIXME: CKEditor id here, not sure if it will change
			var id = $(obj).next("span").attr("id").substring(4); // cke_id
			return CKEDITOR.instances[id].getData();
		}
	}
})(jQuery);
