//var xmlhttp;
//if(document.all) {
//    window.attachEvent('onload', init_xmlhttp);
//}
//else {
//    window.addEventListener('click', init_xmlhttp, false);
//}
//function init_xmlhttp() {
//
//    if (window.XMLHttpRequest) {
//        xmlhttp = new XMLHttpRequest();
//    }
//    else {
//        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
//    }
//    xmlhttp.onreadystatechange = state_change;
//    var ckeditor = CKEDITOR.instances['article'];
//    ckeditor.on('change',on_text_change);
//}


function select_en(){
    document.getElementById('post-en').setAttribute('style', 'display:block');
    document.getElementById('post-zh').setAttribute('style', 'display:none');
}

function select_zh(){
    document.getElementById('post-zh').setAttribute('style', 'display:block');
    document.getElementById('post-en').setAttribute('style', 'display:none');
}
