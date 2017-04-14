(function(){
var xmlhttp;
if(document.all) {
    window.attachEvent('onload', init_xmlhttp);
}
else {
    window.addEventListener('load', init_xmlhttp, false);
}
function init_xmlhttp() {

	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	}
	else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = state_change;
	var ckeditor = CKEDITOR.instances['content'];
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
	var str = this.document.getBody().getText(); 
	if( str.length != 0 && str.length % 10 == 0 ){
		detect_lang(str);
    }
}

function detect_lang(str) {
	xmlhttp.open("POST","/detect.py",true);
	xmlhttp.setRequestHeader("Content-type","text/plain; charset=UTF-8;");
	xmlhttp.send(str);
}
})();
