function initialize_spm_ask_qi(){
	document.getElementById('fullAskText').style.width = "100%";
	var query = '';
	if( ('function' == typeof $$) && $$('#askQI #query4DiscardChanges').length > 0 ) {
		$$('#askQI #query4DiscardChanges')[0].innerHTML = query;
	} else {
		document.getElementById('query4DiscardChanges').innerHTML = query;
	}
	qihelper.initFromQueryString(query);
}
Event.observe(window, 'load', initialize_spm_ask_qi);

function update_wf_page_ask_qi(){
	qihelper.switchTab(3);
	var query = '';
	if( ('function' == typeof $$) && $$('#askQI #query4DiscardChanges').length > 0 ) {
		query = $$('#askQI #query4DiscardChanges')[0].innerHTML;
	} else {
		query = document.getElementById('query4DiscardChanges').innerHTML;
	}
	parent.jQuery.fancybox.close();
	parent.spm_wf_editor.datatype.page.js.appendPossibleValuesQuery( query );
}
