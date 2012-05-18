function initialize_spm_ask_qi(){
	document.getElementById('fullAskText').style.width = "100%";
	var query = document.getElementsByName('spm_obj[val]')[0].innerHTML;
	if( ('function' == typeof $$) && $$('#askQI #query4DiscardChanges').length > 0 ) {
		$$('#askQI #query4DiscardChanges')[0].innerHTML = query;
	} else {
		document.getElementById('query4DiscardChanges').innerHTML = query;
	}
	qihelper.initFromQueryString(query);
}
Event.observe(window, 'load', initialize_spm_ask_qi);

function save_spm_ask_qi(){
	qihelper.switchTab(3);
	var query = '';
	if( ('function' == typeof $$) && $$('#askQI #query4DiscardChanges').length > 0 ) {
		query = $$('#askQI #query4DiscardChanges')[0].innerHTML;
	} else {
		query = document.getElementById('query4DiscardChanges').innerHTML;
	}
	document.getElementsByName('spm_obj[val]')[0].innerHTML = query;
}
function spm_ask_plain_edit() {
	save_spm_ask_qi();
	document.getElementById('qicontent').style.display = 'none';
	document.getElementById('plainask').style.display = 'none';
	document.getElementsByName('spm_obj[val]')[0].style.display = 'block';
}