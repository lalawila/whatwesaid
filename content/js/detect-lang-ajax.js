window.onload = function() {
	if(window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	}
	else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById("lang").value = xmlhttp.responseText;
		}
	}
}

function detect_lang(){
	xmlhttp.open("POST",")
}
