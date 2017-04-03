var xmlhttp;
if(document.all) {
    window.attachEvent('onload', init_xmlhttp);
}
else {
    window.addEventListener('click', init_xmlhttp, false);
}
function init_xmlhttp() {

	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	}
	else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = state_change;
	var ckeditor = CKEDITOR.instances['article'];
	ckeditor.on('change',on_text_change);
}

function state_change () {
	if( xmlhttp.readyState == 4 && xmlhttp.status == 200) {
		var lang = xmlhttp.responseText;
		if ( lang == "en" || lang == "zh")
			document.getElementById("lang").value = lang;
	}
}

function on_text_change() {
	var str = this.getData();
	if( str.length != 0 && str.length % 100 == 0 )
		detect_lang(str);
}

function detect_lang(str) {
	xmlhttp.open("POST","detect.py",true);
	xmlhttp.setRequestHeader("Content-type","text/plain; charset=UTF-8;");
	xmlhttp.send(str);
}

