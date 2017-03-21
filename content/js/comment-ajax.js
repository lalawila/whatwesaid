var xmlhttp;
if (window.XMLHttpRequest) {
  xmlhttp = new XMLHttpRequest();
}
else {
  xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
}

function submit_comment() {
    xmlhttp.open("GET","demo_get.asp?t=" + Math.random(),true);
    xmlhttp.send();
}